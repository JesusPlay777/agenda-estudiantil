<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Users\Pages\Concerns\SynchronizesStudentProfile;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use SynchronizesStudentProfile;

    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->rememberStudentAcademicSectionId(
            filled($data['academic_section_id'] ?? null)
                ? (int) $data['academic_section_id']
                : null,
        );

        unset($data['academic_section_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Model $record */
        $record = $this->record;

        $this->synchronizeStudentProfile($record);
    }
}
