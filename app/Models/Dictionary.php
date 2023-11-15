<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Dictionary extends Model
{
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
}