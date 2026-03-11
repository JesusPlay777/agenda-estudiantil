<?php

namespace Database\Seeders;

use App\Models\AcademicSection;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class AcademicCatalogSeeder extends Seeder
{
    /**
     * Seed academic sections and subjects.
     */
    public function run(): void
    {
        $sections = [
            ['name' => '5to Ano A', 'school_year' => '2026-2027'],
            ['name' => '5to Ano B', 'school_year' => '2026-2027'],
            ['name' => '4to Ano A', 'school_year' => '2026-2027'],
        ];

        foreach ($sections as $section) {
            AcademicSection::updateOrCreate(
                [
                    'name' => $section['name'],
                    'school_year' => $section['school_year'],
                ],
                ['is_active' => true],
            );
        }

        $subjects = [
            ['name' => 'Matematicas', 'code' => 'MAT'],
            ['name' => 'Ingles', 'code' => 'ING'],
            ['name' => 'Castellano', 'code' => 'CAS'],
            ['name' => 'Ciencias Naturales', 'code' => 'NAT'],
            ['name' => 'Historia', 'code' => 'HIS'],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['name' => $subject['name']],
                [
                    'code' => $subject['code'],
                    'is_active' => true,
                ],
            );
        }
    }
}
