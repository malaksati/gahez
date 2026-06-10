<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\V1\Http\Requests\Web\Admin\StoreOfferRequest;
use App\V1\Http\Requests\Web\Admin\UpdateOfferRequest;
use App\V1\Services\CustomerBroadcastService;
use App\V1\Services\OfferService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

class OfferController extends AdminController
{
    public function __construct(
        protected OfferService $offers,
        protected CustomerBroadcastService $customerBroadcasts,
    ) {}

    public function index(): View
    {
        return view('v1.admin.offers.index', [
            'offers' => $this->offers->getPaginatedOffers(),
        ]);
    }

    public function create(): View
    {
        return view('v1.admin.offers.create', $this->offerFormData());
    }

    public function store(StoreOfferRequest $request): RedirectResponse
    {
        $this->offers->create($this->offerPayload($request));

        return $this->redirectWithSuccess('v1.admin.offers.index', 'Offer created successfully.');
    }

    public function show(Offer $offer): View
    {
        $offer->load(['offerable', 'rewardProducts.product']);

        return view('v1.admin.offers.show', [
            'offer' => $offer,
        ]);
    }

    public function edit(Offer $offer): View
    {
        return view('v1.admin.offers.edit', $this->offerFormData($offer));
    }

    public function update(UpdateOfferRequest $request, Offer $offer): RedirectResponse
    {
        $this->offers->update($offer->id, $this->offerPayload($request));

        return $this->redirectWithSuccess('v1.admin.offers.index', 'Offer updated successfully.');
    }

    public function destroy(Offer $offer): RedirectResponse
    {
        $this->offers->delete($offer->id);

        return $this->redirectWithSuccess('v1.admin.offers.index', 'Offer deleted successfully.');
    }

    public function notifyCustomers(Offer $offer): RedirectResponse
    {
        $sent = $this->customerBroadcasts->notifyCustomersAboutOffer($offer);

        return $this->redirectBackWithSuccess(
            __('messages.Offer notification sent to :count customers.', ['count' => $sent]),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function offerPayload(StoreOfferRequest|UpdateOfferRequest $request): array
    {
        return Arr::except($request->validated(), ['offerable_type_key']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function offerFormData(?Offer $offer = null): array
    {
        if ($offer) {
            $offer->loadMissing(['offerable', 'rewardProducts']);
        }

        $locale = app()->getLocale();

        $products = $this->selectableProducts($locale);
        $categories = $this->selectableCategories($locale);

        return [
            'offer' => $offer,
            'offerablePickerConfig' => [
                'products' => $products,
                'categories' => $categories,
                'initialTypeKey' => $this->resolveOfferableTypeKey($offer),
                'initialId' => (string) old('offerable_id', $offer?->offerable_id ?? ''),
            ],
        ];
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    protected function selectableProducts(string $locale): array
    {
        return Product::query()
            ->orderBy('id', 'asc')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->getTranslation('name', $locale, false)
                    ?: $product->getTranslation('name', 'en', false)
                    ?: __('messages.Product').' #'.$product->id,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    protected function selectableCategories(string $locale): array
    {
        return Category::query()
            ->orderBy('id', 'asc')
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->getTranslation('name', $locale, false)
                    ?: $category->getTranslation('name', 'en', false)
                    ?: __('messages.Category').' #'.$category->id,
            ])
            ->values()
            ->all();
    }

    protected function resolveOfferableTypeKey(?Offer $offer): string
    {
        $key = old('offerable_type_key');

        if ($key) {
            return $key;
        }

        if (! $offer) {
            return 'product';
        }

        return $offer->offerable_type === Category::class ? 'category' : 'product';
    }
}
