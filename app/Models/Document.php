<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class Document extends Model
{
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    protected $casts = [
        "created_at" => 'datetime:Y-m-d H:i',
        "updated_at" => 'datetime:Y-m-d H:i',
    ];
    
    protected $hidden = ["uuid"];
}