<?php

namespace App\Filament\Resources\ClassTaskResource\Pages;

use App\Filament\Resources\ClassTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassTask extends EditRecord
{
    protected static string $resource = ClassTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
