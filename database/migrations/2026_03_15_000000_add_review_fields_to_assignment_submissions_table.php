<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table): void {
            $table->decimal('score', 5, 2)->nullable()->after('submitted_at');
            $table->text('teacher_feedback')->nullable()->after('score');
            $table->timestamp('reviewed_at')->nullable()->after('teacher_feedback');
            $table->foreignId('reviewed_by')
                ->nullable()
                ->after('reviewed_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn([
                'score',
                'teacher_feedback',
                'reviewed_at',
            ]);
        });
    }
};
