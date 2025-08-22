<?php

namespace App\Services\Handlers;

use App\DTO\ChangeType;
use App\DTO\FileEvent;
use App\Services\Dispatcher\FileEventDispatcher;
use App\Services\Handlers\Contracts\FileEventHandler;
use App\Services\Watcher\RecentActionGuard;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class ZipExtractHandler implements FileEventHandler
{
    public function __construct(
        private FileEventDispatcher $dispatcher,
        private RecentActionGuard $guard
    ) {}

    public function handle(FileEvent $event): void
    {
        $zip = new ZipArchive();
        if ($zip->open($event->path) !== true) {
            Log::warning("[Watcher] Could not open ZIP: {$event->path}");
            return;
        }

        $targetDir = preg_replace('/\.zip$/i', '', $event->path);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $zip->extractTo($targetDir);
        $zip->close();
        Log::info("[Watcher] Extracted ZIP to {$targetDir}");

        // Dispatch "created" for extracted files
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($targetDir));
        foreach ($rii as $file) {
            if ($file->isFile()) {
                $path = $file->getPathname();
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $this->dispatcher->dispatch(new FileEvent($path, $ext, ChangeType::CREATED));
            }
        }

        // Delete archive, and guard against anti-delete replacement (we initiated)
        $delGuardKey = RecentActionGuard::key($event->path, 'zip-self-delete');
        $this->guard->mark($delGuardKey);
        @unlink($event->path);
        Log::info("[Watcher] Deleted ZIP archive {$event->path}");
    }
}
