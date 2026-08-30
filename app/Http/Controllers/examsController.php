<?php

namespace App\Http\Controllers;

use App\Models\Exams;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class examsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Exams::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $exam = Exams::create($this->validatedData($request));

        return response()->json($exam, 201);
    }

    public function show(Exams $exam): JsonResponse
    {
        return response()->json($exam);
    }

    public function update(Request $request, Exams $exam): JsonResponse
    {
        $exam->update($this->validatedData($request));

        return response()->json($exam->fresh());
    }

    public function destroy(Exams $exam): JsonResponse
    {
        $exam->delete();

        return response()->json(status: 204);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        ]);
    }
}
