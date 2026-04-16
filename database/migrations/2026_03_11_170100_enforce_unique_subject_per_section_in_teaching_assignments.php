<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('teaching_assignments')
            ->select('academic_section_id', 'subject_id', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('academic_section_id', 'subject_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicates) {
            throw new RuntimeException('Duplicate teaching assignments found for the same section and subject. Resolve them before running this migration.');
        }

        Schema::table('teaching_assignments', function (Blueprint $table): void {
            $table->unique(
                ['academic_section_id', 'subject_id'],
                'teaching_section_subject_unique',
            );
        });

        Schema::table('teaching_assignments', function (Blueprint $table): void {
            $table->dropUnique('teaching_unique');
        });
    }

    public function down(): void
    {
        Schema::table('teaching_assignments', function (Blueprint $table): void {
            $table->unique(
                ['academic_section_id', 'subject_id', 'teacher_id'],
                'teaching_unique',
            );
        });

        Schema::table('teaching_assignments', function (Blueprint $table): void {
            $table->dropUnique('teaching_section_subject_unique');
        });
    }
};
