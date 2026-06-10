<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\V1\Services\AdminSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSearchController extends AdminController
{
    public function __construct(
        protected AdminSearchService $search,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $query = (string) $request->get('q', '');

        return response()->json([
            'results' => $this->search->search($query),
        ]);
    }
}
