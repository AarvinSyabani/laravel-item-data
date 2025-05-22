<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->tooltip('Edit this category'),
            Actions\DeleteAction::make()
                ->tooltip('Delete this category')
                ->requiresConfirmation()
                ->before(function (Actions\DeleteAction $action) {
                    if ($this->record->items()->exists()) {
                        $action->cancel();
                        $action->failureNotification()?->title('Category cannot be deleted')
                            ->body('This category has associated items. Remove the items first before deleting this category.')
                            ->send();
                    }
                }),
        ];
    }
}
