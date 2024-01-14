<?php

namespace App\Libraries;

use Kreait\Firebase\Factory;

class FirebaseHelper {
    public static function createUser($uuid, $password, $name)
    {
        $userProperties = [
            "uid" => $uuid,
            "email" => $uuid . "@n86.pl",
            "password" => $password,
            "disabled" => false,
        ];
        $auth = app('firebase.auth');
        $createdUser = $auth->createUser($userProperties);
        return true;
    }
    
    public static function updateStats($uuid, $stats)
    {
        $factory = (new Factory)
            ->withServiceAccount(env("GOOGLE_APPLICATION_CREDENTIALS"))
            ->withDatabaseUri(env("FIREBASE_DATABASE_URI"));
        
        $database = $factory->withDatabaseAuthVariableOverride(["uid" => env("FIREBASE_ADMIN_UUID")])->createDatabase();
        $database->getReference("stats/" . $uuid)->set($stats);
    }
}
