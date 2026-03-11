<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Schedule;
use App\Models\StudentProfile;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Contracts\View\View;

class StudentDashboardController extends Controller
{
    public function __invoke(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $studentProfile = StudentProfile::query()
            ->where('user_id', $user->id)
            ->with('academicSection:id,name,school_year')
            ->first();

        if (! $studentProfile) {
            return view('dashboards.student', [
                'studentProfile' => null,
                'subjects' => collect(),
                'teachers' => collect(),
                'schedules' => collect(),
                'recentAssignments' => collect(),
            ]);
        }

        $teachingAssignments = TeachingAssignment::query()
            ->where('academic_section_id', $studentProfile->academic_section_id)
            ->where('is_active', true)
            ->with([
                'subject:id,name,code',
                'teacher:id,name,email',
            ])
            ->get();

        $assignmentIds = $teachingAssignments->pluck('id');

        $subjects = $teachingAssignments
            ->pluck('subject')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $teachers = $teachingAssignments
            ->pluck('teacher')
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
                'teachingAssignment.teacher:id,name',
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
                'teachingAssignment.teacher:id,name',
            ])
            ->orderBy('due_date')
            ->orderByDesc('published_at')
            ->limit(8)
            ->get();

        return view('dashboards.student', [
            'studentProfile' => $studentProfile,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'schedules' => $schedules,
            'recentAssignments' => $recentAssignments,
        ]);
    }
}
