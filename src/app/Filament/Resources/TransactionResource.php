<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Item;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    
    protected static ?string $navigationGroup = 'Transaction Management';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $recordTitleAttribute = 'transaction_no';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Section::make('Transaction Information')
                            ->description('Basic information about the transaction')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('transaction_no')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->default(fn () => 'TRX-' . strtoupper(Str::random(8)))
                                            ->disabled(fn ($record) => $record !== null)
                                            ->dehydrated(fn ($record) => $record === null)
                                            ->helperText('Transaction number will be auto-generated if left empty')
                                            ->columnSpan(2),
                                            
                                        Forms\Components\DateTimePicker::make('date')
                                            ->required()
                                            ->default(now())
                                            ->columnSpan(2),
                                            
                                        Forms\Components\Select::make('type')
                                            ->required()
                                            ->options([
                                                'in' => 'Incoming',
                                                'out' => 'Outgoing',
                                            ])
                                            ->default('out')
                                            ->disabled(fn ($record) => $record !== null)
                                            ->dehydrated(fn ($record) => $record === null)
                                            ->helperText('Transaction type cannot be changed after creation')
                                            ->live()
                                            ->columnSpan(2),
                                    ])
                                    ->columns(6)
                                    ->columnSpan('full'),
                                
                                Forms\Components\Textarea::make('notes')
                                    ->maxLength(65535)
                                    ->placeholder('Enter notes about this transaction')
                                    ->columnSpanFull(),
                            ]),
                            
                        Forms\Components\Section::make('Transaction Items')
                            ->description('Items included in this transaction')
                            ->schema([
                                Forms\Components\Repeater::make('transactionItems')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('item_id')
                                            ->label('Item')
                                            ->relationship('item', 'name', function (Builder $query, callable $get) {
                                                // If transaction type is outgoing, only show items with stock > 0
                                                if ($get('../../type') === 'out') {
                                                    return $query->where('stock', '>', 0);
                                                }
                                                return $query;
                                            })
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->distinct()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, $get) {
                                                if ($state) {
                                                    $item = Item::find($state);
                                                    if ($item) {
                                                        $set('price', $item->price);
                                                        
                                                        // For outgoing transactions, limit quantity to available stock
                                                        if ($get('../../type') === 'out') {
                                                            $set('max_quantity', $item->stock);
                                                        }
                                                    }
                                                }
                                            }),
                                            
                                        Forms\Components\TextInput::make('quantity')
                                            ->required()
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, $get) {
                                                // Calculate subtotal dynamically
                                                $price = $get('price') ?? 0;
                                                $quantity = $state ?? 0;
                                                $set('subtotal', $price * $quantity);
                                            })
                                            ->maxValue(function (callable $get) {
                                                // For outgoing transactions, limit by available stock
                                                if ($get('../../type') === 'out') {
                                                    $itemId = $get('item_id');
                                                    if ($itemId) {
                                                        $item = Item::find($itemId);
                                                        if ($item) {
                                                            return $item->stock;
                                                        }
                                                    }
                                                }
                                                return null; // No limit for incoming
                                            }),
                                            
                                        Forms\Components\TextInput::make('price')
                                            ->required()
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->minValue(0)
                                            ->default(0)
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, $get) {
                                                // Calculate subtotal dynamically
                                                $price = $state ?? 0;
                                                $quantity = $get('quantity') ?? 0;
                                                $set('subtotal', $price * $quantity);
                                            }),
                                            
                                        Forms\Components\TextInput::make('subtotal')
                                            ->label('Subtotal')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->prefix('Rp')
                                            ->formatStateUsing(function ($state, callable $get) {
                                                $price = $get('price') ?? 0;
                                                $quantity = $get('quantity') ?? 0;
                                                return $price * $quantity;
                                            }),
                                    ])
                                    ->columns(4)
                                    ->required()
                                    ->minItems(1)
                                    ->defaultItems(1)
                                    ->itemLabel(fn (array $state): ?string => 
                                        $state['item_id'] 
                                            ? Item::find($state['item_id'])?->name . ' (' . ($state['quantity'] ?? 1) . ' pcs)'
                                            : null
                                    )
                                    ->columnSpanFull(),
                            ]),
                            
                        Forms\Components\Section::make('Transaction Summary')
                            ->schema([
                                Forms\Components\Placeholder::make('total_items')
                                    ->label('Total Items')
                                    ->content(function ($get) {
                                        $items = $get('transactionItems');
                                        if (!$items) return 0;
                                        
                                        return collect($items)->sum(function ($item) {
                                            return $item['quantity'] ?? 0;
                                        });
                                    }),
                                    
                                Forms\Components\Placeholder::make('total_amount')
                                    ->label('Total Amount')
                                    ->content(function ($get) {
                                        $items = $get('transactionItems');
                                        if (!$items) return 'Rp 0';
                                        
                                        $total = collect($items)->sum(function ($item) {
                                            $quantity = $item['quantity'] ?? 0;
                                            $price = $item['price'] ?? 0;
                                            return $quantity * $price;
                                        });
                                        
                                        return 'Rp ' . number_format($total, 0, ',', '.');
                                    }),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_no')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->tooltip('Click to copy'),
                Tables\Columns\TextColumn::make('date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in' => 'Incoming',
                        'out' => 'Outgoing',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('transactionItems_count')
                    ->counts('transactionItems')
                    ->label('Items')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('total_value')
                    ->money('IDR')
                    ->label('Total Value')
                    ->getStateUsing(function (Transaction $record): float {
                        return $record->transactionItems->sum(function ($item) {
                            return $item->price * $item->quantity;
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withSum([
                            'transactionItems as total_value' => fn (Builder $query) => 
                                $query->selectRaw('SUM(price * quantity)')
                        ], 'total_value')
                        ->orderBy('total_value', $direction);
                    }),
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
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'in' => 'Incoming',
                        'out' => 'Outgoing',
                    ])
                    ->label('Transaction Type'),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'From ' . \Carbon\Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Until ' . \Carbon\Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        
                        return $indicators;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('has_items')
                    ->label('Has Specific Items')
                    ->form([
                        Forms\Components\Select::make('item_id')
                            ->label('Select Item')
                            ->multiple()
                            ->relationship('transactionItems.item', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['item_id'],
                            fn (Builder $query, $itemIds): Builder => 
                                $query->whereHas('transactionItems', function (Builder $query) use ($itemIds) {
                                    $query->whereIn('item_id', $itemIds);
                                })
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (Transaction $record) {
                        // For each transaction item, update the stock
                        foreach ($record->transactionItems as $item) {
                            $product = $item->item;
                            
                            // If transaction was incoming, decrease stock
                            // If transaction was outgoing, increase stock (return items to inventory)
                            if ($record->type === 'in') {
                                $product->stock -= $item->quantity;
                            } else {
                                $product->stock += $item->quantity;
                            }
                            
                            $product->save();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->before(function (Collection $records) {
                            // For each transaction, update stock levels
                            foreach ($records as $record) {
                                foreach ($record->transactionItems as $item) {
                                    $product = $item->item;
                                    
                                    // If transaction was incoming, decrease stock
                                    // If transaction was outgoing, increase stock (return items to inventory)
                                    if ($record->type === 'in') {
                                        $product->stock -= $item->quantity;
                                    } else {
                                        $product->stock += $item->quantity;
                                    }
                                    
                                    $product->save();
                                }
                            }
                        }),
                    Tables\Actions\BulkAction::make('exportToCsv')
                        ->label('Export selected')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(fn (Collection $records) => redirect()->route('export.transactions', [
                            'ids' => $records->pluck('id')->toArray(),
                        ])),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['transaction_no', 'notes'];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('transactionItems');
    }
}
