<?php

namespace App\Services;

use App\Models\Governorate;
use App\Support\ApiResponseBuilder;

class GovernorateService
{
    public function index(): array
    {
        $governorates = Governorate::orderBy('name')->get();

        if ($governorates->isEmpty()) {
            return ApiResponseBuilder::error('No governorates found', 404, ['data' => []]);
        }

        return ApiResponseBuilder::success('Governorates retrieved successfully', $governorates);
    }

    public function store(array $validated): array
    {
        $governorate = Governorate::create($validated);

        return ApiResponseBuilder::success('Governorate created successfully', $governorate, 201);
    }

    public function show(int|string $id): array
    {
        $governorate = Governorate::find($id);

        if (!$governorate) {
            return ApiResponseBuilder::error('Governorate not found', 404);
        }

        return ApiResponseBuilder::success('Governorate retrieved successfully', $governorate);
    }

    public function update(int|string $id, array $validated): array
    {
        $governorate = Governorate::find($id);

        if (!$governorate) {
            return ApiResponseBuilder::error('Governorate not found', 404);
        }

        $governorate->update($validated);

        return ApiResponseBuilder::success('Governorate updated successfully', $governorate);
    }

    public function destroy(int|string $id): array
    {
        $governorate = Governorate::find($id);

        if (!$governorate) {
            return ApiResponseBuilder::error('Governorate not found', 404);
        }

        if ($governorate->addresses()->exists()) {
            return ApiResponseBuilder::error('Cannot delete governorate with existing addresses', 400);
        }

        $governorate->delete();

        return ApiResponseBuilder::success('Governorate deleted successfully');
    }
}
