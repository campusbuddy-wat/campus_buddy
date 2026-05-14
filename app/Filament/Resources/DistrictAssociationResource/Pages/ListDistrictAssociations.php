<?php

namespace App\Filament\Resources\DistrictAssociationResource\Pages;

use App\Filament\Resources\DistrictAssociationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDistrictAssociations extends ListRecords
{
    protected static string $resource = DistrictAssociationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
