<?php

namespace App\Services;

use App\Models\Address;
use App\Support\ApiResponseBuilder;

class AddressService
{
    public function index(int $userId): array
    {
        $addresses = Address::with('governorate')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        if ($addresses->isEmpty()) {
            return ApiResponseBuilder::error('No addresses found', 404, ['data' => []]);
        }

        return ApiResponseBuilder::success('Addresses retrieved successfully', $addresses);
    }

    public function store(array $validated, int $userId): array
    {
        $address = Address::create([
            'user_id' => $userId,
            'title' => $validated['title'],
            'details' => $validated['details'],
            'governorate_id' => $validated['governorate_id'],
        ]);

        return ApiResponseBuilder::success('Address created successfully', $address->load('governorate'), 201);
    }

    public function show(int|string $id, int $userId): array
    {
        $address = Address::with('governorate')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$address) {
            return ApiResponseBuilder::error('Address not found', 404);
        }

        return ApiResponseBuilder::success('Address retrieved successfully', $address);
    }

    public function update(int|string $id, array $validated, int $userId): array
    {
        $address = Address::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$address) {
            return ApiResponseBuilder::error('Address not found', 404);
        }

        $address->update($validated);

        return ApiResponseBuilder::success('Address updated successfully', $address->load('governorate'));
    }

    public function destroy(int|string $id, int $userId): array
    {
        $address = Address::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$address) {
            return ApiResponseBuilder::error('Address not found', 404);
        }

        $address->delete();

        return ApiResponseBuilder::success('Address deleted successfully');
    }
}
