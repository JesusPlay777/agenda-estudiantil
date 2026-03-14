<?php

namespace App\Filament\Resources\TeachingAssignments\Pages;

use App\Filament\Resources\TeachingAssignments\Pages\Concerns\ValidatesTeachingAssignmentData;
use App\Filament\Resources\TeachingAssignments\TeachingAssignmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTeachingAssignment extends CreateRecord
{
    use ValidatesTeachingAssignmentData;

    protected static string $resource = TeachingAssignmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->validateTeachingAssignmentData($data);
    }
}
