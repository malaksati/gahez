<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\V1\Http\Resources\Api\GoalResource;
use App\V1\Services\GoalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function __construct(
        protected GoalService $goals,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = collect($this->goals->getActiveGoalsWithProgressForUser($user))
            ->map(fn (array $row) => (new GoalResource($row))->toArray($request))
            ->values();

        return response()->json(['data' => $data]);
    }
}
