<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningProject extends Model
{
    use HasFactory;

    // Kolom yang diizinkan untuk diisi secara massal
    protected $fillable = ['user_id', 'title', 'description', 'status'];

    // Relasi: Satu project memiliki banyak catatan log harian
    public function logs()
    {
        return $this->hasMany(LearningLog::class);
    }

    // Relasi: Satu project memiliki banyak histori chat AI
    public function chatHistories()
    {
        return $this->hasMany(AiChatHistory::class);
    }

    // Relasi: Satu project dimiliki oleh satu user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
