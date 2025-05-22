<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewItem extends ViewRecord
{
    protected static string $resource = ItemResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->tooltip('Edit this item'),
            Actions\DeleteAction::make()
                ->tooltip('Delete this item')
                ->requiresConfirmation()
                ->before(function (Actions\DeleteAction $action) {
                    if ($this->record->transactionItems()->exists()) {
                        $action->cancel();
                        $action->failureNotification()?->title('Item cannot be deleted')
                            ->body('This item has associated transactions. Remove the transactions first before deleting this item.')
                            ->send();
                    }
                }),
        ];
    }
}
