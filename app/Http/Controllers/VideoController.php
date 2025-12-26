<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Video;

class VideoController extends Controller
{
    public function upload(Request $request)
    {
        $file = $request->file('video');
        $name = uniqid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('videos', $name, 'public');

        $probe = \FFMpeg\FFProbe::create();
        $fullPath = storage_path("app/public/$path");

        $video = Video::create([
            'filename' => $path,
            'duration' => $probe->format($fullPath)->get('duration'),
            'width' => $probe->streams($fullPath)->videos()->first()->get('width'),
            'height' => $probe->streams($fullPath)->videos()->first()->get('height'),
        ]);

        return response()->json($video);
    }
}
