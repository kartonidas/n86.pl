<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Exceptions\Exception;
use App\Http\Requests\UpdateConfigRequest;
use App\Models\Config;

class ConfigController extends Controller
{
    public function get(Request $request)
    {
        return Config::getConfig("basic");
    }
    
    public function update(UpdateConfigRequest $request)
    {
        $validated = $request->validated();
        
        foreach($validated as $key => $val)
            Config::saveConfig("basic", $key, $val);
            
        return true;
    }
}