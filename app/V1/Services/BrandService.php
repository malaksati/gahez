<?php

namespace App\V1\Services;

use App\Models\Brand;
use App\V1\Repositories\BrandRepository;
use App\V1\Support\UploadStorage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class BrandService
{
    public function __construct(
        protected BrandRepository $brands,
    ) {}

    public function getAllBrands(): Collection
    {
        return $this->brands->getAllBrands();
    }

    public function getPaginatedBrands(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        return $this->brands->getPaginatedBrands($perPage, $filters);
    }

    public function getBrandById(int $id): Brand
    {
        return $this->brands->getBrandById($id);
    }

    public function create(array $data, ?UploadedFile $image = null): Brand
    {
        $data = $this->prepareImageData($data, $image);

        return $this->brands->create($data);
    }

    public function update(Brand $brand, array $data, ?UploadedFile $image = null, bool $removeImage = false): bool
    {
        $data = $this->prepareImageData($data, $image, $brand, $removeImage);

        return $this->brands->update($brand, $data);
    }

    public function delete(Brand $brand): bool
    {
        $this->deleteStoredImage($brand);

        return $this->brands->delete($brand);
    }

    public function forceDelete(Brand $brand): bool
    {
        $this->deleteStoredImage($brand);

        return $this->brands->forceDelete($brand);
    }

    public function restore(Brand $brand): bool
    {
        return $this->brands->restore($brand);
    }

    public function search(string $search): Collection
    {
        return $this->brands->search($search);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepareImageData(
        array $data,
        ?UploadedFile $image = null,
        ?Brand $brand = null,
        bool $removeImage = false,
    ): array {
        if ($removeImage && $brand !== null) {
            $this->deleteStoredImage($brand);
            $data['image'] = null;
        } elseif ($image instanceof UploadedFile && $image->isValid()) {
            if ($brand !== null) {
                $this->deleteStoredImage($brand);
            }

            $data['image'] = UploadStorage::store($image, 'brands', 'public');
        } else {
            unset($data['image']);
        }

        unset($data['remove_image']);

        return $data;
    }

    protected function deleteStoredImage(Brand $brand): void
    {
        $path = $brand->getRawOriginal('image');

        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
