<?php

namespace App\Services\Handlers;

use App\DTO\FileEvent;
use App\Services\Handlers\Contracts\FileEventHandler;
use App\Services\Watcher\RecentActionGuard;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MemeReplaceOnDeleteHandler implements FileEventHandler
{
    public function __construct(private RecentActionGuard $guard) {}

    public function handle(FileEvent $event): void
    {
        // Skip if we just deleted ourselves (e.g., after ZIP extract)
        $guardKey = RecentActionGuard::key($event->path, 'zip-self-delete');
        if ($this->guard->shouldSkip($guardKey)) {
            Log::info("[Watcher] Skipping meme replacement (guard) for {$event->path}");
            return;
        }

        $metaUrl = config('fswatcher.meme_url');
        $meta = Http::get($metaUrl)->json();
        $imgUrl = is_array($meta) && isset($meta['url']) ? $meta['url'] : null;

        if (!$imgUrl) {
            Log::warning("[Watcher] Meme API did not return a URL. {$event->path}");
            return;
        }

        $imgResp = Http::get($imgUrl);
        if (!$imgResp->ok()) {
            Log::warning("[Watcher] Could not fetch meme image: {$imgUrl}");
            return;
        }

        // Write bytes directly to the original path (extension may not match; that's OK per spec)
        @file_put_contents($event->path, $imgResp->body());
        Log::info("[Watcher] Replaced deleted file with meme: {$event->path}");
    }
}
