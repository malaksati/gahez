<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\Category;
use App\V1\Http\Requests\Web\Admin\StoreCategoryRequest;
use App\V1\Http\Requests\Web\Admin\UpdateCategoryRequest;
use App\V1\Services\CategoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends AdminController
{
    public function __construct(
        protected CategoryService $categories,
    ) {}

    public function index(Request $request): View|Response
    {
        $filters = $this->listFilters($request, [
            'search', 'status', 'featured', 'parent_id', 'sort',
        ]);

        $rootCategories = $this->categories->getPaginatedCategorySections(15, $filters);
        $categoriesByParent = $this->categories->getCategoriesGroupedByParent();
        $orphanCategories = $this->categories->getOrphanCategories();

        $categorySections = $rootCategories->getCollection()
            ->map(fn (Category $root) => [
                'root' => $root,
                'tree' => $this->categories->filterCategoryTreeByFilters(
                    $this->categories->flattenCategorySection($root, $categoriesByParent),
                    $filters,
                ),
            ])
            ->filter(fn (array $section) => $section['tree']->isNotEmpty())
            ->values();

        if (! empty($filters['search'])) {
            $orphanCategories = $this->categories->filterCategoriesMatchingSearch(
                $orphanCategories,
                (string) $filters['search'],
            );
        }

        $resultsData = [
            'rootCategories' => $rootCategories,
            'categorySections' => $categorySections,
            'orphanCategories' => $orphanCategories,
        ];

        return $this->adminListResponse(
            $request,
            'v1.admin.categories.index',
            'v1.admin.categories.partials.results',
            array_merge($resultsData, [
                'parentCategories' => $this->categories->getAllCategories(),
            ]),
            $resultsData,
        );
    }

    public function create(): View
    {
        return view('v1.admin.categories.create', [
            'parentCategories' => $this->categories->getRootCategories(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categories->create($request->validated());

        return $this->redirectWithSuccess('v1.admin.categories.index', 'Category created successfully.');
    }

    public function show(Category $category): View
    {
        $category->load(['parent'])->loadCount(['children', 'products']);

        return view('v1.admin.categories.show', [
            'category' => $category,
        ]);
    }

    public function edit(Category $category): View
    {
        return view('v1.admin.categories.edit', [
            'category' => $category,
            'parentCategories' => $this->categories->getRootCategories(),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categories->update($category, $request->validated());

        return $this->redirectWithSuccess('v1.admin.categories.index', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse|JsonResponse
    {
        $this->categories->delete($category);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.Category deleted successfully.'),
            ]);
        }

        return $this->redirectWithSuccess('v1.admin.categories.index', 'Category deleted successfully.');
    }
}
