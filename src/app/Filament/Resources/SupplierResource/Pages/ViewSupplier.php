<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSupplier extends ViewRecord
{
    protected static string $resource = SupplierResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->tooltip('Edit this supplier'),
            Actions\DeleteAction::make()
                ->tooltip('Delete this supplier')
                ->requiresConfirmation()
                ->before(function (Actions\DeleteAction $action) {
                    if ($this->record->items()->exists()) {
                        $action->cancel();
                        $action->failureNotification()?->title('Supplier cannot be deleted')
                            ->body('This supplier has associated items. Remove or reassign the items before deleting this supplier.')
                            ->send();
                    }
                }),
        ];
    }
}
