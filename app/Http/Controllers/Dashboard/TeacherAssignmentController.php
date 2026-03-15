<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TeacherAssignmentController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $assignments = Assignment::query()
            ->whereHas('teachingAssignment', fn ($query) => $query->where('teacher_id', $user->id))
            ->withCount('submissions')
            ->withCount([
                'submissions as reviewed_submissions_count' => fn ($query) => $query->whereNotNull('reviewed_at'),
            ])
            ->with([
                'teachingAssignment.subject:id,name',
                'teachingAssignment.academicSection:id,name,school_year',
            ])
            ->orderByDesc('published_at')
            ->orderBy('due_date')
            ->paginate(12);

        return view('teacher.assignments.index', [
            'assignments' => $assignments,
        ]);
    }

    public function create(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $teachingAssignments = $this->getTeachingAssignmentsForTeacher($user);

        return view('teacher.assignments.create', [
            'teachingAssignments' => $teachingAssignments,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $this->validatePayload($request);

        $this->ensureTeachingAssignmentBelongsToTeacher($user, (int) $data['teaching_assignment_id']);

        Assignment::create($data);

        return redirect()
            ->route('teacher.assignments.index')
            ->with('status', __('Assignment created successfully.'));
    }

    public function edit(Assignment $assignment): View
    {
        /** @var User $user */
        $user = auth()->user();

        $assignment = $this->ensureAssignmentBelongsToTeacher($user, $assignment);

        $teachingAssignments = $this->getTeachingAssignmentsForTeacher($user);

        return view('teacher.assignments.edit', [
            'assignment' => $assignment,
            'teachingAssignments' => $teachingAssignments,
        ]);
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $assignment = $this->ensureAssignmentBelongsToTeacher($user, $assignment);
        $data = $this->validatePayload($request);

        $this->ensureTeachingAssignmentBelongsToTeacher($user, (int) $data['teaching_assignment_id']);

        $assignment->update($data);

        return redirect()
            ->route('teacher.assignments.index')
            ->with('status', __('Assignment updated successfully.'));
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $assignment = $this->ensureAssignmentBelongsToTeacher($user, $assignment);
        $assignment->delete();

        return redirect()
            ->route('teacher.assignments.index')
            ->with('status', __('Assignment deleted successfully.'));
    }

    /**
     * @return array{
     *     teaching_assignment_id: int,
     *     title: string,
     *     description: string,
     *     due_date: string|null,
     *     published_at: string|null,
     *     is_active: bool
     * }
     */
    private function validatePayload(Request $request): array
    {
        /** @var array{
         *     teaching_assignment_id: int,
         *     title: string,
         *     description: string,
         *     due_date: string|null,
         *     published_at: string|null,
         *     is_active: bool
         * } $validated
         */
        $validated = $request->validate([
            'teaching_assignment_id' => ['required', 'integer', 'exists:teaching_assignments,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_date' => ['nullable', 'date'],
            'published_at' => ['nullable', 'date'],
            'is_active' => ['required', 'boolean'],
        ]);

        return $validated;
    }

    /**
     * @return Collection<int, TeachingAssignment>
     */
    private function getTeachingAssignmentsForTeacher(User $user): Collection
    {
        return $user->teachingAssignments()
            ->where('is_active', true)
            ->with([
                'subject:id,name',
                'academicSection:id,name,school_year',
            ])
            ->orderBy('id')
            ->get();
    }

    private function ensureTeachingAssignmentBelongsToTeacher(User $user, int $teachingAssignmentId): void
    {
        $owned = $user->teachingAssignments()
            ->whereKey($teachingAssignmentId)
            ->exists();

        if (! $owned) {
            abort(403);
        }
    }

    private function ensureAssignmentBelongsToTeacher(User $user, Assignment $assignment): Assignment
    {
        $owned = $assignment->teachingAssignment()
            ->where('teacher_id', $user->id)
            ->exists();

        if (! $owned) {
            abort(403);
        }

        return $assignment;
    }
}
