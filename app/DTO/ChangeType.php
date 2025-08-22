<?php

namespace App\DTO;

enum ChangeType: string
{
    case CREATED  = 'created';
    case MODIFIED = 'modified';
    case DELETED  = 'deleted';
}
