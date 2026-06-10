<?php

namespace App\V1\Services;

use App\Models\Slider;
use App\V1\Repositories\SliderRepository;
use App\V1\Support\UploadStorage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class SliderService
{
    public function __construct(
        protected SliderRepository $sliders,
    ) {}

    public function getAllSliders(): Collection
    {
        return $this->sliders->getAllSliders();
    }

    public function getPaginatedSliders(int $perPage = 15): LengthAwarePaginator
    {
        return $this->sliders->getPaginatedSliders($perPage);
    }

    public function getSliderById(int $id): Slider
    {
        return $this->sliders->getSliderById($id);
    }

    public function create(array $data): Slider
    {
        return $this->sliders->create($data);
    }

    public function update(int $id, array $data): Slider
    {
        return $this->sliders->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->sliders->delete($id);
    }

    public function forceDelete(int $id): bool
    {
        return $this->sliders->forceDelete($id);
    }

    public function restore(int $id): bool
    {
        return $this->sliders->restore($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function applyImageUpload(array &$data, ?UploadedFile $file, ?string $previousPath = null): void
    {
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            return;
        }

        if ($previousPath) {
            $this->deleteStoredImage($previousPath);
        }

        $data['image'] = UploadStorage::store($file, 'sliders');
    }

    public function deleteStoredImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
