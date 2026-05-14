<?php

namespace App\Filament\Resources\DistrictAssociationResource\Pages;

use App\Filament\Resources\DistrictAssociationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDistrictAssociation extends EditRecord
{
    protected static string $resource = DistrictAssociationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
