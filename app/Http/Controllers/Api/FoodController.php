<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FoodController extends Controller
{
    public function index(): JsonResponse
    {
        $foods = Food::all();
        return response()->json($foods);
    }

    public function store(Request $request): JsonResponse
    {
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

        return response()->json([
            'message' => 'Food created successfully',
            'data' => $food
        ], 201);
    }

    public function show(Food $food): JsonResponse
    {
        return response()->json($food);
    }

    public function update(Request $request, Food $food): JsonResponse
    {
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

        return response()->json([
            'message' => 'Food updated successfully',
            'data' => $food
        ]);
    }

    public function destroy(Food $food): JsonResponse
    {
        if ($food->image) {
            Storage::disk('public')->delete($food->image);
        }

        $food->delete();

        return response()->json([
            'message' => 'Food deleted successfully'
        ]);
    }
}