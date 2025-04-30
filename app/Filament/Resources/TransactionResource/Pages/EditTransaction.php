<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

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
            ->title('Movimiento actualizado')
            ->body('El movimiento se ha actualizado exitosamente.')
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
