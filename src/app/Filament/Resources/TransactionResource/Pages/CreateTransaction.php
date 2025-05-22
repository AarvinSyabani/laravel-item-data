<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Item;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function handleRecordCreation(array $data): Model
    {
        $transaction = static::getModel()::create($data);
        
        // Update the stock based on transaction type
        if (isset($data['transactionItems'])) {
            foreach ($data['transactionItems'] as $transactionItem) {
                $item = Item::find($transactionItem['item_id']);
                if ($item) {
                    // For 'in' transactions, add to stock; for 'out' transactions, subtract from stock
                    if ($data['type'] === 'in') {
                        $item->stock += $transactionItem['quantity'];
                    } else {
                        $item->stock = max(0, $item->stock - $transactionItem['quantity']);
                    }
                    $item->save();
                }
            }
        }
        
        return $transaction;
    }
}
