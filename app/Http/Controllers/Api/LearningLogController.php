<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearningLog;
use App\Models\LearningProject;
use App\Services\Ai\GeminiService;
use Illuminate\Http\Request;

class LearningLogController extends Controller
{
    /**
     * Helper function untuk memverifikasi apakah project ini milik user yang sedang login
     */
    private function checkProjectOwnership(Request $request, $projectId)
    {
        $project = LearningProject::where('id', $projectId)
                    ->where('user_id', $request->user()->id)
                    ->first();
        if (!$project) {
            abort(404, 'Proyek belajar tidak ditemukan atau Anda tidak memiliki akses.');
        }

        return $project;
    }

    /**
     * Menampilkan daftar catatan belajar untuk suatu proyek
     */
    public function index(Request $request, $projectId)
    {
        $project = $this->checkProjectOwnership($request, $projectId);

        // Ambil logs dari project tersebut, diurutkan dari tanggal progres terbaru
        $logs = $project->logs()->orderBy('progress_date', 'desc')->latest()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Catatan belajar berhasil diambil',
            'data' => $logs
        ]);
    }

    /**
     * Menyimpan catatan belajar baru
     */
    public function store(Request $request, $projectId)
    {
        $project = $this->checkProjectOwnership($request, $projectId);

        $request->validate([
            'note' => 'required|string',
            'progress_date' => 'required|date',
        ]);

        $log = $project->logs()->create([
            'note' => $request->note,
            'progress_date' => $request->progress_date,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Catatan belajar berhasil ditambahkan.',
            'data' => [
                'log' => $log,
            ]
        ], 201);
    }

    /**
     * Mengupdate catatan belajar
     */
    public function update(Request $request, $projectId, $logId)
    {
        $project = $this->checkProjectOwnership($request, $projectId);

        $request->validate([
            'note' => 'required|string',
            'progress_date' => 'required|date',
        ]);

        $log = $project->logs()->where('id', $logId)->firstOrFail();

        $log->update([
            'note' => $request->note,
            'progress_date' => $request->progress_date,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Catatan belajar berhasil diperbarui',
            'data' => $log
        ]);
    }

    /**
     * Menghapus catatan belajar
     */
    public function destroy(Request $request, $projectId, $logId)
    {
        $project = $this->checkProjectOwnership($request, $projectId);

        $log = $project->logs()->where('id', $logId)->firstOrFail();
        $log->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Catatan belajar berhasil dihapus'
        ]);
    }
}
