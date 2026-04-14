<?php

namespace Database\Seeders;

use App\Models\AcademicSection;
use App\Models\Assignment;
use App\Models\Schedule;
use App\Models\TeachingAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FourthYearASectionSeeder extends Seeder
{
    public function run(): void
    {
        $section = AcademicSection::query()
            ->where('school_year', '2025-2026')
            ->get()
            ->first(fn (AcademicSection $candidate): bool => Str::ascii($candidate->name) === '4to Ano A');

        if (! $section) {
            return;
        }

        $teachingAssignments = TeachingAssignment::query()
            ->where('academic_section_id', $section->id)
            ->where('is_active', true)
            ->with([
                'subject:id,name,code',
                'teacher:id,name',
            ])
            ->get()
            ->sortBy(fn (TeachingAssignment $assignment): string => (string) $assignment->subject?->code)
            ->values();

        if ($teachingAssignments->isEmpty()) {
            return;
        }

        $scheduleTemplates = [
            'MAT' => [
                ['weekday' => 'lunes', 'start_time' => '08:00:00', 'end_time' => '09:30:00'],
                ['weekday' => 'jueves', 'start_time' => '08:00:00', 'end_time' => '09:30:00'],
                ['weekday' => 'martes', 'start_time' => '13:20:00', 'end_time' => '14:00:00'],
            ],
            'ING' => [
                ['weekday' => 'miercoles', 'start_time' => '10:00:00', 'end_time' => '11:30:00'],
                ['weekday' => 'viernes', 'start_time' => '10:00:00', 'end_time' => '11:30:00'],
                ['weekday' => 'jueves', 'start_time' => '13:10:00', 'end_time' => '13:50:00'],
            ],
            'HIS' => [
                ['weekday' => 'martes', 'start_time' => '09:00:00', 'end_time' => '10:30:00'],
                ['weekday' => 'jueves', 'start_time' => '10:00:00', 'end_time' => '11:30:00'],
                ['weekday' => 'viernes', 'start_time' => '14:40:00', 'end_time' => '15:20:00'],
            ],
        ];

        $assignmentTemplates = [
            'MAT' => [
                [
                    'title' => 'Taller de ecuaciones lineales',
                    'description' => 'Resuelve 10 ecuaciones lineales y explica el procedimiento usado en cada caso.',
                ],
                [
                    'title' => 'Practica de fracciones equivalentes',
                    'description' => 'Simplifica y compara fracciones equivalentes usando ejemplos del cuaderno.',
                ],
            ],
            'ING' => [
                [
                    'title' => 'Vocabulary worksheet: daily routines',
                    'description' => 'Completa la guia de vocabulario sobre rutinas diarias y escribe cinco oraciones propias.',
                ],
                [
                    'title' => 'Reading comprehension: school life',
                    'description' => 'Lee el texto asignado y responde las preguntas de comprension en ingles.',
                ],
            ],
            'HIS' => [
                [
                    'title' => 'Linea de tiempo de procesos historicos',
                    'description' => 'Elabora una linea de tiempo con los hechos historicos vistos en clase.',
                ],
                [
                    'title' => 'Resumen de civilizaciones antiguas',
                    'description' => 'Redacta un resumen comparando dos civilizaciones antiguas estudiadas en clase.',
                ],
            ],
        ];

        $fallbackScheduleTemplates = [
            [
                ['weekday' => 'lunes', 'start_time' => '07:00:00', 'end_time' => '08:30:00'],
                ['weekday' => 'miercoles', 'start_time' => '07:00:00', 'end_time' => '08:30:00'],
            ],
            [
                ['weekday' => 'martes', 'start_time' => '08:00:00', 'end_time' => '09:30:00'],
                ['weekday' => 'jueves', 'start_time' => '08:00:00', 'end_time' => '09:30:00'],
            ],
            [
                ['weekday' => 'viernes', 'start_time' => '09:00:00', 'end_time' => '10:30:00'],
            ],
        ];

        foreach ($teachingAssignments as $index => $teachingAssignment) {
            $subjectCode = strtoupper((string) $teachingAssignment->subject?->code);

            $this->seedSchedules(
                $teachingAssignment,
                collect($scheduleTemplates[$subjectCode] ?? $fallbackScheduleTemplates[$index % count($fallbackScheduleTemplates)]),
            );

            $this->seedAssignments(
                $teachingAssignment,
                collect($assignmentTemplates[$subjectCode] ?? $this->buildFallbackAssignments($teachingAssignment)),
                $index,
            );
        }
    }

    /**
     * @param  Collection<int, array{weekday: string, start_time: string, end_time: string}>  $templates
     */
    protected function seedSchedules(TeachingAssignment $teachingAssignment, Collection $templates): void
    {
        foreach ($templates as $template) {
            Schedule::updateOrCreate(
                [
                    'teaching_assignment_id' => $teachingAssignment->id,
                    'weekday' => $template['weekday'],
                    'start_time' => $template['start_time'],
                    'end_time' => $template['end_time'],
                ],
                [],
            );
        }
    }

    /**
     * @param  Collection<int, array{title: string, description: string}>  $templates
     */
    protected function seedAssignments(TeachingAssignment $teachingAssignment, Collection $templates, int $assignmentOffset): void
    {
        foreach ($templates->values() as $index => $template) {
            Assignment::updateOrCreate(
                [
                    'teaching_assignment_id' => $teachingAssignment->id,
                    'title' => $template['title'],
                ],
                [
                    'description' => $template['description'],
                    'due_date' => now()->addDays(4 + $assignmentOffset + ($index * 4))->toDateString(),
                    'published_at' => now()->subDays($index + 1),
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    protected function buildFallbackAssignments(TeachingAssignment $teachingAssignment): array
    {
        $subjectName = $teachingAssignment->subject?->name ?? 'Materia';

        return [
            [
                'title' => 'Actividad guiada - '.$subjectName,
                'description' => 'Desarrolla la actividad guiada correspondiente a '.$subjectName.' y entregala en clase.',
            ],
            [
                'title' => 'Resumen de contenidos - '.$subjectName,
                'description' => 'Prepara un resumen breve con los contenidos vistos en la ultima clase de '.$subjectName.'.',
            ],
        ];
    }
}
