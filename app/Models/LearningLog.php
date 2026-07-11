<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningLog extends Model
{
    use HasFactory;

    protected $fillable = ['learning_project_id', 'note', 'progress_date'];

    // Relasi: Catatan log ini dimiliki oleh sebuah project
    public function project()
    {
        return $this->belongsTo(LearningProject::class, 'learning_project_id');
    }
}
