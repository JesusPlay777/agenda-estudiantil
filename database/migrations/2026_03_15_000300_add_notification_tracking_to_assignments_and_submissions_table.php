<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table): void {
            $table->timestamp('published_notification_sent_at')
                ->nullable()
                ->after('published_at');
        });

        Schema::table('assignment_submissions', function (Blueprint $table): void {
            $table->timestamp('review_notification_sent_at')
                ->nullable()
                ->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table): void {
            $table->dropColumn('published_notification_sent_at');
        });

        Schema::table('assignment_submissions', function (Blueprint $table): void {
            $table->dropColumn('review_notification_sent_at');
        });
    }
};
