<?php

namespace App\DTO;

class FileEvent
{
    public function __construct(
        public string $path,
        public string $ext,
        public ChangeType $type
    ) {}
}
