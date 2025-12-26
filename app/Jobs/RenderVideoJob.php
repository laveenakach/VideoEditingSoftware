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

        $start  = (float) $this->edit->start;
        $end    = (float) $this->edit->end;
        $aspect = $this->edit->aspect;

        $duration = $end - $start;

        if ($duration <= 0) {
            throw new \Exception('Invalid trim duration');
        }

        $filter = match ($aspect) {
            '9:16' => 'crop=ih*9/16:ih',
            '1:1'  => 'crop=ih:ih',
            default => 'scale=iw:ih'
        };

        exec(
            "ffmpeg -y -ss $start -i \"$input\" -t $duration -vf \"$filter\" -c:a copy \"$output\""
        );

        $this->edit->update([
            'output_path' => '/storage/exports/' . $outputName,
            'status' => 'completed'
        ]);
    }
}
