<?php

namespace App\Http\Controllers;

use App\Models\Questions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class questionsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DB::table('questions')
            ->join('question_answers', 'questions.id', '=', 'question_answers.question_id')
            ->where('question_answers.is_correct', 1)
            ->select('questions.*', DB::raw('GROUP_CONCAT(question_answers.answer_text) as correct_answer'))
            ->groupBy('questions.id');

        // cek apakah ada filter kategori
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // bisa tambahkan filter lain (difficulty, type, dsb)
        if ($request->has('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->has('id')){
            $query = Question::whereIn('id', $request->id)->get();
        }

        $questions = $query->get();

        return response()->json($questions);
    }

    public function assignQuestions(Request $request)
    {
        $examId = $request->exam_id;
        $examDetails = DB::table('exams')->where('id', $examId)->first();
        // $questionIds = DB::table('exam_question')
        //                 ->join('questions', 'exam_question.question_id', '=', 'questions.id')
        //                 ->where('exam_question.exam_id', $examId)
        //                 ->select('questions.*')
        //                 ->get();
         $questionIds = DB::table('questions')
            ->inRandomOrder()
            ->limit($examDetails->count_qa)
            ->get();

        return response()->json([
            'message' => 'Questions assigned successfully',
            'exam_id' => $examId,
            'exam_details' => $examDetails,
            'question_ids' => $questionIds
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $question = Questions::create([
            'category_id' => $request->category,
            'text' => $request->question,
            'type' => $request->type,
            'difficulty' => $request->difficulty,
            'score_value' => $request->score,
            'score_ifwrong' => $request->score_if_wrong,
            'options' => json_encode($request->options), // simpan array ke JSON
            // 'correct_answer' => $request->correct_answer,
        ]);

        // for ($i = 0; $i < count($request->options); $i++) {
        //     if ($request->options[$i] === $request->correct_answer) {
        //         $question->correct_answer = $request->correct_answer;
        //         break;
        //     }
        // }
        // foreach ($request->options as $key => $value) {
        //     if ($value === $request->correct_answer) {
        //         $question->correct_answer = $key; // Simpan key dari jawaban yang benar
        //         break;
        //     }
        // }
        foreach ($request->options as $label => $value) {
            DB::table('question_answers')->insert([
                'question_id' => $question->id,
                // 'label' => $label,
                'answer_text' => $value,
                'is_correct' => ($value === $request->correct_answer) ? 1 : 0,
            ]);
        } 

        // dd($request->all());
        // $question = new Questions();
        // $question->category_id = $request->category;
        // $question->text = $request->question;
        // $question->type = $request->type;
        // $question->difficulty = $request->difficulty;
        // $question->score_value = $request->score;
        // $question->options = json_encode($request->options); // simpan array ke JSON
        // // $question->correct_answer = $request->correct_answer;
        // $question->save();

        

        // return response()->json(['message' => 'Question created successfully']);

        return response()->json($question, 201);
    }

    public function show(Questions $question): JsonResponse
    {
        return response()->json($question);
    }

    public function update(Request $request, Questions $question): JsonResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer'],
            'question' => ['required', 'string', 'max:5000'],
            'options' => ['required', 'array'],
            'options.option_a' => ['required', 'string', 'max:1000'],
            'options.option_b' => ['required', 'string', 'max:1000'],
            'options.option_c' => ['required', 'string', 'max:1000'],
            'options.option_d' => ['required', 'string', 'max:1000'],
            'correct_answer' => ['required', 'string', 'max:1000'],
            'score' => ['required', 'integer', 'min:1', 'max:1000'],
            'score_if_wrong' => ['required', 'integer', 'max:1000'],
            'difficulty' => ['required', 'string'],
            'type' => ['required', 'string'],
        ]);

        if (! in_array($data['correct_answer'], $data['options'], true)) {
            throw ValidationException::withMessages([
                'correct_answer' => ['The correct answer must match one of the options.'],
            ]);
        }

        $question->update([
            'category_id' => $data['category_id'],
            'text' => $data['question'],
            'type' => $data['type'],
            'difficulty' => $data['difficulty'],
            'score_value' => $data['score'],
            'score_ifwrong' => $data['score_if_wrong'],
            'options' => json_encode($data['options']),
        ]);

        DB::table('question_answers')
            ->where('question_id', $question->id)
            ->update(['is_correct' => 0]);

        DB::table('question_answers')
            ->where('question_id', $question->id)
            ->where('answer_text', $data['correct_answer'])
            ->update(['is_correct' => 1]);

        return response()->json($question->fresh()->setAttribute('correct_answer', $data['correct_answer']));
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
