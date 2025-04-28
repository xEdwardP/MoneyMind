<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

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
            ->title('Categoria creada')
            ->body('La categoria se ha creado exitosamente.')
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
