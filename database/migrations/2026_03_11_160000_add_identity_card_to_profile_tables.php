<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('identity_card')->nullable()->after('academic_section_id');
            $table->unique('identity_card');
        });

        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->string('identity_card')->nullable()->after('user_id');
            $table->unique('identity_card');
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropUnique(['identity_card']);
            $table->dropColumn('identity_card');
        });

        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->dropUnique(['identity_card']);
            $table->dropColumn('identity_card');
        });
    }
};
