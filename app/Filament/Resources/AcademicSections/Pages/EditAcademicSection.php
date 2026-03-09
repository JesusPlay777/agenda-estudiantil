<?php

namespace App\Filament\Resources\AcademicSections\Pages;

use App\Filament\Resources\AcademicSections\AcademicSectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAcademicSection extends EditRecord
{
    protected static string $resource = AcademicSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
