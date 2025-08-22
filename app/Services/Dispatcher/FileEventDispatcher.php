<?php

namespace App\Services\Dispatcher;

use App\DTO\FileEvent;
use App\DTO\ChangeType;
use Illuminate\Support\Facades\Log;

class FileEventDispatcher
{
    public function dispatch(FileEvent $event): void
    {
        $map = config('fswatcher.handlers', []);
        $key = strtolower($event->ext).'.'.$event->type->value;

        $handlerClass = $map[$key] ?? null;

        if (!$handlerClass && $event->type === ChangeType::DELETED) {
            $handlerClass = config('fswatcher.deletion_handler');
        }

        if (!$handlerClass) {
            Log::info("[Watcher] No handler for event {$key} on {$event->path}");
            return;
        }

        try {
            app($handlerClass)->handle($event);
        } catch (\Throwable $e) {
            Log::error("[Watcher] Handler error {$handlerClass} for {$key} on {$event->path}: ".$e->getMessage());
        }
    }
}
