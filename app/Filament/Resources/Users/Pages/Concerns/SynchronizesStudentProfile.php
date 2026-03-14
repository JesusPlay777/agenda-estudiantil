<?php

namespace App\Filament\Resources\Users\Pages\Concerns;

use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Validation\ValidationException;

trait SynchronizesStudentProfile
{
    protected ?int $studentAcademicSectionId = null;

    protected ?string $profileIdentityCard = null;

    protected ?string $profilePhone = null;

    protected function rememberStudentAcademicSectionId(?int $academicSectionId): void
    {
        $this->studentAcademicSectionId = filled($academicSectionId) ? $academicSectionId : null;
    }

    protected function rememberProfileIdentityCard(?string $identityCard): void
    {
        $this->profileIdentityCard = filled($identityCard) ? strtoupper($identityCard) : null;
    }

    protected function rememberProfilePhone(?string $phone): void
    {
        $this->profilePhone = filled($phone) ? $phone : null;
    }

    protected function synchronizeStudentProfile(User $user): void
    {
        if ($user->role === User::ROLE_STUDENT) {
            if (! filled($this->studentAcademicSectionId)) {
                throw ValidationException::withMessages([
                    'data.academic_section_id' => __('validation.required', ['attribute' => __('ui.fields.academic_section')]),
                ]);
            }

            if (! filled($this->profileIdentityCard)) {
                throw ValidationException::withMessages([
                    'data.identity_card' => __('validation.required', ['attribute' => __('ui.fields.identity_card')]),
                ]);
            }

            StudentProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'academic_section_id' => $this->studentAcademicSectionId,
                    'identity_card' => $this->profileIdentityCard,
                    'phone' => $this->profilePhone,
                ],
            );

            $user->teacherProfile()?->delete();

            return;
        }

        if ($user->role === User::ROLE_TEACHER) {
            if (! filled($this->profileIdentityCard)) {
                throw ValidationException::withMessages([
                    'data.identity_card' => __('validation.required', ['attribute' => __('ui.fields.identity_card')]),
                ]);
            }

            TeacherProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'identity_card' => $this->profileIdentityCard,
                    'phone' => $this->profilePhone,
                ],
            );

            $user->studentProfile()?->delete();

            return;
        }

        $user->studentProfile()?->delete();
        $user->teacherProfile()?->delete();
    }
}
