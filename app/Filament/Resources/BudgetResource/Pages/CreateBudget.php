<?php

namespace App\Filament\Resources\BudgetResource\Pages;

use App\Filament\Resources\BudgetResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateBudget extends CreateRecord
{
    protected static string $resource = BudgetResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return null;
    }

    protected function afterCreate()
    {
        Notification::make()
            ->title('Presupuesto creado')
            ->body('El presupuesto se ha creado exitosamente.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Guardar')
                ->color('success')
                ->icon('heroicon-o-check'),

            // $this->getCreateAnotherFormAction()
            //   ->label('Guardar y nuevo'),

            $this->getCancelFormAction()
                ->label('Cancelar')
                ->color('warning')
                ->icon('heroicon-o-arrow-left')
        ];
    }
}
