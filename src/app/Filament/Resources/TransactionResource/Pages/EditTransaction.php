<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Item;
use App\Models\TransactionItem;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    // When deleting a transaction, restore the stock
                    $transaction = $this->record;
                    foreach ($transaction->transactionItems as $transactionItem) {
                        $item = Item::find($transactionItem->item_id);
                        if ($item) {
                            // Reverse the stock changes based on transaction type
                            if ($transaction->type === 'in') {
                                $item->stock = max(0, $item->stock - $transactionItem->quantity);
                            } else {
                                $item->stock += $transactionItem->quantity;
                            }
                            $item->save();
                        }
                    }
                }),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Store old transaction items to calculate stock differences
        $oldType = $record->type;
        $oldTransactionItems = $record->transactionItems->keyBy('id')->toArray();
        
        // Update the transaction record
        $record->update($data);
        
        // Update items stock based on changes
        if (isset($data['transactionItems'])) {
            $currentTransactionItemIds = [];
            
            foreach ($data['transactionItems'] as $transactionItemData) {
                $item = Item::find($transactionItemData['item_id']);
                
                if (!$item) continue;
                
                // Check if this is an existing or new transaction item
                if (isset($transactionItemData['id'])) {
                    $currentTransactionItemIds[] = $transactionItemData['id'];
                    $oldTransactionItem = $oldTransactionItems[$transactionItemData['id']] ?? null;
                    
                    if ($oldTransactionItem) {
                        // Revert old quantity impact on stock
                        if ($oldType === 'in') {
                            $item->stock -= $oldTransactionItem['quantity'];
                        } else {
                            $item->stock += $oldTransactionItem['quantity'];
                        }
                        
                        // Apply new quantity impact on stock
                        if ($data['type'] === 'in') {
                            $item->stock += $transactionItemData['quantity'];
                        } else {
                            $item->stock -= $transactionItemData['quantity'];
                        }
                    }
                } else {
                    // New transaction item
                    if ($data['type'] === 'in') {
                        $item->stock += $transactionItemData['quantity'];
                    } else {
                        $item->stock -= $transactionItemData['quantity'];
                    }
                }
                
                // Ensure stock doesn't go below 0
                $item->stock = max(0, $item->stock);
                $item->save();
            }
            
            // Handle deleted transaction items
            foreach ($oldTransactionItems as $id => $oldItem) {
                if (!in_array($id, $currentTransactionItemIds)) {
                    $item = Item::find($oldItem['item_id']);
                    if ($item) {
                        // Revert the stock change for deleted items
                        if ($oldType === 'in') {
                            $item->stock -= $oldItem['quantity'];
                        } else {
                            $item->stock += $oldItem['quantity'];
                        }
                        $item->stock = max(0, $item->stock);
                        $item->save();
                    }
                }
            }
        }
        
        return $record;
    }
}
