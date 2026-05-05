<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductImageService
{
    public function store(UploadedFile $image): string
    {
        return $image->store('products', 'public');
    }

    public function replace(UploadedFile $image, ?string $oldPath): string
    {
        $newPath = $this->store($image);

        $this->delete($oldPath);

        return $newPath;
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
