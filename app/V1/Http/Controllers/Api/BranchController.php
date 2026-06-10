<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\V1\Http\Requests\Api\StoreBranchRequest;
use App\V1\Http\Requests\Api\UpdateBranchRequest;
use App\V1\Http\Resources\Api\BranchResource;
use App\V1\Services\BranchService;

class BranchController extends Controller
{
    public function __construct(
        protected BranchService $branchService,
    ) {}

    public function index()
    {
        return BranchResource::collection(
            $this->branchService->getAllBranches()
        );
    }

    public function show(int $id)
    {
        return new BranchResource(
            $this->branchService->getBranchById($id)
        );
    }

    public function store(StoreBranchRequest $request)
    {
        $branch = $this->branchService->create($request->validated());

        return (new BranchResource($branch))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateBranchRequest $request, int $id)
    {
        $branch = $this->branchService->getBranchById($id);

        $this->branchService->update($branch, $request->validated());

        return new BranchResource($branch->fresh());
    }

    public function destroy(int $id)
    {
        $branch = $this->branchService->getBranchById($id);

        $this->branchService->delete($branch);

        return response()->noContent();
    }
}
