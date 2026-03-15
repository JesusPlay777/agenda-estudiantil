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

class TeacherAssignmentSubmissionController extends Controller
{
    use ResolvesAssignmentSubmissionStatus;

    public function index(Assignment $assignment): View
    {
        /** @var User $user */
        $user = auth()->user();

        $assignment = $this->ensureAssignmentBelongsToTeacher($user, $assignment);

        $students = $this->buildStudentSubmissionCollection($assignment);

        return view('teacher.assignments.submissions.index', [
            'assignment' => $assignment,
            'students' => $students,
        ]);
    }

    public function show(Assignment $assignment, AssignmentSubmission $submission): View
    {
        /** @var User $user */
        $user = auth()->user();

        $assignment = $this->ensureAssignmentBelongsToTeacher($user, $assignment);
        $submission = $this->ensureSubmissionBelongsToAssignment($assignment, $submission);

        return view('teacher.assignments.submissions.show', [
            'assignment' => $assignment,
            'submission' => $submission,
            'submissionStatus' => $this->resolveSubmissionStatus($assignment, $submission),
        ]);
    }

    public function review(Request $request, Assignment $assignment, AssignmentSubmission $submission): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $assignment = $this->ensureAssignmentBelongsToTeacher($user, $assignment);
        $submission = $this->ensureSubmissionBelongsToAssignment($assignment, $submission);

        /** @var array{score: numeric-string|int|float|null, teacher_feedback: string|null} $validated */
        $validated = $request->validate([
            'score' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'teacher_feedback' => ['nullable', 'string', 'max:5000'],
        ]);

        $hasReviewData = filled($validated['score'] ?? null) || filled($validated['teacher_feedback'] ?? null);

        $submission->update([
            'score' => filled($validated['score'] ?? null) ? $validated['score'] : null,
            'teacher_feedback' => filled($validated['teacher_feedback'] ?? null) ? trim((string) $validated['teacher_feedback']) : null,
            'reviewed_at' => $hasReviewData ? now() : null,
            'reviewed_by' => $hasReviewData ? $user->id : null,
        ]);

        return redirect()
            ->route('teacher.assignments.submissions.show', [$assignment, $submission])
            ->with('status', __('Review saved successfully.'));
    }

    /**
     * @return Collection<int, array{studentProfile: StudentProfile, student: ?User, submission: ?AssignmentSubmission, status: array{key: string, label: string, badge_class: string}}>
     */
    private function buildStudentSubmissionCollection(Assignment $assignment): Collection
    {
        $studentProfiles = StudentProfile::query()
            ->where('academic_section_id', $assignment->teachingAssignment->academic_section_id)
            ->whereHas('user', fn ($query) => $query->where('role', User::ROLE_STUDENT))
            ->with('user:id,name,email,role')
            ->get()
            ->sortBy(fn (StudentProfile $studentProfile): string => (string) $studentProfile->user?->name)
            ->values();

        $submissionsByStudent = AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->with([
                'student:id,name,email',
                'reviewedBy:id,name',
            ])
            ->get()
            ->keyBy('student_id');

        return $studentProfiles
            ->map(function (StudentProfile $studentProfile) use ($assignment, $submissionsByStudent): array {
                $submission = $submissionsByStudent->get($studentProfile->user_id);

                return [
                    'studentProfile' => $studentProfile,
                    'student' => $studentProfile->user,
                    'submission' => $submission,
                    'status' => $this->resolveSubmissionStatus($assignment, $submission),
                ];
            });
    }

    private function ensureAssignmentBelongsToTeacher(User $user, Assignment $assignment): Assignment
    {
        $owned = Assignment::query()
            ->whereKey($assignment->id)
            ->whereHas('teachingAssignment', fn ($query) => $query->where('teacher_id', $user->id))
            ->exists();

        if (! $owned) {
            abort(403);
        }

        return $assignment->load([
            'teachingAssignment.subject:id,name,code',
            'teachingAssignment.academicSection:id,name,school_year',
            'teachingAssignment.teacher:id,name,email',
        ]);
    }

    private function ensureSubmissionBelongsToAssignment(Assignment $assignment, AssignmentSubmission $submission): AssignmentSubmission
    {
        if ($submission->assignment_id !== $assignment->id) {
            abort(403);
        }

        return $submission->load([
            'student:id,name,email',
            'reviewedBy:id,name',
        ]);
    }
}
