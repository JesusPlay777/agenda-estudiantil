<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Pages\Concerns\SynchronizesStudentProfile;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use SynchronizesStudentProfile;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => $this->record->getKey() !== auth()->id()),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['academic_section_id'] = $this->record->studentProfile?->academic_section_id;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->rememberStudentAcademicSectionId(
            filled($data['academic_section_id'] ?? null)
                ? (int) $data['academic_section_id']
                : null,
        );

        unset($data['academic_section_id']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->synchronizeStudentProfile($this->record);
    }
}
