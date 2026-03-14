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
        $data['identity_card'] = match ($this->record->role) {
            \App\Models\User::ROLE_STUDENT => $this->record->studentProfile?->identity_card,
            \App\Models\User::ROLE_TEACHER => $this->record->teacherProfile?->identity_card,
            default => null,
        };
        $data['phone'] = match ($this->record->role) {
            \App\Models\User::ROLE_STUDENT => $this->record->studentProfile?->phone,
            \App\Models\User::ROLE_TEACHER => $this->record->teacherProfile?->phone,
            default => null,
        };

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->rememberStudentAcademicSectionId(
            filled($data['academic_section_id'] ?? null)
                ? (int) $data['academic_section_id']
                : null,
        );
        $this->rememberProfileIdentityCard($data['identity_card'] ?? null);
        $this->rememberProfilePhone($data['phone'] ?? null);

        unset($data['academic_section_id']);
        unset($data['identity_card']);
        unset($data['phone']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->synchronizeStudentProfile($this->record);
    }
}
