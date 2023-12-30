<?php

namespace App\Libraries;

class Data
{
    public static function getSystemBillTypes()
    {
        return [
            "rent" => [-1, __("Rent")],
            "deposit" => [-2, __("Deposit")],
        ];
    }
}