<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FoodController extends Controller
{
    /**
     * Master Makanan - List all foods (PRD: Pelayan can manage foods)
     */
    public function index(): Response
    {
        $foods = Food::orderBy('type')->orderBy('name')->get();

        return Inertia::render('Foods/Index', [
            'foods' => $foods,
        ]);
    }

    /**
     * Show form to create new food
     */
    public function create(): Response
    {
        return Inertia::render('Foods/Create');
    }

    /**
     * Store new food (PRD: Pelayan can add food)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:food,beverage',
            'is_available' => 'boolean',
        ]);

        Food::create($validated);

        return redirect()->route('foods.index')->with('success', 'Food created successfully');
    }

    /**
     * Show form to edit food
     */
    public function edit(Food $food): Response
    {
        return Inertia::render('Foods/Edit', [
            'food' => $food,
        ]);
    }

    /**
     * Update food (PRD: Pelayan can update food)
     */
    public function update(Request $request, Food $food)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:food,beverage',
            'is_available' => 'boolean',
        ]);

        $food->update($validated);

        return redirect()->route('foods.index')->with('success', 'Food updated successfully');
    }

    /**
     * Delete food (PRD: Pelayan can delete food)
     */
    public function destroy(Food $food)
    {
        $food->delete();

        return redirect()->route('foods.index')->with('success', 'Food deleted successfully');
    }
}
