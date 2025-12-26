<?php

namespace App\Jobs;

use App\Models\VideoEdit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RenderVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public VideoEdit $edit) {}

    public function handle(): void
{
    $input = storage_path('app/public/' . $this->edit->video_path);

    $outputName = 'output_' . time() . '.mp4';
    $outputDir = storage_path('app/public/exports');

    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0777, true);
    }

    $output = $outputDir . '/' . $outputName;

    $start  = $this->edit->start;
    $end    = $this->edit->end;
    $aspect = $this->edit->aspect;

    $filter = match ($aspect) {
        '9:16' => 'crop=ih*9/16:ih',
        '1:1'  => 'crop=ih:ih',
        default => 'scale=iw:ih'
    };

    exec("ffmpeg -y -i \"$input\" -ss $start -to $end -vf \"$filter\" \"$output\"");

    $this->edit->update([
        'output_path' => '/storage/exports/' . $outputName,
        'status' => 'completed'
    ]);
}

}
