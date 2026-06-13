<?php

namespace Tests\Unit;

use App\V1\Http\Requests\Rules\ProductValidation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProductImageValidationTest extends TestCase
{
    public function test_admin_image_rule_accepts_jpeg_with_octet_stream_mime(): void
    {
        $source = UploadedFile::fake()->image('dac-cleaner.jpg', 240, 240);
        $tempPath = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($tempPath, file_get_contents($source->getRealPath()));

        $file = new UploadedFile($tempPath, 'dac-cleaner.jpg', 'application/octet-stream', null, true);

        $validator = Validator::make(
            ['thumbnail' => $file],
            ['thumbnail' => ProductValidation::adminImageUploadRule()],
        );

        $this->assertFalse($validator->fails(), implode(', ', $validator->errors()->all()));
    }

    public function test_admin_image_rule_accepts_second_gallery_image(): void
    {
        $images = [
            UploadedFile::fake()->image('gallery-0.jpg'),
            UploadedFile::fake()->image('gallery-1.jpg'),
        ];

        $validator = Validator::make(
            ['images' => $images],
            [
                'images' => ['array'],
                'images.*' => ProductValidation::adminImageUploadRule(),
            ],
        );

        $this->assertFalse($validator->fails(), implode(', ', $validator->errors()->all()));
    }

    public function test_admin_image_rule_accepts_variant_thumbnail_with_jpg_extension(): void
    {
        $source = UploadedFile::fake()->image('variant.jpg');
        $tempPath = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($tempPath, file_get_contents($source->getRealPath()));

        $file = new UploadedFile($tempPath, 'variant.jpg', 'application/octet-stream', null, true);

        $validator = Validator::make(
            ['product_variants' => [['thumbnail' => $file]]],
            ['product_variants.0.thumbnail' => ProductValidation::adminImageUploadRule()],
        );

        $this->assertFalse($validator->fails(), implode(', ', $validator->errors()->all()));
    }

    public function test_is_allowed_admin_image_upload_rejects_non_image_file(): void
    {
        $file = UploadedFile::fake()->create('notes.txt', 32, 'text/plain');

        $this->assertFalse(ProductValidation::isAllowedAdminImageUpload($file));
    }
}
