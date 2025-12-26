<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoEdit extends Model
{
    protected $fillable = ['edit_data','status','output_path'];
    protected $casts = ['edit_data' => 'array'];
}
