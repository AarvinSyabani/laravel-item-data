<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    
    protected static ?string $navigationGroup = 'Inventory Management';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Section::make('Basic Information')
                            ->description('Enter the basic details of the item')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->autofocus()
                                    ->placeholder('Enter item name')
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('sku')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('Enter SKU')
                                    ->columnSpan(2),
                                Forms\Components\Textarea::make('description')
                                    ->maxLength(65535)
                                    ->placeholder('Enter item description')
                                    ->columnSpanFull(),
                            ])
                            ->columns(4),
                        
                        Forms\Components\Section::make('Pricing & Stock')
                            ->description('Manage pricing and inventory levels')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->minValue(0)
                                    ->placeholder('Enter price')
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('stock')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->placeholder('Enter stock quantity')
                                    ->helperText('Current inventory level')
                                    ->columnSpan(2),
                            ])
                            ->columns(4),
                        
                        Forms\Components\Section::make('Classification')
                            ->description('Assign this item to categories and suppliers')
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('description')
                                            ->maxLength(65535),
                                    ])
                                    ->columnSpan(2),
                                Forms\Components\Select::make('supplier_id')
                                    ->relationship('supplier', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('address')
                                            ->maxLength(65535),
                                        Forms\Components\TextInput::make('phone')
                                            ->tel()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('contact_person')
                                            ->maxLength(255),
                                    ])
                                    ->columnSpan(2),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),
                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->sortable()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => 
                        $state <= 5 ? 'danger' : 
                        ($state <= 10 ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Tables\Filters\Filter::make('low_stock')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '<', 10))
                    ->label('Low Stock (<10)'),
                Tables\Filters\Filter::make('out_of_stock')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '=', 0))
                    ->label('Out of Stock'),
                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('min_price')
                            ->numeric()
                            ->placeholder('Minimum price')
                            ->label('Min Price'),
                        Forms\Components\TextInput::make('max_price')
                            ->numeric()
                            ->placeholder('Maximum price')
                            ->label('Max Price'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_price'],
                                fn (Builder $query, $price): Builder => $query->where('price', '>=', $price),
                            )
                            ->when(
                                $data['max_price'],
                                fn (Builder $query, $price): Builder => $query->where('price', '<=', $price),
                            );
                    })
                    ->label('Price Range'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (Tables\Actions\DeleteAction $action, Item $record) {
                        if ($record->transactionItems()->exists()) {
                            $action->cancel();
                            $action->failureNotification()?->title('Item cannot be deleted')
                                ->body('This item has associated transactions. Remove the transactions first before deleting this item.')
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('exportToCsv')
                        ->label('Export selected')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(fn (Collection $records) => redirect()->route('export.items', [
                            'ids' => $records->pluck('id')->toArray(),
                        ])),
                    Tables\Actions\BulkAction::make('updateStock')
                        ->label('Update Stock')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->form([
                            Forms\Components\TextInput::make('stock_change')
                                ->label('Adjust Stock By')
                                ->numeric()
                                ->required()
                                ->default(0)
                                ->helperText('Use positive numbers to increase stock, negative to decrease'),
                            Forms\Components\Radio::make('operation')
                                ->label('Operation')
                                ->options([
                                    'add' => 'Add/Subtract (Relative)',
                                    'set' => 'Set to Value (Absolute)',
                                ])
                                ->default('add')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $record) {
                                if ($data['operation'] === 'add') {
                                    $record->stock += $data['stock_change'];
                                } else {
                                    $record->stock = $data['stock_change'];
                                }
                                
                                $record->save();
                                
                                // Log activity
                                activity()
                                    ->causedBy(auth()->user())
                                    ->performedOn($record)
                                    ->log($data['operation'] === 'add' 
                                        ? "Stock adjusted by {$data['stock_change']}" 
                                        : "Stock set to {$data['stock_change']}");
                            }
                            
                            Notification::make()
                                ->title('Stock Updated')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'view' => Pages\ViewItem::route('/{record}'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku', 'description', 'category.name', 'supplier.name'];
    }
}
