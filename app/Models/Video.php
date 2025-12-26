<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\VideoEdit;

class Video extends Model
{
    protected $fillable = ['filename','duration','width','height'];
    public function edits() {
        return $this->hasMany(VideoEdit::class);
    }
}
