<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        // Menggunakan model gemini-3.1-flash-lite
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent?key=' . $this->apiKey;
    }

    /**
     * Menganalisis catatan belajar dan memberikan respons cerdas.
     *
     * @param string $logNote Catatan yang baru saja dibuat user
     * @param string $projectTitle Judul proyek belajar
     * @return string Balasan dari AI
     */
    public function analyzeLearningLog($logNote, $projectTitle)
    {
        $prompt = $this->buildPrompt($logNote, $projectTitle);

        try {
            $response = Http::timeout(60)->post($this->apiUrl, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                // Mengambil teks jawaban dari struktur JSON Gemini
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return $data['candidates'][0]['content']['parts'][0]['text'];
                }

                return "Maaf, AI gagal memformat jawaban.";
            }

            Log::error('Gemini API Error: ' . $response->body());
            return "Maaf, terjadi kesalahan saat menghubungi server AI.";
        } catch (\Exception $e) {
            Log::error('Gemini Service Exception: ' . $e->getMessage());
            return "Maaf, sistem AI sedang mengalami gangguan.";
        }
    }

    /**
     * Merangkai instruksi rahasia (System Prompt) untuk AI.
     */
    private function buildPrompt($logNote, $projectTitle)
    {
        return "
Kamu adalah Asisten Belajar Proaktif yang sangat cerdas, ramah, dan memotivasi.
Pengguna sedang belajar topik: \"{$projectTitle}\".

Hari ini, dia baru saja menambahkan catatan belajar berikut:
\"{$logNote}\"

Tugasmu sebagai tutor AI berdasarkan catatan di atas adalah:
1. UJI PEMAHAMAN: Berikan 1 atau 2 pertanyaan kuis/tes singkat (essay atau pilihan ganda) yang menantang pemahamannya terhadap materi yang baru saja dia catat tersebut.
2. RENCANA LANJUTAN: Berikan saran singkat tentang apa yang sebaiknya dia pelajari besok agar kemampuannya semakin meningkat (menuju level ahli).
3. MOTIVASI: Tutup dengan kalimat pengingat dan motivasi yang semangat agar dia tidak lupa belajar lagi besok.

Format jawabanmu dengan rapi menggunakan Markdown (Gunakan heading, list, atau bold jika perlu). Jangan terlalu panjang, buat agar mudah dibaca di layar HP.
";
    }

    /**
     * Berinteraksi dengan AI layaknya Chatbot (Tektok)
     *
     * @param string $userMessage Pesan baru dari user
     * @param string $projectTitle Judul proyek untuk konteks
     * @param array $chatHistories Riwayat chat sebelumnya
     * @param string $userName Nama pengguna
     * @return string Balasan AI
     */
    public function chat($userMessage, $projectTitle, $chatHistories, $userName = 'Pengguna')
    {
        $contents = [];
        $tz = new \DateTimeZone('Asia/Jakarta');
        $date = new \DateTime('now', $tz);
        $currentTime = $date->format('Y-m-d H:i');

        // System prompt sebagai konteks pertama
        $systemPrompt = "Nama kamu adalah Anwar, seorang Asisten/Mentor Belajar Pribadi yang cerdas, ramah, dan solutif. "
            . "Kamu adalah hasil ide brilian dan diciptakan oleh seorang mahasiswa bernama Alif. Jika pengguna bertanya tentang asal-usulmu, ceritakan dengan bangga tentang Alif. "
            . "Kamu sedang berbicara dengan muridmu bernama {$userName} yang sedang belajar tentang topik: \"{$projectTitle}\". "
            . "Tugasmu adalah menjawab pertanyaannya, berdiskusi, memberikan saran, dan memotivasi. "
            . "Gunakan bahasa Indonesia yang baik, asik, tidak kaku, dan format Markdown agar rapi. "
            . "WAKTU SAAT INI ADALAH: {$currentTime}. "
            . "INSTRUKSI KHUSUS 1 (CATATAN): Jika pengguna memintamu untuk mencatat/merangkum, sisipkan tag: ||SAVE_NOTE::(isi catatan)|| di akhir balasanmu. "
            . "INSTRUKSI KHUSUS 2 (PENGINGAT/ALARM): Jika pengguna memintamu untuk mengingatkan mereka belajar di waktu tertentu (contoh: 'ingatkan saya besok jam 8 pagi'), "
            . "kamu HARUS menghitung waktu yang tepat berdasarkan waktu saat ini, lalu sisipkan tag: ||SET_REMINDER::YYYY-MM-DD HH:MM::(Pesan pengingat singkat)|| di akhir balasanmu. "
            . "Contoh pengingat: Baik, alarm sudah saya setel! ||SET_REMINDER::2026-07-12 08:00::Waktunya belajar Laravel!||. "
            . "INSTRUKSI KHUSUS 3 (BATASAN TOPIK): JIKA pengguna bertanya atau membahas hal yang SAMA SEKALI TIDAK ADA HUBUNGANNYA dengan topik proyek \"{$projectTitle}\", kamu DILARANG KERAS menjawab substansinya! Tolak dengan sopan dan arahkan pengguna untuk menanyakannya di menu tab 'Anwar Explore' (halaman utama). "
            . "Jika tidak ada perintah khusus, jawab biasa saja sesuai konteks topik tanpa tag apapun.";

        // Gemini API menerima array 'contents' berupa bergantian user dan model
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $systemPrompt]]
        ];

        $contents[] = [
            'role' => 'model',
            'parts' => [['text' => 'Mengerti! Saya siap menjadi mentor Anda.']]
        ];

        // Masukkan riwayat chat
        foreach ($chatHistories as $history) {
            $role = ($history['sender'] === 'ai') ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $history['message']]]
            ];
        }

        // Masukkan pesan terbaru user
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userMessage]]
        ];

        try {
            $response = Http::timeout(60)->post($this->apiUrl, [
                'contents' => $contents
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return $data['candidates'][0]['content']['parts'][0]['text'];
                }
                return "Maaf, AI gagal memformat jawaban.";
            }

            Log::error('Gemini API Error (Chat): ' . $response->body());
            return "Maaf, terjadi kesalahan saat menghubungi server AI.";
        } catch (\Exception $e) {
            Log::error('Gemini Service Exception (Chat): ' . $e->getMessage());
            return "Maaf, sistem AI sedang mengalami gangguan.";
        }
    }
}
