<?php

namespace App\Services\Handlers\Contracts;

use App\DTO\FileEvent;

interface FileEventHandler
{
    public function handle(FileEvent $event): void;
}
