<?php

namespace App\Services\Handlers;

use App\DTO\FileEvent;
use App\Services\Handlers\Contracts\FileEventHandler;
use App\Services\Watcher\RecentActionGuard;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TxtAppendHandler implements FileEventHandler
{
    public function __construct(private RecentActionGuard $guard) {}

    public function handle(FileEvent $event): void
    {
        // Prevent infinite loop: our append triggers a "modified" event
        $guardKey = RecentActionGuard::key($event->path, 'txt-append');
        if ($this->guard->shouldSkip($guardKey)) {
            Log::info("[Watcher] Skipping (guard) TXT append on {$event->path}");
            return;
        }

        $url = config('fswatcher.bacon_url');
        $resp = Http::get($url);

        $text = '';
        if ($resp->ok()) {
            $json = $resp->json();
            // BaconIpsum returns an array of paragraphs
            if (is_array($json)) {
                $text = "\n\n".implode("\n\n", $json)."\n";
            } else {
                $text = "\n\n".$resp->body()."\n";
            }
        }

        file_put_contents($event->path, $text, FILE_APPEND);
        $this->guard->mark($guardKey);
        Log::info("[Watcher] Appended Bacon Ipsum to {$event->path}");
    }
}
