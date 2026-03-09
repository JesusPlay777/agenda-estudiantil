<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('student')->after('email');
            });
        }

        if ($this->isAcademicSchemaComplete()) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('assignments');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('teacher_profiles');
        Schema::dropIfExists('teaching_assignments');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('academic_sections');

        Schema::create('academic_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('school_year');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['name', 'school_year']);
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('name');
        });

        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('phone')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_section_id')->constrained()->cascadeOnDelete();
            $table->string('phone')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });

        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['academic_section_id', 'subject_id', 'teacher_id'],
                'teaching_unique',
            );
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_assignment_id')->constrained()->cascadeOnDelete();
            $table->string('weekday');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_assignment_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->date('due_date')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Intentionally left as a no-op to avoid destructive rollback behavior.
    }

    private function isAcademicSchemaComplete(): bool
    {
        return
            Schema::hasTable('academic_sections')
            && Schema::hasColumn('academic_sections', 'name')
            && Schema::hasColumn('academic_sections', 'school_year')
            && Schema::hasColumn('academic_sections', 'is_active')
            && Schema::hasTable('subjects')
            && Schema::hasColumn('subjects', 'name')
            && Schema::hasColumn('subjects', 'code')
            && Schema::hasColumn('subjects', 'is_active')
            && Schema::hasTable('student_profiles')
            && Schema::hasColumn('student_profiles', 'user_id')
            && Schema::hasColumn('student_profiles', 'academic_section_id')
            && Schema::hasColumn('student_profiles', 'phone')
            && Schema::hasTable('teacher_profiles')
            && Schema::hasColumn('teacher_profiles', 'user_id')
            && Schema::hasColumn('teacher_profiles', 'phone')
            && Schema::hasTable('teaching_assignments')
            && Schema::hasColumn('teaching_assignments', 'academic_section_id')
            && Schema::hasColumn('teaching_assignments', 'subject_id')
            && Schema::hasColumn('teaching_assignments', 'teacher_id')
            && Schema::hasColumn('teaching_assignments', 'is_active')
            && Schema::hasTable('schedules')
            && Schema::hasColumn('schedules', 'teaching_assignment_id')
            && Schema::hasColumn('schedules', 'weekday')
            && Schema::hasColumn('schedules', 'start_time')
            && Schema::hasColumn('schedules', 'end_time')
            && Schema::hasTable('assignments')
            && Schema::hasColumn('assignments', 'teaching_assignment_id')
            && Schema::hasColumn('assignments', 'title')
            && Schema::hasColumn('assignments', 'description')
            && Schema::hasColumn('assignments', 'due_date')
            && Schema::hasColumn('assignments', 'published_at')
            && Schema::hasColumn('assignments', 'is_active');
    }
};
