<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_chat_histories', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel learning_projects agar obrolannya sesuai konteks proyek
            $table->foreignId('learning_project_id')->constrained()->cascadeOnDelete();
            $table->enum('sender', ['user', 'ai']);
            $table->string('ai_model')->nullable();
            $table->text('message');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_chat_histories');
    }
};
