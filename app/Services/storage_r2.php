<?php

namespace App\Services;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;

// Storage - Upload images

class storage_r2
{

    public function uploadToCloudinary($image): array
    {
        try {
            $uploaded = Cloudinary::uploadApi()->upload(
                $image->getRealPath(),
                [
                    'folder' => 'products',
                    'resource_type' => 'image',
                ]
            );

            // 🔥 normalize response (Object → Array)
            $data = json_decode(json_encode($uploaded), true);

            if (
                ! is_array($data) ||
                ! isset($data['secure_url'], $data['public_id'])
            ) {
                Log::error('Cloudinary invalid response', [
                    'response' => $data,
                ]);

                throw new \Exception('Invalid Cloudinary response');
            }

            return [
                'url' => $data['secure_url'],
                'public_id' => $data['public_id'],
            ];
        } catch (\Exception $e) {
            Log::error('Cloudinary upload failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e; // أثناء التطوير
        }
    }
}
