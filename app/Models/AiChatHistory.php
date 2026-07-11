<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiChatHistory extends Model
{
    use HasFactory;

    protected $fillable = ['learning_project_id', 'sender', 'ai_model', 'message'];

    // Relasi: Histori chat ini merujuk pada sebuah project belajar
    public function project()
    {
        return $this->belongsTo(LearningProject::class, 'learning_project_id');
    }
}
