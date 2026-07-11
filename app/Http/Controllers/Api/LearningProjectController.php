<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearningProject;
use Illuminate\Http\Request;

class LearningProjectController extends Controller
{
    // 1. Fungsi untuk mengambil semua data (Read)
    public function index(Request $request)
    {
        // Mengambil semua project milik user yang sedang login, diurutkan dari yang terbaru
        $projects = $request->user()->projects()->latest()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data proyek belajar berhasil diambil',
            'data' => $projects
        ], 200);
    }

    // 2. Fungsi untuk menyimpan data baru (Create)
    public function store(Request $request)
    {
        // Validasi data yang dikirim dari Flutter
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Menyimpan ke database dengan ID user yang sedang login
        $project = $request->user()->projects()->create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'aktif'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Proyek belajar berhasil ditambahkan',
            'data' => $project
        ], 201);
    }
    // 3. Fungsi untuk menghapus data (Delete)
    public function destroy(Request $request, $id)
    {
        $project = $request->user()->projects()->where('id', $id)->first();

        if (!$project) {
            return response()->json([
                'status' => 'error',
                'message' => 'Proyek tidak ditemukan atau Anda tidak memiliki akses.'
            ], 404);
        }

        $project->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Proyek berhasil dihapus'
        ], 200);
    }
}
