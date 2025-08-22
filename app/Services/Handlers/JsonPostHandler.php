<?php

namespace App\Services\Handlers;

use App\DTO\FileEvent;
use App\Services\Handlers\Contracts\FileEventHandler;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JsonPostHandler implements FileEventHandler
{
    public function handle(FileEvent $event): void
    {
        $url = config('fswatcher.post_url');
        $raw = @file_get_contents($event->path) ?: '';

        $payload = json_decode($raw, true);
        if ($payload === null) {
            $payload = ['raw' => $raw];
        }

        $resp = Http::asJson()->post($url, $payload);
        Log::info("[Watcher] JSON posted to {$url} (status {$resp->status()}): {$event->path}");
    }
}
