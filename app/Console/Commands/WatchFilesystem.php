<?php

namespace App\Console\Commands;

use App\DTO\ChangeType;
use App\DTO\FileEvent;
use App\Services\Dispatcher\FileEventDispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class WatchFilesystem extends Command
{
    protected $signature = 'fs:watch {path?} {--once}';
    protected $description = 'Poll a directory for file changes and dispatch handlers';

    public function __construct(private FileEventDispatcher $dispatcher)
    {
        parent::__construct();
    }

    /** @var array<string,array{mtime:int,size:int}> */
    private array $snapshot = [];

    public function handle(): int
    {
        $path = $this->argument('path') ?: config('fswatcher.path');
        $interval = (int) config('fswatcher.poll_interval', 2);

        if (!is_dir($path)) {
            $this->error("Path does not exist: {$path}");
            return self::FAILURE;
        }

        $this->info("Watching: {$path} (poll {$interval}s)");
        $this->snapshot = $this->scan($path); // baseline

        do {
            $current = $this->scan($path);
            [$created, $modified, $deleted] = $this->diff($this->snapshot, $current);

            foreach ($created as $p)  { $this->dispatch($p, ChangeType::CREATED); }
            foreach ($modified as $p) { $this->dispatch($p, ChangeType::MODIFIED); }
            foreach ($deleted as $p)  { $this->dispatch($p, ChangeType::DELETED); }

            $this->snapshot = $current;

            if ($this->option('once')) {
                break;
            }

            sleep($interval);
        } while (true);

        return self::SUCCESS;
    }

    private function dispatch(string $path, ChangeType $type): void
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $this->dispatcher->dispatch(new FileEvent($path, $ext, $type));
        Log::info("[Watcher] {$type->value}: {$path}");
        $this->line("{$type->value}: {$path}");
    }

    /**
     * @return array<string,array{mtime:int,size:int}>
     */
    private function scan(string $root): array
    {
        $info = [];

        $rii = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
        );

        $ignores = config('fswatcher.ignore', []);

        /** @var \SplFileInfo $file */
        foreach ($rii as $file) {
            if (!$file->isFile()) continue;

            $path = $file->getPathname();
            $skip = false;
            foreach ($ignores as $pattern) {
                if (preg_match($pattern, $path)) {
                    $skip = true; break;
                }
            }
            if ($skip) continue;

            $info[$path] = [
                'mtime' => (int) $file->getMTime(),
                'size'  => (int) $file->getSize(),
            ];
        }
        return $info;
    }

    /**
     * @param array<string,array{mtime:int,size:int}> $old
     * @param array<string,array{mtime:int,size:int}> $new
     * @return array{0:array<int,string>,1:array<int,string>,2:array<int,string>}
     */
    private function diff(array $old, array $new): array
    {
        $created = array_values(array_diff(array_keys($new), array_keys($old)));
        $deleted = array_values(array_diff(array_keys($old), array_keys($new)));

        $modified = [];
        foreach ($new as $path => $stat) {
            if (!isset($old[$path])) continue;
            if ($stat['mtime'] !== $old[$path]['mtime'] || $stat['size'] !== $old[$path]['size']) {
                $modified[] = $path;
            }
        }

        return [$created, $modified, $deleted];
    }
}
