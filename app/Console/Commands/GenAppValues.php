<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Rental;

class GenAppValues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:gen-values';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate App values (resources/js/data/values.json)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $languages = ["pl"];
         
        
        $toJson = [];
        foreach($languages as $lang)
        {
            app()->setLocale($lang);
            
            if(!isset($toJson[$lang]))
                $toJson[$lang] = [];
                
            $toJson[$lang]["customer_types"] = [
                ["id" => Customer::TYPE_PERSON, "name" => __("Person")],
                ["id" => Customer::TYPE_FIRM, "name" => __("Firm")],
            ];
            
            $toJson[$lang]["tenant_types"] = [
                ["id" => Customer::TYPE_PERSON, "name" => __("Person")],
                ["id" => Customer::TYPE_FIRM, "name" => __("Firm")],
            ];
            
            foreach(Item::getTypes() as $type => $name)
                $toJson[$lang]["item_types"][] = ["id" => $type, "name" => $name];
                
            foreach(Item::getOwnershipTypes() as $type => $name)
                $toJson[$lang]["ownership_types"][] = ["id" => $type, "name" => $name];
                
            foreach(Rental::getPeriods() as $type => $name)
                $toJson[$lang]["rental"]["periods"][] = ["id" => $type, "name" => $name];
            
            foreach(Rental::getTerminationPeriods() as $type => $name)
                $toJson[$lang]["rental"]["termination_periods"][] = ["id" => $type, "name" => $name];
            
            foreach(Rental::getPaymentsType() as $type => $name)
                $toJson[$lang]["rental"]["payments"][] = ["id" => $type, "name" => $name];
            
            foreach(Rental::getPaymentDays() as $type => $name)
                $toJson[$lang]["rental"]["payment_days"][] = ["id" => $type, "name" => $name];
        }
        
        $fp = fopen(resource_path("js/data/values.json"), "w");
        fwrite($fp, json_encode($toJson, JSON_PRETTY_PRINT));
        fclose($fp);
    }
}
