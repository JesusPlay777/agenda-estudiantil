<?php

namespace Database\Seeders;

use App\Models\AcademicSection;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Schedule;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademicDomainSeeder extends Seeder
{
    /**
     * Seed profiles and domain relationships.
     */
    public function run(): void
    {
        $teacher = User::where('email', 'jrojastest777@gmail.com')
            ->orWhere('role', User::ROLE_TEACHER)
            ->first();

        $student = User::where('email', 'francisco.francisco.miranda@gmail.com')
            ->orWhere('role', User::ROLE_STUDENT)
            ->first();

        $section = AcademicSection::query()
            ->where('name', '5to Ano A')
            ->where('school_year', '2026-2027')
            ->first();

        if (! $teacher || ! $student || ! $section) {
            return;
        }

        TeacherProfile::updateOrCreate(
            ['user_id' => $teacher->id],
            [
                'identity_card' => 'V10000001',
                'phone' => '04121234567',
            ],
        );

        StudentProfile::updateOrCreate(
            ['user_id' => $student->id],
            [
                'academic_section_id' => $section->id,
                'identity_card' => 'V10000002',
                'phone' => '04141234567',
            ],
        );

        $subjectCodesForTeacher = ['MAT', 'ING', 'CAS'];
        $subjects = Subject::query()
            ->whereIn('code', $subjectCodesForTeacher)
            ->orderBy('name')
            ->get();

        $teacher->specializedSubjects()->sync($subjects->pluck('id')->all());

        $seededAssignments = [];
        $seededHomework = [];

        foreach ($subjects as $subject) {
            $teachingAssignment = TeachingAssignment::updateOrCreate(
                [
                    'academic_section_id' => $section->id,
                    'subject_id' => $subject->id,
                ],
                [
                    'teacher_id' => $teacher->id,
                    'is_active' => true,
                ],
            );

            $seededAssignments[] = $teachingAssignment;
        }

        $weekdayBlocks = [
            ['weekday' => 'lunes', 'start_time' => '08:00:00', 'end_time' => '09:30:00'],
            ['weekday' => 'miercoles', 'start_time' => '10:00:00', 'end_time' => '11:30:00'],
            ['weekday' => 'viernes', 'start_time' => '09:00:00', 'end_time' => '10:30:00'],
        ];

        foreach ($seededAssignments as $index => $teachingAssignment) {
            $block = $weekdayBlocks[$index % count($weekdayBlocks)];

            Schedule::updateOrCreate(
                [
                    'teaching_assignment_id' => $teachingAssignment->id,
                    'weekday' => $block['weekday'],
                    'start_time' => $block['start_time'],
                    'end_time' => $block['end_time'],
                ],
                [],
            );

            $assignment = Assignment::updateOrCreate(
                [
                    'teaching_assignment_id' => $teachingAssignment->id,
                    'title' => 'Tarea diagnostica - '.$teachingAssignment->subject->name,
                ],
                [
                    'description' => 'Actividad inicial para evaluar conocimientos previos.',
                    'due_date' => now()->addDays(7 + ($index * 2))->toDateString(),
                    'published_at' => now(),
                    'is_active' => true,
                ],
            );

            $seededHomework[] = $assignment;
        }

        $firstHomework = $seededHomework[0] ?? null;

        if ($firstHomework) {
            AssignmentSubmission::updateOrCreate(
                [
                    'assignment_id' => $firstHomework->id,
                    'student_id' => $student->id,
                ],
                [
                    'submission_text' => 'Entrega inicial de diagnostico del estudiante.',
                    'submitted_at' => now()->subDay(),
                ],
            );
        }
    }
}
