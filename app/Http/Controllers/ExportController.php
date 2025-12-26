<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VideoEdit;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function export(Request $request)
    {
        // 1️⃣ Validate
        $request->validate([
            'video'  => 'required|file|mimes:mp4,mov,avi',
            'start'  => 'required|numeric',
            'end'    => 'required|numeric',
            'text'   => 'required'
        ]);

        // 2️⃣ Store uploaded video
        $videoPath = $request->file('video')->store('videos', 'public');

        // 3️⃣ Parse inputs
        $start = (float) $request->start;
        $end   = (float) $request->end;
        $duration = $end - $start;

        if ($duration <= 0) {
            return response()->json(['error' => 'Invalid trim range'], 422);
        }

        $text = json_decode($request->text, true);

        // 4️⃣ SAVE EDIT IN DB (THIS WAS MISSING BEFORE)
        $edit = VideoEdit::create([
            'video_path' => $videoPath,
            'edit_data' => [
                'trim' => [
                    'start' => $start,
                    'end'   => $end
                ],
                'text' => $text
            ],
            'status' => 'processing'
        ]);

        // 5️⃣ Prepare paths
        $input = storage_path("app/public/$videoPath");

        $outputName = 'out_' . time() . '.mp4';
        $outputDir  = storage_path('app/public/exports');
        $output     = $outputDir . '/' . $outputName;

        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // 6️⃣ FFmpeg path (WINDOWS)
        $ffmpeg = 'C:\\ffmpeg-8.0.1-essentials_build\\bin\\ffmpeg.exe';

        // 7️⃣ Build drawtext filter
        $drawText = "drawtext=text='{$text['text']}':" .
                    "x=w*{$text['x']}/100:" .
                    "y=h*{$text['y']}/100:" .
                    "fontsize={$text['size']}:" .
                    "fontcolor={$text['color']}";

        // 8️⃣ FINAL FFmpeg command (THIS ONE WORKS)
        $cmd = "\"$ffmpeg\" -y -ss $start -i \"$input\" -t $duration " .
               "-vf \"$drawText\" -c:v libx264 -c:a aac \"$output\" 2>&1";

        exec($cmd, $log, $status);

        if ($status !== 0) {
            $edit->update(['status' => 'failed']);

            return response()->json([
                'error' => 'FFmpeg failed',
                'log' => $log
            ], 500);
        }

        // 9️⃣ Update DB with output
        $edit->update([
            'status' => 'completed',
            'output_path' => "exports/$outputName"
        ]);

        // 🔟 Return download link
        return response()->json([
            'download' => asset("storage/exports/$outputName")
        ]);
    }
}
