<?php

namespace App\Http\Controllers;

use App\Models\Questions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class questionsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Questions::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        // $question = Questions::create($this->validatedData($request));
        // dd($request->all());
        $question = new Questions();
        $question->category_id = $request->category;
        $question->text = $request->question;
        $question->type = $request->type;
        $question->difficulty = $request->difficulty;
        $question->score_value = $request->score;
        $question->options = json_encode($request->options); // simpan array ke JSON
        // $question->correct_answer = $request->correct_answer;
        $question->save();

        // return response()->json(['message' => 'Question created successfully']);

        return response()->json($question, 201);
    }

    public function show(Questions $question): JsonResponse
    {
        return response()->json($question);
    }

    public function update(Request $request, Questions $question): JsonResponse
    {
        $question->update($this->validatedData($request));

        return response()->json($question->fresh());
    }

    public function destroy(Questions $question): JsonResponse
    {
        $question->delete();

        return response()->json(status: 204);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'string', 'max:255'],
            'question' => ['required', 'string', 'max:5000'],
            'options' => ['required', 'array'],
            'options.option_a' => ['required', 'string', 'max:1000'],
            'options.option_b' => ['required', 'string', 'max:1000'],
            'options.option_c' => ['required', 'string', 'max:1000'],
            'options.option_d' => ['required', 'string', 'max:1000'],
            'correct_answer' => ['required', 'in:A,B,C,D'],
            'score' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);
    }
}
