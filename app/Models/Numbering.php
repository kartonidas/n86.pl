<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Numbering extends Model
{
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }

    public $timestamps = false;
    protected $table = 'numbering';
}
