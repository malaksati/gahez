<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\V1\Http\Requests\Api\StoreSliderRequest;
use App\V1\Http\Requests\Api\UpdateSliderRequest;
use App\V1\Http\Requests\Rules\SliderValidation;
use App\V1\Http\Resources\Api\SliderResource;
use App\V1\Services\SliderService;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function __construct(
        protected SliderService $sliderService,
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate(SliderValidation::apiIndexFilter());

        return SliderResource::collection(
            $this->sliderService->getAllSliders($validated['type'] ?? null)
        );
    }

    public function store(StoreSliderRequest $request)
    {
        $data = $request->validated();
        $this->sliderService->applyImageUpload($data, $request->file('image'));

        $slider = $this->sliderService->create($data);

        return (new SliderResource($slider))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateSliderRequest $request, int $id)
    {
        $slider = $this->sliderService->getSliderById($id);
        $data = $request->validated();
        $this->sliderService->applyImageUpload($data, $request->file('image'), $slider->image);

        $slider = $this->sliderService->update($id, $data);

        return new SliderResource($slider);
    }

    public function destroy(int $id)
    {
        $slider = $this->sliderService->getSliderById($id);
        $this->sliderService->deleteStoredImage($slider->image);
        $this->sliderService->delete($id);

        return response()->noContent();
    }
}
