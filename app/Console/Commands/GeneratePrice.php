<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Models\Invoice;

class GeneratePrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate price';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $prices = config("packages.allowed");
        
        $languages = ["pl"];
        foreach($languages as $lang)
        {
            app()->setLocale($lang);
            foreach($prices as $i => $price)
            {
                if($price["months"] == 1)
                    $prices[$i][$lang]["name"] = __("One month");
                else
                    $prices[$i][$lang]["name"] = __("One year");
            }
        }
        
        $fp = fopen(resource_path("js/data/prices.json"), "w");
        fwrite($fp, json_encode($prices, JSON_PRETTY_PRINT));
        fclose($fp);
    }
}
