<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    
    protected static ?string $navigationGroup = 'Inventory Management';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Section::make('Supplier Information')
                            ->description('Basic information about the supplier')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->autofocus()
                                    ->placeholder('Enter supplier name')
                                    ->columnSpan(2),
                                Forms\Components\Textarea::make('address')
                                    ->maxLength(65535)
                                    ->placeholder('Enter supplier address')
                                    ->columnSpanFull(),
                            ]),
                        
                        Forms\Components\Section::make('Contact Information')
                            ->description('Contact details for this supplier')
                            ->collapsed(false)
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255)
                                    ->placeholder('Enter phone number')
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->maxLength(255)
                                    ->placeholder('Enter email address')
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('contact_person')
                                    ->maxLength(255)
                                    ->placeholder('Enter contact person name')
                                    ->columnSpan(2),
                            ])
                            ->columns(2),
                    ])
                    ->columns(2),
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
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('contact_person')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items Supplied')
                    ->sortable()
                    ->badge()
                    ->color('success'),
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
                Tables\Filters\Filter::make('has_items')
                    ->query(fn (Builder $query): Builder => $query->whereHas('items'))
                    ->label('With Items'),
                Tables\Filters\Filter::make('no_items')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave('items'))
                    ->label('Without Items'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (Tables\Actions\DeleteAction $action, Supplier $record) {
                        if ($record->items()->count() > 0) {
                            $action->cancel();
                            $action->failureNotification()?->title('Supplier cannot be deleted')
                                ->body('This supplier has associated items. Remove or reassign the items before deleting this supplier.')
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
                        ->action(fn (Collection $records) => redirect()->route('export.suppliers', [
                            'ids' => $records->pluck('id')->toArray(),
                        ])),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'view' => Pages\ViewSupplier::route('/{record}'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('items');
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone', 'contact_person'];
    }
}
