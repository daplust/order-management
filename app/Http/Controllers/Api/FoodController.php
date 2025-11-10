<?php

namespace App\Http\Controllers\Api;
use App\Models\Food;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FoodController extends ApiController
{
    public function index(): JsonResponse
    {
        return $this->handle(function () {
            return Food::all();
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->handle(function () use ($request) {
            $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'type' => ['required', Rule::in(['food', 'beverage'])],
            'image' => 'nullable|image|max:2048', // 2MB Max
            'is_available' => 'boolean'
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('foods', 'public');
            $validated['image'] = $path;
        }

        $food = Food::create($validated);
            return $this->created($food, 'Food created successfully');
        });
    }

    public function show(int $id): JsonResponse
    {
        return $this->handle(function () use ($id) {
            return Food::findOrFail($id);
        });
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->handle(function () use ($request, $id) {
            $food = Food::findOrFail($id);
            $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'type' => ['sometimes', 'required', Rule::in(['food', 'beverage'])],
            'image' => 'nullable|image|max:2048',
            'is_available' => 'boolean'
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($food->image) {
                Storage::disk('public')->delete($food->image);
            }
            $path = $request->file('image')->store('foods', 'public');
            $validated['image'] = $path;
        }

        $food->update($validated);
            return $this->success($food, 'Food updated successfully');
        });
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->handle(function () use ($id) {
            $food = Food::findOrFail($id);
            if ($food->image) {
                Storage::disk('public')->delete($food->image);
            }

            $food->delete();

            return $this->success(null, 'Food deleted successfully');
        });
    }
}