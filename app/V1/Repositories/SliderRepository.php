<?php

namespace App\V1\Repositories;

use App\Models\Slider;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SliderRepository
{
    protected $model;

    public function __construct(Slider $slider)
    {
        $this->model = $slider;
    }

    public function getAllSliders(?string $type = null): Collection
    {
        $query = $this->model::query()->orderByDesc('id');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->get();
    }

    public function getPaginatedSliders(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model::query()
            ->latest('id')
            ->paginate($perPage);
    }

    public function getSliderById(int $id): Slider
    {
        return $this->model::findOrFail($id);
    }

    public function create(array $data): Slider
    {
        return $this->model::create($data);
    }

    public function update(int $id, array $sliderData): ?Slider
    {
        $slider = $this->model->findOrFail($id);
        if ($slider) {
            $slider->update($sliderData);

            return $slider->fresh();
        }

        return null;
    }

    public function delete(int $id): bool
    {
        $slider = $this->model->findOrFail($id);

        return $slider->delete();
    }

    public function forceDelete(int $id): bool
    {
        $slider = $this->model->findOrFail($id);

        return $slider->forceDelete();
    }

    public function restore(int $id): bool
    {
        $slider = $this->model->findOrFail($id);

        return $slider->restore();
    }
}
