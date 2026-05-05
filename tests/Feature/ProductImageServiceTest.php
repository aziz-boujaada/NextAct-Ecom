<?php

use App\Services\ProductImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('it stores product images on the public disk', function () {
    Storage::fake('public');

    $path = app(ProductImageService::class)->store(
        UploadedFile::fake()->image('product.jpg'),
    );

    expect($path)->toStartWith('products/');
    Storage::disk('public')->assertExists($path);
});

test('it replaces product images and removes the old file', function () {
    Storage::fake('public');

    $service = app(ProductImageService::class);
    $oldPath = $service->store(UploadedFile::fake()->image('old.jpg'));

    $newPath = $service->replace(
        UploadedFile::fake()->image('new.jpg'),
        $oldPath,
    );

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($newPath);
});

test('it deletes product images when a path exists', function () {
    Storage::fake('public');

    $service = app(ProductImageService::class);
    $path = $service->store(UploadedFile::fake()->image('deleted.jpg'));

    $service->delete($path);

    Storage::disk('public')->assertMissing($path);
});
