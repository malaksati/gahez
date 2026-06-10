<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\V1\Http\Requests\Api\StoreCategoryRequest;
use App\V1\Http\Requests\Api\UpdateCategoryRequest;
use App\V1\Http\Resources\Api\CategoryResource;
use App\V1\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService,
    ) {}

    public function index(Request $request)
    {
        if ($request->boolean('paginate') || $request->filled('per_page')) {
            $perPage = (int) $request->input('per_page', 15);

            return CategoryResource::collection(
                $this->categoryService->getPaginatedCategories(
                    $perPage,
                    $this->categoryFilters($request)
                )
            );
        }

        return CategoryResource::collection(
            $this->categoryService->getActiveCategories()
        );
    }

    public function tree()
    {
        return CategoryResource::collection(
            $this->categoryService->getCategoryTree()
        );
    }

    public function show(int $id)
    {
        $category = $this->categoryService->getCategoryById($id);

        abort_if($category === null, 404);

        return new CategoryResource($category);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->create($request->validated());

        return (new CategoryResource($category->load(['parent', 'children'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateCategoryRequest $request, int $id)
    {
        $category = $this->categoryService->getCategoryById($id);

        abort_if($category === null, 404);

        $this->categoryService->update($category, $request->validated());

        return new CategoryResource($category->fresh(['parent', 'children']));
    }

    public function destroy(int $id)
    {
        $category = $this->categoryService->getCategoryById($id);

        abort_if($category === null, 404);

        $this->categoryService->delete($category);

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    protected function categoryFilters(Request $request): array
    {
        return $request->only(['search', 'status', 'featured', 'parent_id', 'sort']);
    }
}
