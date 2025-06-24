<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Todo extends Model
{
    protected $table = 'todos';

    protected $fillable = ['title', 'description', 'file_path'];

    public function getFilePathAttribute($file_path)
    {
        return $file_path ? Storage::disk('public')->url($file_path) : null;
    }
}
