<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VideoEdit;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function export(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,avi',
            'start' => 'required|numeric',
            'end'   => 'required|numeric',
            'text'  => 'required'
        ]);

        $video = $request->file('video')->store('videos', 'public');
        $text  = json_decode($request->text, true);

        $start = (float) $request->start;
        $end   = (float) $request->end;
        $dur   = $end - $start;

        if ($dur <= 0) {
            return response()->json(['error' => 'Invalid trim range'], 422);
        }

        $input  = storage_path("app/public/$video");
        $outputName = 'out_' . time() . '.mp4';
        $output = storage_path("app/public/exports/$outputName");

        if (!file_exists(dirname($output))) {
            mkdir(dirname($output), 0755, true);
        }

        $ffmpeg = 'C:\\ffmpeg-8.0.1-essentials_build\\bin\\ffmpeg.exe';

        $draw = "drawtext=text='{$text['text']}':x=w*{$text['x']}/100:y=h*{$text['y']}/100:fontsize={$text['size']}:fontcolor={$text['color']}";

        $cmd = "\"$ffmpeg\" -y -ss $start -i \"$input\" -t $dur -vf \"$draw\" -c:v libx264 -c:a aac \"$output\" 2>&1";

        exec($cmd, $ffmpegLog, $status);

        if ($status !== 0) {
            return response()->json([
                'error' => 'FFmpeg failed',
                'log' => $ffmpegLog
            ], 500);
        }

        return response()->json([
            'download' => asset("storage/exports/$outputName")
        ]);
    }
}
