{{-- Step 4: Media --}}
<div x-show="currentStepKey === 'images'" x-transition.opacity class="wizard-content">
    <h5 class="mb-3">
        <i class="bi bi-images me-2"></i>{{ __('messages.Product media') }}
    </h5>
    <p class="text-muted mb-4">{{ __('messages.Upload product media') }}</p>

    <div class="mb-4">
        <label class="form-label">{{ __('messages.Thumbnail') }}</label>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-outline-primary"
                @click="$refs.thumbnailInput.click()">
                <i class="bi bi-upload me-1"></i>{{ __('messages.Upload file') }}
            </button>
            <span class="text-muted small" x-show="thumbnailFile" x-text="thumbnailFile?.name"></span>
        </div>
        @error('thumbnail')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        <small class="text-muted d-block mt-1">{{ __('messages.Max image size 5MB') }}</small>
        <div x-show="thumbnailPreview" class="mt-3">
            <img :src="thumbnailPreview" alt="" class="img-thumbnail"
                style="max-width: 200px; max-height: 200px; object-fit: cover;">
            <button type="button" class="btn btn-sm btn-outline-danger ms-2" @click="removeThumbnail()">
                <i class="bi bi-trash"></i> {{ __('messages.Remove') }}
            </button>
        </div>
    </div>


    @if ($isEdit && count($existingImages) > 0)
        <div class="mb-4">
            <h6>{{ __('messages.Current gallery images') }}</h6>
            <div class="row g-3">
                @foreach ($existingImages as $image)
                    <div class="col-md-3">
                        <div class="border rounded p-2 h-100">
                            <img src="{{ $image['url'] }}" alt="" class="img-thumbnail w-100 mb-2"
                                style="height: 120px; object-fit: cover;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="existing_images[]"
                                    value="{{ $image['id'] }}" id="existing_image_{{ $image['id'] }}"
                                    @checked($image['keep'])>
                                <label class="form-check-label" for="existing_image_{{ $image['id'] }}">
                                    {{ __('messages.Keep image') }}
                                </label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mb-3">
        <label class="form-label">{{ __('messages.Gallery images') }}</label>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <button type="button" class="btn btn-outline-primary"
                @click="$refs.galleryInput.click()">
                <i class="bi bi-images me-1"></i>{{ __('messages.Upload file') }}
            </button>
            <span class="text-muted small" x-show="selectedImages.length > 0">
                <span x-text="selectedImages.length"></span> {{ __('messages.Gallery images') }}
            </span>
        </div>
        @error('images')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        @error('images.*')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        <small class="text-muted">{{ __('messages.You can select multiple images') }}</small>
    </div>

    <div x-show="selectedImages.length > 0" class="mt-3">
        <h6>{{ __('messages.New images to upload') }}</h6>
        <div class="row g-3">
            <template x-for="(image, index) in selectedImages" :key="index">
                <div class="col-md-3">
                    <div class="position-relative">
                        <img :src="image.preview" alt="" class="img-thumbnail w-100"
                            style="height: 150px; object-fit: cover;">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                            @click="removeImage(index)">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>

{{-- Step 5: Related products --}}
<div x-show="currentStepKey === 'related'" x-transition.opacity class="wizard-content">
    <h5 class="mb-3">
        <i class="bi bi-link-45deg me-2"></i>{{ __('messages.Related products') }}
    </h5>
    <p class="text-muted mb-4">{{ __('messages.Select related products') }}</p>

    <select name="related_products[]" class="form-select @error('related_products') is-invalid @enderror"
        multiple size="12" x-model="formData.related_products">
        @foreach ($allProducts as $catalogProduct)
            <option value="{{ $catalogProduct->id }}">
                {{ $catalogProduct->getTranslation('name', app()->getLocale()) }}
                ({{ $catalogProduct->sku }})
            </option>
        @endforeach
    </select>
    <small class="text-muted d-block mt-2">{{ __('messages.Hold Ctrl or Cmd to select multiple') }}</small>
    @error('related_products')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    @error('related_products.*')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
