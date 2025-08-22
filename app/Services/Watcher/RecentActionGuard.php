<?php

namespace App\Services\Watcher;

/**
 * In-memory guard to avoid recursive loops (e.g., our own writes
 * triggering new events immediately). TTL default ~8 seconds.
 */
class RecentActionGuard
{
    /** @var array<string,int> */
    private array $guard = [];
    public function __construct(private int $ttlSeconds = 8) {}

    public function mark(string $key): void
    {
        $this->guard[$key] = time();
    }

    public function shouldSkip(string $key): bool
    {
        $now = time();
        foreach ($this->guard as $k => $t) {
            if ($now - $t > $this->ttlSeconds) {
                unset($this->guard[$k]);
            }
        }
        return isset($this->guard[$key]);
    }

    public static function key(string $path, string $action): string
    {
        return sha1($action.'|'.$path);
    }
}
