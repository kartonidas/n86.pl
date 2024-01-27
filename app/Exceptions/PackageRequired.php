<?php

namespace App\Exceptions;

use App\Exceptions\ApiBaseException;

class PackageRequired extends ApiBaseException
{
    public $status = 404;
}