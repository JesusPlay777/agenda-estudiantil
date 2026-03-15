<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\Concerns\SortsSchedulesByWeekday;
use App\Models\Assignment;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Contracts\View\View;

class TeacherDashboardController extends Controller
{
    use SortsSchedulesByWeekday;

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

        $schedules = Schedule::query()
            ->whereIn('teaching_assignment_id', $assignmentIds)
            ->with([
                'teachingAssignment.subject:id,name',
                'teachingAssignment.academicSection:id,name,school_year',
            ])
            ->get();

        $schedules = $this->sortSchedulesByWeekday($schedules);
        $scheduleGroups = $this->groupSchedulesByWeekday($schedules);

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
            'scheduleGroups' => $scheduleGroups,
            'recentAssignments' => $recentAssignments,
        ]);
    }
}
