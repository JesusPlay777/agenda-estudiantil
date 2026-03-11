<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Contracts\View\View;

class TeacherDashboardController extends Controller
{
    public function __invoke(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $teachingAssignments = $user->teachingAssignments()
            ->where('is_active', true)
            ->with([
                'subject:id,name,code',
                'academicSection:id,name,school_year',
            ])
            ->get();

        $assignmentIds = $teachingAssignments->pluck('id');

        $subjects = $teachingAssignments
            ->pluck('subject')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $sections = $teachingAssignments
            ->pluck('academicSection')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $weekdayOrder = [
            'lunes' => 1,
            'martes' => 2,
            'miercoles' => 3,
            'jueves' => 4,
            'viernes' => 5,
        ];

        $schedules = Schedule::query()
            ->whereIn('teaching_assignment_id', $assignmentIds)
            ->with([
                'teachingAssignment.subject:id,name',
                'teachingAssignment.academicSection:id,name,school_year',
            ])
            ->get()
            ->sortBy([
                fn (Schedule $schedule): int => $weekdayOrder[$schedule->weekday] ?? 99,
                'start_time',
            ])
            ->values();

        $recentAssignments = Assignment::query()
            ->whereIn('teaching_assignment_id', $assignmentIds)
            ->where('is_active', true)
            ->with([
                'teachingAssignment.subject:id,name',
                'teachingAssignment.academicSection:id,name,school_year',
            ])
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('dashboards.teacher', [
            'subjects' => $subjects,
            'sections' => $sections,
            'schedules' => $schedules,
            'recentAssignments' => $recentAssignments,
        ]);
    }
}
