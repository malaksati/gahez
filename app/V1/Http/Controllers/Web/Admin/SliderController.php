<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\Slider;
use App\V1\Http\Requests\Web\Admin\StoreSliderRequest;
use App\V1\Http\Requests\Web\Admin\UpdateSliderRequest;
use App\V1\Services\SliderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SliderController extends AdminController
{
    public function __construct(
        protected SliderService $sliders,
    ) {}

    public function index(): View
    {
        return view('v1.admin.sliders.index', [
            'sliders' => $this->sliders->getPaginatedSliders(),
        ]);
    }

    public function create(): View
    {
        return view('v1.admin.sliders.create');
    }

    public function store(StoreSliderRequest $request): RedirectResponse
    {
        $data = [];
        $this->sliders->applyImageUpload($data, $request->file('image'));

        $this->sliders->create($data);

        return $this->redirectWithSuccess('v1.admin.sliders.index', 'Slider created successfully.');
    }

    public function show(Slider $slider): View
    {
        return view('v1.admin.sliders.show', [
            'slider' => $slider,
        ]);
    }

    public function edit(Slider $slider): View
    {
        return view('v1.admin.sliders.edit', [
            'slider' => $slider,
        ]);
    }

    public function update(UpdateSliderRequest $request, Slider $slider): RedirectResponse
    {
        $data = [];
        $this->sliders->applyImageUpload($data, $request->file('image'), $slider->image);

        if ($data !== []) {
            $this->sliders->update($slider->id, $data);
        }

        return $this->redirectWithSuccess('v1.admin.sliders.index', 'Slider updated successfully.');
    }

    public function destroy(Slider $slider): RedirectResponse
    {
        $this->sliders->deleteStoredImage($slider->image);
        $this->sliders->delete($slider->id);

        return $this->redirectWithSuccess('v1.admin.sliders.index', 'Slider deleted successfully.');
    }
}
