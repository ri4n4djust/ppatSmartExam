<?php

namespace App\Http\Controllers;

use App\Models\Exams;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Questions;
use App\Models\ExamQuestions;
use Illuminate\Support\Facades\DB;

class examsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Exams::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        // $exam = Exams::create($this->validatedData($request));
        // $exam = new Exams();
        // $exam->title = $request->title;
        // $exam->start_time = $request->start_time;
        // $exam->end_time = $request->end_time;
        // $exam->duration = $request->duration;
        // $exam->count_qa = $request->count_qa;
        // $exam->status = $request->status;
        // $exam->save();
        $examId = DB::table('exams')->insertGetId([
            'title'      => $request->title,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'duration'   => $request->duration,
            'count_qa'   => $request->count_qa,
            'status'     => $request->status,
        ]);

        // // 2. Ambil soal random sesuai count_qa
        // $questions = DB::table('questions')
        //     ->inRandomOrder()
        //     ->limit($request->count_qa)
        //     ->pluck('id');

        // // 3. Insert ke exam_question
        // $examQuestions = $questions->map(function ($questionId) use ($examId) {
        //     return [
        //         'exam_id'     => $examId,
        //         'question_id' => $questionId,
        //     ];
        // })->toArray();

        // DB::table('exam_question')->insert($examQuestions);

        return response()->json($examId, 201);
    }

    public function submitExam(Request $request)
    {
        $examId = $request->exam_id;
        $userId = $request->siswa_id;
        $answers = $request->answers;

        // Simpan jawaban ke tabel exam_answers
        foreach ($answers as $answer) {
            $isCorrect = DB::table('question_answers')
                ->where('question_id', $answer['question_id'])
                ->where('answer_text', $answer['answer'])
                ->value('is_correct');
            if ($isCorrect === null  || $isCorrect === 0) {
                $isCorrect = 0; // Jika jawaban tidak ditemukan, anggap salah
                $score = DB::table('questions')
                ->where('id', $answer['question_id'])
                ->value('score_ifwrong');
            }else {
                $isCorrect = (int)$isCorrect; // Konversi ke integer
                $score = DB::table('questions')
                ->where('id', $answer['question_id'])
                ->value('score_value');
            }
            
            DB::table('exam_answers')->insert([
                'exam_id' => $examId,
                'siswa_id' => $userId,
                'question_id' => $answer['question_id'],
                'answer_text' => $answer['answer'],
                'is_correct' => $isCorrect,
                'score' => $score,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Exam submitted successfully']);
    }

    public function getExamResults(Request $request)
    {
        if (! $request->filled('exam_id') && $request->filled('exam'))
            $request->merge(['exam_id' => $request->input('exam')]);

        $data = $request->validate([
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
        ]);

        $examId = $data['exam_id'];

        $detail = DB::table('questions')
                ->join('exam_answers', 'questions.id', '=', 'exam_answers.question_id')
                ->select(
                    'questions.id as question_id',
                    'questions.text as question_text',
                    'questions.options',
                    'exam_answers.answer_text',
                    'exam_answers.is_correct',
                    'exam_answers.score'
                )
                ->where('exam_answers.exam_id', $examId)
                ->where('exam_answers.siswa_id', $request->siswa_id)
                ->get();


        return response()->json([
            'exam_id' => $examId,
            'result' => $detail
        ]);
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
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'duration' => ['required', 'integer', 'min:1', 'max:1440'],
            'count_qa' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'string', 'in:Scheduled,Ongoing,Completed'],
        ]);
    }
}
