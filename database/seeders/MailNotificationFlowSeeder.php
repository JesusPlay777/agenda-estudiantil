<?php

namespace Database\Seeders;

use App\Models\AcademicSection;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MailNotificationFlowSeeder extends Seeder
{
    /**
     * Seed only the minimum data required to test the assignment email flow.
     */
    public function run(): void
    {
        $student = User::updateOrCreate(
            ['email' => 'francisco.francisco.miranda@gmail.com'],
            [
                'name' => 'Francisco Miranda',
                'role' => User::ROLE_STUDENT,
                'preferred_locale' => 'es',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $teacher = User::updateOrCreate(
            ['email' => 'docente.pruebas.correo@agenda-estudiantil.test'],
            [
                'name' => 'Docente Pruebas Correo',
                'role' => User::ROLE_TEACHER,
                'preferred_locale' => 'es',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $section = AcademicSection::updateOrCreate(
            [
                'name' => '4to Ano Pruebas Correo',
                'school_year' => '2026-2027',
            ],
            [
                'is_active' => true,
            ],
        );

        $subject = Subject::updateOrCreate(
            ['name' => 'Matematicas'],
            [
                'code' => 'MAT',
                'is_active' => true,
            ],
        );

        TeacherProfile::updateOrCreate(
            ['user_id' => $teacher->id],
            [
                'identity_card' => 'V20000001',
                'phone' => '04121230001',
            ],
        );

        StudentProfile::updateOrCreate(
            ['user_id' => $student->id],
            [
                'academic_section_id' => $section->id,
                'identity_card' => 'V20000002',
                'phone' => '04121230002',
            ],
        );

        $teacher->specializedSubjects()->syncWithoutDetaching([$subject->id]);

        TeachingAssignment::updateOrCreate(
            [
                'academic_section_id' => $section->id,
                'subject_id' => $subject->id,
            ],
            [
                'teacher_id' => $teacher->id,
                'is_active' => true,
            ],
        );
    }
}
