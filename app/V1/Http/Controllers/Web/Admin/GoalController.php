<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\Goal;
use App\V1\Http\Requests\Web\Admin\StoreGoalRequest;
use App\V1\Http\Requests\Web\Admin\UpdateGoalRequest;
use App\V1\Services\GoalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class GoalController extends AdminController
{
    public function __construct(
        protected GoalService $goals,
    ) {}

    public function index(): View
    {
        return view('v1.admin.goals.index', [
            'goals' => $this->goals->getPaginatedGoals(),
        ]);
    }

    public function create(): View
    {
        return view('v1.admin.goals.create');
    }

    public function store(StoreGoalRequest $request): RedirectResponse
    {
        $this->goals->create($this->payload($request->validated()));

        return $this->redirectWithSuccess('v1.admin.goals.index', 'Goal created successfully.');
    }

    public function show(Goal $goal): View
    {
        return view('v1.admin.goals.show', [
            'goal' => $goal,
        ]);
    }

    public function edit(Goal $goal): View
    {
        return view('v1.admin.goals.edit', [
            'goal' => $goal,
        ]);
    }

    public function update(UpdateGoalRequest $request, Goal $goal): RedirectResponse
    {
        $this->goals->update($goal, $this->payload($request->validated()));

        return $this->redirectWithSuccess('v1.admin.goals.index', 'Goal updated successfully.');
    }

    public function destroy(Goal $goal): RedirectResponse
    {
        $this->goals->delete($goal);

        return $this->redirectWithSuccess('v1.admin.goals.index', 'Goal deleted successfully.');
    }

    public function toggleActive(Goal $goal): JsonResponse
    {
        $this->goals->toggleActive($goal);

        return response()->json([
            'success' => true,
            'message' => __('messages.Goal status updated successfully.'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function payload(array $data): array
    {
        if (array_key_exists('sort_order', $data) && $data['sort_order'] === null) {
            $data['sort_order'] = 0;
        }

        return $data;
    }
}
