<?php

namespace App\Libraries;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FbTrack
{
    private static $apiVersion = "v19.0";
    private static $id = "1206775190283798";
    private static $token = "EAAFJQIRQZAJIBOxm8TI6CJy63tLUZBdwE7VGagiMUUdq7Lr9K36PdABLg2HyIzxbG0SAOdI30adz0eGf6cZCyXf5nNY7C7UgAWfBbAxTaMaDcST2AsORtZBj5vZCWWdNCOkCFzbqKtxTc5E1rYRU5VI5ZAaYYDdIaJFtVT1b6jxTwoiOhlsrdVzzHaIyhTrClDnwZDZD";
    
    private static function getEndpoint()
    {
        return sprintf("https://graph.facebook.com/%s/%s/events", self::$apiVersion, self::$id);
    }
    
    public static function track(Request $request, string $event, array $trackParams = null)
    {
        if(env("APP_DEV_MODE", false))
            return;
        
        try
        {
            $userData = [
                "client_ip_address" => $request->ip(),
                "client_user_agent" => $request->header("User-Agent"),
            ];
            
            if(!empty($trackParams["user_data"]["em"]))
                $userData["em"] = hash("sha256", $trackParams["user_data"]["em"]);
            if(!empty($trackParams["user_data"]["fn"]))
                $userData["fn"] = hash("sha256", $trackParams["user_data"]["fn"]);
            if(!empty($trackParams["user_data"]["ln"]))
                $userData["ln"] = hash("sha256", $trackParams["user_data"]["ln"]);
                
            $data = [
                "action_source" => "website",
                "event_name" => $event,
                "event_time" => time(),
                "event_source_url" => url()->current(),
                "user_data" => $userData
            ];
            
            if(!empty($trackParams["custom_data"]) && is_array($trackParams["custom_data"]))
                $data = array_merge($data, $trackParams["custom_data"]);
            
            $response = Http::asForm()->post(self::getEndpoint(), ["access_token" => self::$token, "data" => [json_encode($data)]]);
        }
        catch(Throwable $e) {}
    }
}
