{{-- File inputs live outside x-show steps so browsers include them on submit (display:none omits files). --}}
<div class="wizard-persistent-file-inputs visually-hidden" aria-hidden="true">
    <input type="file" x-ref="thumbnailInput" id="thumbnail" name="thumbnail" accept="image/*"
        @change="handleThumbnailChange($event)">

    <input type="file" x-ref="galleryInput" id="images" name="images[]" accept="image/*" multiple
        @change="handleImagesChange($event)">

    <template x-for="(row, index) in productVariants" :key="row._key ?? index">
        <input type="file"
            :id="`product_variant_thumbnail_${index}`"
            :name="`product_variants[${index}][thumbnail]`"
            accept="image/*"
            @change="handleVariationThumbnailChange(index, $event)">
    </template>
</div>
