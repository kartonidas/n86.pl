<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\ContactRequest;
use App\Libraries\Data;
use App\Libraries\FbTrack;
use App\Libraries\Helper;
use App\Mail\ContactForm;

use App\Exceptions\ObjectNotExist;
use App\Exceptions\Unauthorized;

class PageController extends Controller
{
    public function index(Request $request)
    {
        FbTrack::track($request, "ViewContent");
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
    
    public function features(Request $request)
    {
        $path = mb_strtolower($request->path());
        $path = explode("/", $path);
        
        if(!in_array($path[0], ["najwazniejsze-funkcje"]))
            abort(404);
        
        $pageFile = resource_path("views/html/" . app()->getLocale() . "/" . $path[1] . ".html");
        if(!file_exists($pageFile))
            abort(404);
            
        $html = file_get_contents($pageFile);
        $xml = simplexml_load_string($html);
        
        return view("pages.features", [
            "html" => self::prepareVariables((string)$xml->content),
            "title" => (string)$xml->title,
            "subtitle" => (string)($xml->subtitle ?? null),
            "current" => $path[1],
            "meta" => [
                "title" => self::prepareVariables((string)($xml->meta->title ?? (string)$xml->title)),
                "description" => (string)($xml->meta->description ?? null),
            ]
        ]);
    }
    
    public function contact(ContactRequest $request)
    {
        $validated = $request->validated();
        if(!Helper::verifyCaptcha($validated["g-recaptcha-response"]))
            throw new Unauthorized("Udowodnij, że nie jesteś robotem");
        
        // wysyłka
        $mailable = new ContactForm($validated);
        $mailable->to(env("CONTACT_FORM_EMAIL"));
        Mail::send($mailable);
        
        return [
            "success" => true,
            "html" => view("pages._contact-success")->render(),
        ];
    }
    
    public function help(Request $request, $slug = null)
    {
        if($slug !== null)
        {
            $slug = explode("/", $slug);
            
            if(count($slug) != 2 || !in_array($slug[0], Data::getHelpCategories()))
                abort(404);
                
            $helpFile = resource_path("views/help/html/" . app()->getLocale() . "/" . $slug[0] . "/" . $slug[1] . ".html");
            if(file_exists($helpFile))
            {
                $html = file_get_contents($helpFile);
                $xml = simplexml_load_string($html);
                
                return view("help.details", [
                    "help" => (string)$xml->content,
                    "title" => (string)$xml->title,
                    "category" => array_flip(Data::getHelpCategories())[$slug[0]],
                ]);
            }
            else
                abort(404);
        }
        else
        {
            $helpCategories = [];
            foreach(Data::getHelpCategories() as $categoryName => $categoryPath)
            {
                $path = "views/help/html/" . app()->getLocale() . "/" . $categoryPath . "/";
                
                $allHelpFiles = scandir(resource_path($path));
                foreach($allHelpFiles as $helpFile)
                {
                    if($helpFile == "." || $helpFile == ".." || substr($helpFile, -5) != ".html")
                        continue;
                    
                    $html = file_get_contents(resource_path($path) . $helpFile);
                    $xml = simplexml_load_string($html);
                    
                    $helpCategories[$categoryName][(int)$xml->attributes()["sort"] ?? 999][] = [
                        "title" => (string)$xml->title,
                        "slug" => self::getHelpPrefixUrl() . $categoryPath . "/" . substr($helpFile, 0, -5),
                    ];
                }
                ksort($helpCategories[$categoryName]);
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
    
    private static function prepareVariables($html)
    {
        $html = str_replace(
            [
                "[APP_NAME]",
                "[TRY_FOR_FREE]",
                "[META_TITLE]",
            ],
            [
                env("APP_NAME"),
                view("pages._features_try_for_free")->render(),
                config("page.meta.title_postfix"),
            ],
            $html
        );
        return $html;
    }
    
    public function fbtrack(Request $request)
    {
        $actionSource = explode(":", $request->input("source", null));
        
        $eventTrack = null;
        $eventParams = ["custom_data" => []];
        
        $action = $actionSource[0];
        $source = $actionSource[1] ?? null;
        
        switch($action)
        {
            case "signup":
                $allowedSources = ["header", "intro", "price-month", "price-year"];
                if(in_array($source, $allowedSources))
                {
                    $eventTrack = "ViewContent";
                    $eventParams["custom_data"] = [
                        "content_name" => "sign-up",
                        "content_category" => $source
                    ];
                }
            break;
        }
        
        if($eventTrack !== null)
            FbTrack::track($request, $eventTrack, $eventParams);
    }
}