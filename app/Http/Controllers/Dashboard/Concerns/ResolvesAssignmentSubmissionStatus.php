<?php

namespace App\Http\Controllers\Dashboard\Concerns;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Carbon;

trait ResolvesAssignmentSubmissionStatus
{
    /**
     * @return array{key: string, label: string, badge_class: string}
     */
    protected function resolveSubmissionStatus(Assignment $assignment, ?AssignmentSubmission $submission): array
    {
        if (! $submission?->submitted_at) {
            if ($assignment->due_date && Carbon::now()->gt($assignment->due_date->copy()->endOfDay())) {
                return $this->statusMeta('overdue');
            }

            return $this->statusMeta('pending');
        }

        if (! $assignment->due_date || $submission->submitted_at->lte($assignment->due_date->copy()->endOfDay())) {
            return $this->statusMeta('submitted_on_time');
        }

        return $this->statusMeta('submitted_late');
    }

    /**
     * @return array{key: string, label: string, badge_class: string}
     */
    private function statusMeta(string $key): array
    {
        return match ($key) {
            'pending' => [
                'key' => 'pending',
                'label' => __('Pending'),
                'badge_class' => 'border-amber-300 bg-amber-50 text-amber-900 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-100',
            ],
            'overdue' => [
                'key' => 'overdue',
                'label' => __('Overdue'),
                'badge_class' => 'border-red-300 bg-red-50 text-red-900 dark:border-red-700 dark:bg-red-900/20 dark:text-red-100',
            ],
            'submitted_late' => [
                'key' => 'submitted_late',
                'label' => __('Submitted late'),
                'badge_class' => 'border-orange-300 bg-orange-50 text-orange-900 dark:border-orange-700 dark:bg-orange-900/20 dark:text-orange-100',
            ],
            default => [
                'key' => 'submitted_on_time',
                'label' => __('Submitted on time'),
                'badge_class' => 'border-emerald-300 bg-emerald-50 text-emerald-900 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-100',
            ],
        };
    }
}
