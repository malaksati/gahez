<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\Branch;
use App\V1\Http\Requests\Web\Admin\StoreBranchRequest;
use App\V1\Http\Requests\Web\Admin\UpdateBranchRequest;
use App\V1\Services\BranchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BranchController extends AdminController
{
    public function __construct(
        protected BranchService $branches,
    ) {}

    public function index(Request $request): View|Response
    {
        $branches = $this->branches->getPaginatedBranches(15, $this->listFilters($request, [
            'search', 'status', 'sort',
        ]));

        return $this->adminListResponse(
            $request,
            'v1.admin.branches.index',
            'v1.admin.branches.partials.results',
            ['branches' => $branches],
            ['branches' => $branches],
        );
    }

    public function create(): View
    {
        return view('v1.admin.branches.create');
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $this->branches->create($request->validated());

        return $this->redirectWithSuccess('v1.admin.branches.index', 'Branch created successfully.');
    }

    public function show(Branch $branch): View
    {
        return view('v1.admin.branches.show', [
            'branch' => $branch,
        ]);
    }

    public function edit(Branch $branch): View
    {
        return view('v1.admin.branches.edit', [
            'branch' => $branch,
        ]);
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $this->branches->update($branch, $request->validated());

        return $this->redirectWithSuccess('v1.admin.branches.index', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $this->branches->delete($branch);

        return $this->redirectWithSuccess('v1.admin.branches.index', 'Branch deleted successfully.');
    }
}
