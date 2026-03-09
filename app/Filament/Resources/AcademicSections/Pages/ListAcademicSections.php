<?php

namespace App\Filament\Resources\AcademicSections\Pages;

use App\Filament\Resources\AcademicSections\AcademicSectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAcademicSections extends ListRecords
{
    protected static string $resource = AcademicSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
