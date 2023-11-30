<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Libraries\ValidatorExt;

class Pesel implements Rule
{
    public function passes($attribute, $value)
    {
        if(empty($value))
            return true;
        
        if(!preg_match("/^[0-9]+$/", $value))
           return false;
        
        $sum = 0;
        $weights = array(1, 3, 7, 9, 1, 3, 7, 9, 1, 3, 1);
        
        foreach (str_split($value) as $position => $digit)
            $sum += $digit * ($weights[$position] ?? 0);
    
        return substr($sum % 10, -1, 1) == 0;
    }
    
    public function message()
    {
        return __("Invalid pesel");
    }
}