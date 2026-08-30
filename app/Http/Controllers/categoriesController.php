<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class categoriesController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Categories::orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $category = Categories::create($this->validatedData($request));

        return response()->json($category, 201);
    }

    public function show(Categories $category): JsonResponse
    {
        return response()->json($category);
    }

    public function update(Request $request, Categories $category): JsonResponse
    {
        $category->update($this->validatedData($request, $category));

        return response()->json($category->fresh());
    }

    public function destroy(Categories $category): JsonResponse
    {
        $category->delete();

        return response()->json(status: 204);
    }

    private function validatedData(Request $request, ?Categories $category = null): array
    {
        $uniqueName = 'unique:categories,name';

        if ($category) {
            $uniqueName .= ','.$category->id;
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255', $uniqueName],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
