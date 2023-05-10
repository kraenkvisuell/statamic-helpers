<?php

namespace Kraenkvisuell\StatamicHelpers\Jobs;

use Illuminate\Bus\Queueable;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Kraenkvisuell\StatamicHelpers\Facades\Helper;

class CreateAssetPresets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $asset;
    
    public function __construct($asset)
    {
        $this->asset = $asset;
    }

    public function handle()
    {
        $meta = $this->asset->meta() ?? null;
        $presets = config('statamic-helpers.presets');
        $mimeType = $meta['mime_type'];

        if (
            config('statamic-helpers.preset_on_upload') 
            && $presets
            && (
                $mimeType == 'image/jpeg'
                || $mimeType == 'image/png'
            )
        ) {
            $presetDisk = config('statamic-helpers.preset_disk') ?: 'presets';
            $assetDisk = $this->asset->container->disk;
            $assetPath = $this->asset->path;
            $format = $mimeType == 'image/jpeg' ? 'jpg' : 'png';
            
            $url = Helper::asset(path: $assetPath, disk: $assetDisk, useCdn: false);
            
            foreach ($presets as $presetKey => $preset) {
                $img = Image::make($url);
    
                $width = $preset['w'] ?? null;
                $height = $preset['h'] ?? null;
                $quality = $preset['q'] ?? 90;
                
                if ($width || $height) {
                    $img->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }

                $img->encode($format, $quality);

                //$resource = $img->stream()->detach();

                Storage::disk($presetDisk)->put($presetKey.'/'.$assetPath, $img, 'public');
            }
        }
    }
}
