<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Exceptions\ObjectNotExist;

class PageController extends Controller
{
    public function index(Request $request)
    {
        return view("pages.index");
    }
    
    public function regulations(Request $request)
    {
        return view("pages.regulations");
    }
    
    public function privacyPolicy(Request $request)
    {
        return view("pages.privacy_policy");
    }
    
    public function cookies(Request $request)
    {
        return view("pages.cookies");
    }
    
    public function help(Request $request, $slug = null)
    {
        if($slug !== null)
        {
            $helpFile = resource_path("views/help/html/" . app()->getLocale() . "/" . $slug . ".html");
            if(file_exists($helpFile))
            {
                $html = file_get_contents($helpFile);
                $xml = simplexml_load_string($html);
                
                return view("help.details", [
                    "help" => (string)$xml->content,
                    "title" => (string)$xml->title,
                ]);
            }
            else
                abort(404);
        }
        else
        {
            $helpCategories = [];
            $path = "views/help/html/" . app()->getLocale() . "/";
            
            $allHelpFiles = scandir(resource_path($path));
            foreach($allHelpFiles as $helpFile)
            {
                if($helpFile == "." || $helpFile == ".." || substr($helpFile, -5) != ".html")
                    continue;
                
                $html = file_get_contents(resource_path($path) . $helpFile);
                $xml = simplexml_load_string($html);
                
                $helpCategories[(string)$xml->attributes()["category"]][] = [
                    "title" => (string)$xml->title,
                    "slug" => self::getHelpPrefixUrl() . substr($helpFile, 0, -5),
                ];
            }
            
            return view("help.index", [
                "categories" => $helpCategories,
            ]);
        }
    }
    
    private static function getHelpPrefixUrl()
    {
        switch(app()->getLocale())
        {
            case "en":
                return "/help/";
            default:
                return "/pomoc/";
        }
    }
}