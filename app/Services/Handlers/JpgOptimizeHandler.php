<?php

namespace App\Services\Handlers;

use App\DTO\FileEvent;
use App\Services\Handlers\Contracts\FileEventHandler;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class JpgOptimizeHandler implements FileEventHandler
{
    public function handle(FileEvent $event): void
    {
        // Optimize on create only
        $manager = new ImageManager(new Driver());
        $image = $manager->read($event->path);

        // Re-encode JPEG ~75% quality as a sane web default
        $image->toJpeg(75)->save($event->path);
        Log::info("[Watcher] Optimized JPG: {$event->path}");
    }
}
