<?php

namespace App\Filament\Resources\TeachingAssignments\Pages;

use App\Filament\Resources\TeachingAssignments\Pages\Concerns\ValidatesTeachingAssignmentData;
use App\Filament\Resources\TeachingAssignments\TeachingAssignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTeachingAssignment extends EditRecord
{
    use ValidatesTeachingAssignmentData;

    protected static string $resource = TeachingAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->validateTeachingAssignmentData($data, $this->record);
    }
}
