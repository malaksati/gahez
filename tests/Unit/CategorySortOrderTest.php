<?php

namespace Tests\Unit;

use App\Models\Category;
use App\V1\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySortOrderTest extends TestCase
{
    use RefreshDatabase;

    protected CategoryService $categories;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categories = app(CategoryService::class);
    }

    public function test_inserting_at_position_shifts_sibling_categories(): void
    {
        $first = $this->categories->create([
            'name' => ['en' => 'First', 'ar' => 'أول'],
            'is_active' => true,
        ]);
        $second = $this->categories->create([
            'name' => ['en' => 'Second', 'ar' => 'ثاني'],
            'is_active' => true,
        ]);

        $this->assertSame(1, $first->fresh()->sort_order);
        $this->assertSame(2, $second->fresh()->sort_order);

        $inserted = $this->categories->create([
            'name' => ['en' => 'Inserted', 'ar' => 'مدخل'],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertSame(1, $inserted->fresh()->sort_order);
        $this->assertSame(2, $first->fresh()->sort_order);
        $this->assertSame(3, $second->fresh()->sort_order);
    }

    public function test_sort_order_is_scoped_to_the_same_parent_only(): void
    {
        $rootA = $this->categories->create([
            'name' => ['en' => 'Root A', 'ar' => 'أ'],
            'is_active' => true,
        ]);
        $rootB = $this->categories->create([
            'name' => ['en' => 'Root B', 'ar' => 'ب'],
            'is_active' => true,
        ]);

        $child = $this->categories->create([
            'name' => ['en' => 'Child', 'ar' => 'فرع'],
            'is_active' => true,
            'parent_id' => $rootA->id,
            'sort_order' => 1,
        ]);

        $this->assertSame(1, $rootA->fresh()->sort_order);
        $this->assertSame(2, $rootB->fresh()->sort_order);
        $this->assertSame(1, $child->fresh()->sort_order);
    }

    public function test_moving_category_to_new_parent_reassigns_sort_order(): void
    {
        $parentA = Category::query()->create([
            'name' => ['en' => 'Parent A', 'ar' => 'أ'],
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $parentB = Category::query()->create([
            'name' => ['en' => 'Parent B', 'ar' => 'ب'],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $child = $this->categories->create([
            'name' => ['en' => 'Child', 'ar' => 'فرع'],
            'is_active' => true,
            'parent_id' => $parentA->id,
        ]);

        $this->categories->update($child, [
            'parent_id' => $parentB->id,
            'sort_order' => 1,
        ]);

        $child->refresh();

        $this->assertSame($parentB->id, $child->parent_id);
        $this->assertSame(1, $child->sort_order);
    }

    public function test_restrict_category_tree_to_parents_only_keeps_root_rows(): void
    {
        $root = $this->categories->create([
            'name' => ['en' => 'Groceries', 'ar' => 'بقالة'],
            'is_active' => true,
        ]);

        $child = $this->categories->create([
            'name' => ['en' => 'Dairy', 'ar' => 'ألبان'],
            'is_active' => true,
            'parent_id' => $root->id,
        ]);

        $byParent = $this->categories->getCategoriesGroupedByParent();
        $flat = $this->categories->flattenCategorySection($root, $byParent);

        $this->assertCount(2, $flat);

        $parentsOnly = $this->categories->restrictCategoryTreeToParentsOnly($flat);

        $this->assertCount(1, $parentsOnly);
        $this->assertSame($root->id, $parentsOnly->first()->id);
        $this->assertNotContains($child->id, $parentsOnly->pluck('id'));
    }
}
