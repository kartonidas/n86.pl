<?php

namespace App\Libraries\Settlement\Abstract;

use Illuminate\Http\Request;

abstract class SettlementObject
{
    protected $object;
    public function __construct($object)
    {
        $this->object = $object;
    }

    public function delete()
    {
    }

    abstract public function getAmount();
    abstract public function getObjectType();
    abstract public function settlement($amount);
    abstract public function getId();
    abstract public static function load(\App\Models\Settlements $settlement);
}
