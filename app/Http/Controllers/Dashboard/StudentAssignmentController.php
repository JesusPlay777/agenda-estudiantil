<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\Concerns\ResolvesAssignmentSubmissionStatus;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StudentAssignmentController extends Controller
{
    use ResolvesAssignmentSubmissionStatus;

    public function index(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $studentProfile = $this->getStudentProfile($user->id);

        if (! $studentProfile) {
            return view('student.assignments.index', [
                'studentProfile' => null,
                'assignments' => collect(),
                'submissionsByAssignment' => collect(),
                'statusesByAssignment' => collect(),
            ]);
        }

        $assignments = Assignment::query()
            ->where('is_active', true)
            ->whereHas('teachingAssignment', function ($query) use ($studentProfile): void {
                $query
                    ->where('academic_section_id', $studentProfile->academic_section_id)
                    ->where('is_active', true);
            })
            ->with([
                'teachingAssignment.subject:id,name,code',
                'teachingAssignment.teacher:id,name',
                'teachingAssignment.academicSection:id,name,school_year',
            ])
            ->orderBy('due_date')
            ->orderByDesc('published_at')
            ->paginate(12);

        $submissionsByAssignment = AssignmentSubmission::query()
            ->where('student_id', $user->id)
            ->whereIn('assignment_id', $assignments->getCollection()->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        $statusesByAssignment = $assignments->getCollection()
            ->mapWithKeys(function (Assignment $assignment) use ($submissionsByAssignment): array {
                $submission = $submissionsByAssignment->get($assignment->id);

                return [
                    $assignment->id => $this->resolveSubmissionStatus($assignment, $submission),
                ];
            });

        return view('student.assignments.index', [
            'studentProfile' => $studentProfile,
            'assignments' => $assignments,
            'submissionsByAssignment' => $submissionsByAssignment,
            'statusesByAssignment' => $statusesByAssignment,
        ]);
    }

    public function show(Assignment $assignment): View
    {
        /** @var User $user */
        $user = auth()->user();

        $studentProfile = $this->getStudentProfile($user->id);

        if (! $studentProfile) {
            abort(403);
        }

        $assignment = $this->ensureAssignmentBelongsToStudent($assignment, $studentProfile->academic_section_id);

        $submission = AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('student_id', $user->id)
            ->first();

        return view('student.assignments.show', [
            'assignment' => $assignment,
            'submission' => $submission,
            'submissionStatus' => $this->resolveSubmissionStatus($assignment, $submission),
        ]);
    }

    public function submit(Request $request, Assignment $assignment): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $studentProfile = $this->getStudentProfile($user->id);

        if (! $studentProfile) {
            abort(403);
        }

        $assignment = $this->ensureAssignmentBelongsToStudent($assignment, $studentProfile->academic_section_id);

        /** @var array{submission_text: string} $validated */
        $validated = $request->validate([
            'submission_text' => ['required', 'string', 'max:5000'],
        ]);

        AssignmentSubmission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $user->id,
            ],
            [
                'submission_text' => $validated['submission_text'],
                'submitted_at' => now(),
            ],
        );

        return redirect()
            ->route('student.assignments.show', $assignment)
            ->with('status', __('Submission saved successfully.'));
    }

    private function getStudentProfile(int $userId): ?StudentProfile
    {
        return StudentProfile::query()
            ->where('user_id', $userId)
            ->with('academicSection:id,name,school_year')
            ->first();
    }

    private function ensureAssignmentBelongsToStudent(Assignment $assignment, int $academicSectionId): Assignment
    {
        $allowed = Assignment::query()
            ->whereKey($assignment->id)
            ->where('is_active', true)
            ->whereHas('teachingAssignment', function ($query) use ($academicSectionId): void {
                $query
                    ->where('academic_section_id', $academicSectionId)
                    ->where('is_active', true);
            })
            ->exists();

        if (! $allowed) {
            abort(403);
        }

        return $assignment->load([
            'teachingAssignment.subject:id,name,code',
            'teachingAssignment.teacher:id,name',
            'teachingAssignment.academicSection:id,name,school_year',
        ]);
    }
}
