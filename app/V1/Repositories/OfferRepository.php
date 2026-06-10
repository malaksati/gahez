<?php

namespace App\V1\Repositories;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class OfferRepository
{
    public function __construct(
        protected Offer $model,
    ) {}

    public function getAllOffers(): Collection
    {
        return Offer::query()
            ->with(['offerable', 'rewardProducts.product'])
            ->latest()
            ->get();
    }

    public function getPaginatedOffers(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Offer::query()->with(['offerable', 'rewardProducts.product']);

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"], 'and')
                    ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"], 'or');
            });
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['offerable_type'])) {
            $query->where('offerable_type', $filters['offerable_type']);
        }

        if (! empty($filters['offerable_id'])) {
            $query->where('offerable_id', (int) $filters['offerable_id']);
        }

        if (! empty($filters['valid_only']) && filter_var($filters['valid_only'], FILTER_VALIDATE_BOOLEAN)) {
            $query->valid();
        }

        return $query->latest()->paginate($perPage);
    }

    public function getOfferById(int $id): Offer
    {
        return Offer::query()
            ->with(['offerable', 'rewardProducts.product'])
            ->findOrFail($id);
    }

    /**
     * Offers attached to a specific product, category, etc.
     */
    public function getOffersForOfferable(string $offerableType, int $offerableId): Collection
    {
        return Offer::query()
            ->with('offerable')
            ->where('offerable_type', $offerableType)
            ->where('offerable_id', $offerableId)
            ->latest()
            ->get();
    }

    public function getActiveOffers(): Collection
    {
        return Offer::query()
            ->with('offerable')
            ->active()
            ->latest()
            ->get();
    }

    /**
     * Active offers whose start/end window includes now.
     */
    public function getValidOffers(): Collection
    {
        return Offer::query()
            ->with(['offerable', 'rewardProducts.product'])
            ->valid()
            ->latest()
            ->get();
    }

    public function create(array $data): Offer
    {
        return Offer::query()->create($data);
    }

    public function update(Offer $offer, array $data): Offer
    {
        $offer->update($data);

        return $offer->fresh(['offerable']);
    }

    public function delete(Offer $offer): bool
    {
        /** @var Model $model */
        $model = $offer;

        return (bool) $model->delete();
    }
}
