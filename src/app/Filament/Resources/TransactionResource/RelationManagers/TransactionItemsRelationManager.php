<?php

namespace App\Filament\Resources\TransactionResource\RelationManagers;

use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactionItems';

    public function form(Form $form): Form
    {
        $transactionType = $this->getOwnerRecord()->type;
        
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('item_id')
                            ->relationship('item', 'name', function (Builder $query) use ($transactionType) {
                                // If transaction type is outgoing, only show items with stock > 0
                                if ($transactionType === 'out') {
                                    return $query->where('stock', '>', 0);
                                }
                                return $query;
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $item = Item::find($state);
                                    if ($item) {
                                        $set('price', $item->price);
                                    }
                                }
                            })
                            ->columnSpan(2),
                            
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(function () use ($transactionType) {
                                if ($transactionType === 'out') {
                                    // Get the item_id from the form state
                                    $itemId = $this->getState()['item_id'] ?? null;
                                    if ($itemId) {
                                        $item = Item::find($itemId);
                                        if ($item) {
                                            return $item->stock;
                                        }
                                    }
                                }
                                return null; // No limit for incoming
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $price = $this->getState()['price'] ?? 0;
                                $subtotal = $price * ($state ?? 0);
                                $set('subtotal', $subtotal);
                            })
                            ->columnSpan(1),
                            
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $quantity = $this->getState()['quantity'] ?? 0;
                                $subtotal = ($state ?? 0) * $quantity;
                                $set('subtotal', $subtotal);
                            })
                            ->columnSpan(1),
                            
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('Rp')
                            ->afterStateHydrated(function (Forms\Set $set) {
                                $quantity = $this->getState()['quantity'] ?? 0;
                                $price = $this->getState()['price'] ?? 0;
                                $set('subtotal', $quantity * $price);
                            })
                            ->columnSpan(2),
                    ])
                    ->columns(4),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('item.name')
                    ->label('Item')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('item.sku')
                    ->label('SKU')
                    ->searchable()
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity')
                    ->alignCenter()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('subtotal')
                    ->money('IDR')
                    ->label('Subtotal')
                    ->getStateUsing(fn ($record) => $record->quantity * $record->price)
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw('price * quantity ' . $direction);
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('item')
                    ->relationship('item', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                    
                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('min_price')
                            ->numeric()
                            ->placeholder('Min price')
                            ->label('Min Price'),
                        Forms\Components\TextInput::make('max_price')
                            ->numeric()
                            ->placeholder('Max price')
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
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['min_price'] ?? null) {
                            $indicators['min_price'] = 'Min price: Rp ' . number_format($data['min_price'], 0, ',', '.');
                        }
                        
                        if ($data['max_price'] ?? null) {
                            $indicators['max_price'] = 'Max price: Rp ' . number_format($data['max_price'], 0, ',', '.');
                        }
                        
                        return $indicators;
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function ($record) {
                        // Update item stock based on transaction type
                        $transaction = $this->getOwnerRecord();
                        $item = $record->item;
                        
                        if ($transaction->type === 'in') {
                            // If transaction is incoming, increase stock
                            $item->stock += $record->quantity;
                        } else {
                            // If transaction is outgoing, decrease stock
                            $item->stock -= $record->quantity;
                        }
                        
                        $item->save();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->using(function ($record, array $data) {
                        // Save old values for stock adjustment
                        $oldQuantity = $record->quantity;
                        
                        // Update the record
                        $record->update($data);
                        
                        // Update item stock based on quantity change
                        $transaction = $this->getOwnerRecord();
                        $item = $record->item;
                        $quantityDiff = $data['quantity'] - $oldQuantity;
                        
                        if ($transaction->type === 'in') {
                            // If transaction is incoming, adjust stock according to quantity difference
                            $item->stock += $quantityDiff;
                        } else {
                            // If transaction is outgoing, adjust stock according to quantity difference (inverse)
                            $item->stock -= $quantityDiff;
                        }
                        
                        $item->save();
                        
                        return $record;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {
                        // Update item stock when deleting transaction item
                        $transaction = $this->getOwnerRecord();
                        $item = $record->item;
                        
                        if ($transaction->type === 'in') {
                            // If transaction was incoming, decrease stock
                            $item->stock -= $record->quantity;
                        } else {
                            // If transaction was outgoing, increase stock (return to inventory)
                            $item->stock += $record->quantity;
                        }
                        
                        $item->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Update item stock for each deleted record
                            $transaction = $this->getOwnerRecord();
                            
                            foreach ($records as $record) {
                                $item = $record->item;
                                
                                if ($transaction->type === 'in') {
                                    // If transaction was incoming, decrease stock
                                    $item->stock -= $record->quantity;
                                } else {
                                    // If transaction was outgoing, increase stock (return to inventory)
                                    $item->stock += $record->quantity;
                                }
                                
                                $item->save();
                            }
                        }),
                ]),
            ]);
    }
}
