<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearningProject;
use App\Services\Ai\GeminiService;
use Illuminate\Http\Request;

class ChatController extends Controller
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
     * Mengambil riwayat chat untuk proyek tertentu
     */
    public function index(Request $request, $projectId)
    {
        $project = $this->checkProjectOwnership($request, $projectId);

        // Ambil chat histories urut dari yang terlama ke terbaru (untuk UI chat)
        $chats = $project->chatHistories()->orderBy('created_at', 'asc')->get();

        // Jika kosong, buat pesan sapaan pertama dari Anwar
        if ($chats->isEmpty()) {
            $userName = $request->user()->name;
            $firstMessage = $project->chatHistories()->create([
                'sender' => 'ai',
                'ai_model' => 'gemini-1.5-flash',
                'message' => "Hallo {$userName}, saya Anwar disini siap membantu mu dan menjadi mentormu!"
            ]);
            $chats->push($firstMessage);
        }

        return response()->json([
            'status' => 'success',
            'data' => $chats
        ]);
    }

    /**
     * Mengirim pesan ke AI dan mendapatkan balasan
     */
    public function store(Request $request, $projectId)
    {
        $project = $this->checkProjectOwnership($request, $projectId);
        $userName = $request->user()->name;

        $request->validate([
            'message' => 'required|string',
        ]);

        $userMessageText = $request->message;

        // 1. Simpan pesan user
        $userChat = $project->chatHistories()->create([
            'sender' => 'user',
            'message' => $userMessageText
        ]);

        // 2. Ambil riwayat chat sebelumnya (maksimal 10 terakhir agar tidak kepanjangan)
        // Kita exclude pesan user yang baru saja disimpan agar tidak dobel.
        $chatHistories = $project->chatHistories()
            ->where('id', '!=', $userChat->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->reverse()
            ->values()
            ->toArray();

        // 3. Panggil Gemini AI Service untuk chat
        $geminiService = new GeminiService();
        $aiResponseText = $geminiService->chat($userMessageText, $project->title, $chatHistories, $userName);

        // 3.5 Intersepsi perintah penyimpanan catatan dari AI (Tahap 2)
        // Kita mencari pola ||SAVE_NOTE::...||
        if (preg_match('/\|\|SAVE_NOTE::(.*?)\|\|/s', $aiResponseText, $matches)) {
            $noteToSave = trim($matches[1]);

            if (!empty($noteToSave)) {
                // Simpan ke tabel learning_logs secara otomatis
                $project->logs()->create([
                    'note' => $noteToSave,
                    'progress_date' => date('Y-m-d')
                ]);
            }
            // Hapus blok kode rahasia dari balasan AI agar pengguna tidak melihatnya
            $aiResponseText = preg_replace('/\|\|SAVE_NOTE::.*?\|\|/s', '', $aiResponseText);
            $aiResponseText = trim($aiResponseText);
        }

        // 3.6 Intersepsi perintah pengingat (Tahap 3)
        $reminderData = null;
        if (preg_match('/\|\|SET_REMINDER::(.*)::(.*?)\|\|/s', $aiResponseText, $matches)) {
            $reminderTime = trim($matches[1]);
            $reminderMessage = trim($matches[2]);

            $reminderData = [
                'time' => $reminderTime,
                'message' => $reminderMessage
            ];

            // Hapus blok kode rahasia dari balasan AI
            $aiResponseText = preg_replace('/\|\|SET_REMINDER::.*?\|\|/s', '', $aiResponseText);
            $aiResponseText = trim($aiResponseText);
        }

        // 4. Simpan pesan AI
        $aiChat = $project->chatHistories()->create([
            'sender' => 'ai',
            'ai_model' => 'gemini-1.5-flash',
            'message' => $aiResponseText
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pesan terkirim',
            'data' => [
                'user_message' => $userChat,
                'ai_message' => $aiChat
            ],
            'reminder_action' => $reminderData
        ], 201);
    }
}
