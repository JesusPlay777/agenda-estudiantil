<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\Concerns\ResolvesAssignmentSubmissionStatus;
use App\Http\Controllers\Dashboard\Concerns\SortsSchedulesByWeekday;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Schedule;
use App\Models\StudentProfile;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Contracts\View\View;

class StudentDashboardController extends Controller
{
    use ResolvesAssignmentSubmissionStatus;
    use SortsSchedulesByWeekday;

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
                'subjectTeachers' => collect(),
                'schedules' => collect(),
                'scheduleGroups' => collect(),
                'recentAssignments' => collect(),
                'recentSubmissionsByAssignment' => collect(),
                'recentStatusesByAssignment' => collect(),
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

        $subjectTeachers = $teachingAssignments
            ->filter()
            ->sortBy(fn (TeachingAssignment $assignment): string => (string) $assignment->subject?->name)
            ->values();

        $schedules = Schedule::query()
            ->whereIn('teaching_assignment_id', $assignmentIds)
            ->with([
                'teachingAssignment.subject:id,name',
                'teachingAssignment.teacher:id,name',
            ])
            ->get();

        $schedules = $this->sortSchedulesByWeekday($schedules);
        $scheduleGroups = $this->groupSchedulesByWeekday($schedules);

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

        $recentSubmissionsByAssignment = AssignmentSubmission::query()
            ->where('student_id', $user->id)
            ->whereIn('assignment_id', $recentAssignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        $recentStatusesByAssignment = $recentAssignments
            ->mapWithKeys(function (Assignment $assignment) use ($recentSubmissionsByAssignment): array {
                $submission = $recentSubmissionsByAssignment->get($assignment->id);

                return [
                    $assignment->id => $this->resolveSubmissionStatus($assignment, $submission),
                ];
            });

        return view('dashboards.student', [
            'studentProfile' => $studentProfile,
            'subjectTeachers' => $subjectTeachers,
            'schedules' => $schedules,
            'scheduleGroups' => $scheduleGroups,
            'recentAssignments' => $recentAssignments,
            'recentSubmissionsByAssignment' => $recentSubmissionsByAssignment,
            'recentStatusesByAssignment' => $recentStatusesByAssignment,
        ]);
    }
}
