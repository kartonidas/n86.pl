<?php

namespace App\Exceptions;

use App\Exceptions\ApiBaseException;

class InvalidRentalDates extends ApiBaseException
{
    public $status = 409;
}