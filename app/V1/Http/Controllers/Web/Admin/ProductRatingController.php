<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\ProductRating;
use App\V1\Services\ProductRatingService;
use App\V1\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductRatingController extends AdminController
{
    public function __construct(
        protected ProductRatingService $ratings,
        protected ProductService $products,
    ) {}

    public function index(Request $request): View|Response
    {
        $ratings = $this->ratings->getPaginated(15, $this->listFilters($request, [
            'search', 'rating', 'visibility', 'product_id',
        ]));

        return $this->adminListResponse(
            $request,
            'v1.admin.product-ratings.index',
            'v1.admin.product-ratings.partials.results',
            [
                'ratings' => $ratings,
                'products' => $this->products->getAllProducts(),
            ],
            ['ratings' => $ratings],
        );
    }

    public function toggleVisibility(ProductRating $productRating): RedirectResponse
    {
        $this->ratings->update($productRating, [
            'is_visible' => ! $productRating->is_visible,
        ]);

        return $this->redirectWithSuccess('v1.admin.product-ratings.index', 'Rating visibility updated successfully.');
    }
}
