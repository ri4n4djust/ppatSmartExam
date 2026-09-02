<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class laporanController extends Controller
{
    //
    public function indexUser(Request $request)
    {
        $user = $request->user();
        $laporan = \DB::table('exams')
            ->join('exam_answers', 'exams.id', '=', 'exam_answers.exam_id')
            ->select(
                'exams.id as exam_id',
                'exams.title',
                'exams.duration',
                'exams.count_qa',
                'exams.status',
                \DB::raw('DATE(MAX(exam_answers.created_at)) as exam_date'),
                \DB::raw('SUM(exam_answers.score) as total_score')
            )
            ->where('exam_answers.siswa_id', $user->id)
            ->groupBy(
                
                'exams.id',
                'exams.title',
                'exams.duration',
                'exams.count_qa',
                'exams.status'
            )
            ->get();

        return response()->json($laporan);
    }

    public function indexAdmin(Request $request)
    {
        $laporan = \DB::table('exams')
            ->join('exam_answers', 'exams.id', '=', 'exam_answers.exam_id')
            ->join('siswa', 'exam_answers.siswa_id', '=', 'siswa.id')
            ->select(
                'exams.id as exam_id',
                'siswa.id as student_id',
                'exams.title',
                'exams.duration',
                'exams.count_qa',
                'exams.status',
                \DB::raw('DATE(MAX(exam_answers.created_at)) as exam_date'),
                \DB::raw('SUM(exam_answers.score) as total_score'),
                'siswa.username as student_name',
                'siswa.email as student_email'
            )
            ->groupBy(
                'exams.id',
                'siswa.id',
                'exams.title',
                'exams.duration',
                'exams.count_qa',
                'exams.status',
                'siswa.username',
                'siswa.email'
            )
            ->get();

        return response()->json($laporan);
    }
    public function indexSiswa(Request $request)
    {
        $siswa = \DB::table('siswa')->where('role', '!=', 'admin')->get();
        return response()->json($siswa);
    }
    public function indexPengguna(Request $request)
    {
        $pengguna = \DB::table('users')->where('role', 'admin')->get();
        return response()->json($pengguna);
    }

}
