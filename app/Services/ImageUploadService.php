<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class ImageUploadService
{
    /**
     * Handle multiple uploaded images from the default `images` field.
     */
    public function handleMultipleImages(Request $request): array
    {
        if (! $request->hasFile('images')) {
            return [];
        }

        $files = is_array($request->file('images'))
            ? $request->file('images')
            : [$request->file('images')];

        return $this->handleUploadedFiles($files);
    }

    /**
     * Handle any array of uploaded files and return the uploaded URLs.
     */
    public function handleUploadedFiles(array $files): array
    {
        $images = [];

        foreach ($files as $file) {
            $uploaded = $this->processUploadedFile($file);

            if ($uploaded) {
                $images[] = $uploaded;
            }
        }

        return $images;
    }

    /**
     * Process single image upload from a request field.
     */
    public function processImage(Request $request, string $fieldName = 'primary_image'): ?string
    {
        if (! $request->hasFile($fieldName)) {
            return null;
        }

        return $this->processUploadedFile($request->file($fieldName));
    }

    /**
     * Upload a single file and return the final image URL.
     */
    public function processUploadedFile(?UploadedFile $image): ?string
    {
        if (! $image || ! $image->isValid()) {
            return null;
        }

        try {
            $uploaded = new storage_r2();
            $uploaded = $uploaded->uploadToCloudinary($image);
        } catch (\Exception $exception) {
            throw new \Exception('Cloudinary upload failed: ' . $exception->getMessage());
        }

        return $uploaded['url'] ?? ($uploaded['secure_url'] ?? null);
    }
}
