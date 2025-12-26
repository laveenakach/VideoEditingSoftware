<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VideoEdit;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function export(Request $request)
    {
        // 1. Store video
        $video = $request->file('video')->store('videos', 'public');

        // 2. Parse text
        $text = json_decode($request->text, true);

        $start = (float) $request->start;
        $end   = (float) $request->end;
        $dur   = $end - $start;

        // 3. Paths
        $input  = storage_path("app/public/$video");
        $outputName = 'out_' . time() . '.mp4';
        $output = storage_path("app/public/exports/$outputName");

        // 4. Ensure exports dir exists
        if (!file_exists(dirname($output))) {
            mkdir(dirname($output), 0755, true);
        }

        // 5. FFmpeg path (Windows)
        $ffmpeg = 'C:\\ffmpeg\\bin\\ffmpeg.exe';

        // 6. Draw text
        $draw = "drawtext=text='{$text['text']}':x=w*{$text['x']}/100:y=h*{$text['y']}/100:fontsize={$text['size']}:fontcolor={$text['color']}";

        // 7. Execute
        $cmd = "\"$ffmpeg\" -y -ss $start -i \"$input\" -t $dur -vf \"$draw\" -c:v libx264 -c:a aac \"$output\"";
        exec($cmd, $log, $status);

        // 8. If FFmpeg failed
        if ($status !== 0 || !file_exists($output)) {
            return response()->json([
                'error' => 'Export failed',
                'log' => $log
            ], 500);
        }

        // 9. Return public URL
        return response()->json([
            'download' => asset("storage/exports/$outputName")
        ]);
    }

    public function status($id)
    {
        return VideoEdit::findOrFail($id);
    }
}
