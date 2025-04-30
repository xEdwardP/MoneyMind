<?php

namespace App\Filament\Resources\BudgetResource\Pages;

use App\Filament\Resources\BudgetResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBudget extends EditRecord
{
    protected static string $resource = BudgetResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return null;
    }

    protected function afterSave()
    {
        Notification::make()
            ->title('Presupuesto actualizado')
            ->body('El presupuesto se ha actualizado exitosamente.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make()
            // ->successNotification(
            //     Notification::make()
            //     ->title('Categoria eliminado')
            //     ->body('La categoria se ha eliminado exitosamente.')
            //     ->success()
            // ),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Guardar cambios')
                ->color('success')
                ->icon('heroicon-o-check'),

            $this->getCancelFormAction()
                ->label('Cancelar')
                ->color('warning')
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
