<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->tooltip('Edit this transaction'),
            Actions\DeleteAction::make()
                ->tooltip('Delete this transaction')
                ->requiresConfirmation()
                ->before(function (Actions\DeleteAction $action) {
                    // For each transaction item, update the stock before deletion
                    foreach ($this->record->transactionItems as $item) {
                        $product = $item->item;
                        
                        // If transaction was incoming, decrease stock
                        // If transaction was outgoing, increase stock (return items to inventory)
                        if ($this->record->type === 'in') {
                            $product->stock -= $item->quantity;
                        } else {
                            $product->stock += $item->quantity;
                        }
                        
                        $product->save();
                    }
                }),
        ];
    }
}
