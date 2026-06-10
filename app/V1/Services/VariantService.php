<?php

namespace App\V1\Services;

use App\Models\Variant;
use App\Models\VariantOption;
use App\V1\Repositories\VariantRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VariantService
{
    public function __construct(
        protected VariantRepository $variants,
    ) {}

    public function getAllVariants(): Collection
    {
        return $this->variants->getAllVariants();
    }

    public function getPaginatedVariants(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->variants->getPaginatedVariants($perPage, $filters);
    }

    public function getVariantById(int $id): Variant
    {
        return $this->variants->getVariantById($id);
    }

    public function getActiveVariants(): Collection
    {
        return $this->variants->getActiveVariants();
    }

    public function getRequiredVariants(): Collection
    {
        return $this->variants->getRequiredVariants();
    }

    public function create(array $data): Variant
    {
        $options = $data['options'] ?? [];
        unset($data['options']);

        return DB::transaction(function () use ($data, $options) {
            $variant = $this->variants->create($data);
            $this->syncVariantOptions($variant, $options);

            return $variant->load('options');
        });
    }

    public function update(Variant $variant, array $data): bool
    {
        $options = $data['options'] ?? null;
        unset($data['options']);

        return DB::transaction(function () use ($variant, $data, $options) {
            $updated = $this->variants->update($variant, $data);

            if ($options !== null) {
                $this->syncVariantOptions($variant, $options);
            }

            return $updated;
        });
    }

    public function delete(Variant $variant): bool
    {
        return $this->variants->delete($variant);
    }

    public function forceDelete(Variant $variant): bool
    {
        return $this->variants->forceDelete($variant);
    }

    public function restore(Variant $variant): bool
    {
        return $this->variants->restore($variant);
    }

    public function search(string $search): Collection
    {
        return $this->variants->search($search);
    }

    public function getVariantOptions(int $variantId): Collection
    {
        return $this->variants->getVariantOptions($variantId);
    }

    /**
     * @param  list<array<string, mixed>>  $options
     */
    protected function syncVariantOptions(Variant $variant, array $options): void
    {
        $keptIds = [];

        foreach ($options as $optionData) {
            $nameEn = trim((string) ($optionData['name']['en'] ?? ''));
            $nameAr = trim((string) ($optionData['name']['ar'] ?? ''));

            if ($nameEn === '' && $nameAr === '') {
                continue;
            }

            $name = [
                'en' => $nameEn,
                'ar' => $nameAr,
            ];

            $code = trim((string) ($optionData['code'] ?? ''));
            if ($code === '') {
                $code = $this->generateUniqueCode($nameEn !== '' ? $nameEn : $nameAr);
            }

            if (! empty($optionData['id'])) {
                $option = VariantOption::query()
                    ->where('variant_id', $variant->id)
                    ->find((int) $optionData['id']);

                if ($option) {
                    $option->update([
                        'name' => $name,
                        'code' => $code,
                    ]);
                    $keptIds[] = $option->id;
                }

                continue;
            }

            $option = $variant->options()->create([
                'name' => $name,
                'code' => $code,
            ]);

            $keptIds[] = $option->id;
        }

        $variant->options()->whereNotIn('id', $keptIds)->delete();
    }

    protected function generateUniqueCode(string $name): string
    {
        $code = Str::slug($name) ?: 'option';
        $baseCode = $code;
        $counter = 1;

        while (VariantOption::query()->where('code', $code)->exists()) {
            $code = $baseCode.'-'.$counter;
            $counter++;
        }

        return $code;
    }
}
