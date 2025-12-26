<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VideoEdit;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function export(Request $request)
    {
        // ✅ validate
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,avi',
            'start' => 'required|numeric',
            'end' => 'required|numeric',
            'aspect' => 'required|string'
        ]);

        // ✅ store uploaded video
        $path = $request->file('video')->store('videos', 'public');

        // ✅ save edit info
        $edit = VideoEdit::create([
            'video_path' => $path,
            'start' => $request->start,
            'end' => $request->end,
            'aspect' => $request->aspect,
            'status' => 'processing'
        ]);

        // ⚠️ later this will be FFmpeg job
        // dispatch(new RenderVideoJob($edit));

        return response()->json([
            'edit_id' => $edit->id
        ]);
    }

    public function status($id)
    {
        return VideoEdit::findOrFail($id);
    }
}
