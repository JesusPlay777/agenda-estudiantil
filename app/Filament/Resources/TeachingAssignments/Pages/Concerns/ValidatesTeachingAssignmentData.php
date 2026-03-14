<?php

namespace App\Filament\Resources\TeachingAssignments\Pages\Concerns;

use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Validation\ValidationException;

trait ValidatesTeachingAssignmentData
{
    /**
     * @param  array{
     *     academic_section_id: int|string,
     *     subject_id: int|string,
     *     teacher_id: int|string,
     *     is_active: bool
     * }  $data
     * @return array{
     *     academic_section_id: int|string,
     *     subject_id: int|string,
     *     teacher_id: int|string,
     *     is_active: bool
     * }
     */
    protected function validateTeachingAssignmentData(array $data, ?TeachingAssignment $record = null): array
    {
        $duplicateExists = TeachingAssignment::query()
            ->where('academic_section_id', $data['academic_section_id'])
            ->where('subject_id', $data['subject_id'])
            ->when(
                filled($record?->getKey()),
                fn ($query) => $query->whereKeyNot($record->getKey()),
            )
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages([
                'data.subject_id' => __('A section can have only one teacher per subject.'),
            ]);
        }

        $teacherCanTeachSubject = User::query()
            ->whereKey($data['teacher_id'])
            ->where('role', User::ROLE_TEACHER)
            ->whereHas(
                'specializedSubjects',
                fn ($query) => $query->whereKey($data['subject_id']),
            )
            ->exists();

        if (! $teacherCanTeachSubject) {
            throw ValidationException::withMessages([
                'data.teacher_id' => __('The selected teacher is not authorized to teach this subject.'),
            ]);
        }

        return $data;
    }
}
