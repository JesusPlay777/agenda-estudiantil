<?php

namespace App\Filament\Resources\Users\Pages\Concerns;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Validation\ValidationException;

trait SynchronizesStudentProfile
{
    protected ?int $studentAcademicSectionId = null;

    protected function rememberStudentAcademicSectionId(?int $academicSectionId): void
    {
        $this->studentAcademicSectionId = filled($academicSectionId) ? $academicSectionId : null;
    }

    protected function synchronizeStudentProfile(User $user): void
    {
        if ($user->role !== User::ROLE_STUDENT) {
            $user->studentProfile()?->delete();

            return;
        }

        if (! filled($this->studentAcademicSectionId)) {
            throw ValidationException::withMessages([
                'data.academic_section_id' => __('validation.required', ['attribute' => __('ui.fields.academic_section')]),
            ]);
        }

        StudentProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['academic_section_id' => $this->studentAcademicSectionId],
        );
    }
}
