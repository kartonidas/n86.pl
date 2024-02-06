<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BalanceDocument;
use App\Models\ConfigNotification;
use App\Models\Customer;
use App\Models\DocumentTemplate;
use App\Models\Item;
use App\Models\ItemCyclicalFee;
use App\Models\Numbering;
use App\Models\Rental;
use App\Models\SaleRegister;

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
            
            foreach(Customer::getDocumentTypes() as $type => $name)
                $toJson[$lang]["customer"]["documents"][] = ["id" => $type, "name" => $name];
            
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
                
            foreach(Rental::getStatuses(true) as $type => $name)
                $toJson[$lang]["rental"]["statuses"][] = ["id" => $type, "name" => $name];
                
            foreach(Numbering::getNumberingContinuation() as $type => $name)
                $toJson[$lang]["rental"]["mask_numbering_continuation"][] = ["id" => $type, "name" => $name];
                
            foreach(ItemCyclicalFee::getPaymentDays() as $type => $name)
                $toJson[$lang]["cyclical_fee"]["payment_days"][] = ["id" => $type, "name" => $name];
                
            foreach(ItemCyclicalFee::getRepeatMonths() as $type => $name)
                $toJson[$lang]["cyclical_fee"]["repeat_months"][] = ["id" => $type, "name" => $name];
                
            foreach(BalanceDocument::getAvailablePaymentMethods() as $type => $name)
                $toJson[$lang]["payments"]["methods"][] = ["id" => $type, "name" => $name];
                
            foreach(DocumentTemplate::getTypes() as $type => $name)
                $toJson[$lang]["documents"]["types"][] = ["id" => $type, "name" => $name];
                
            foreach(SaleRegister::getNumberingContinuation() as $type => $name)
                $toJson[$lang]["invoices"]["sale_register"]["continuation"][] = ["id" => $type, "name" => $name];
                
            foreach(SaleRegister::getAllowedTypes() as $type => $name)
                $toJson[$lang]["invoices"]["types"][] = ["id" => $type, "name" => $name];
                
            foreach(SaleRegister::getAllowedTypes(true) as $type => $name)
                $toJson[$lang]["invoices"]["types_new"][] = ["id" => $type, "name" => $name];
                
            foreach(config("invoice.vat") as $type => $name)
                $toJson[$lang]["invoices"]["vat_values"][] = ["id" => (string)$type, "name" => $name];
            
            foreach(config("invoice.gtu") as $type => $name)
                $toJson[$lang]["invoices"]["gtu"][] = ["id" => $type, "name" => $name];
            
            foreach(ConfigNotification::getAllowedOwners() as $type => $name)
                $toJson[$lang]["notifications"]["owner"][] = ["id" => $type, "name" => $name];
                
            foreach(ConfigNotification::getAllowedTypes() as $type => $row)
                $toJson[$lang]["notifications"]["types"][] = array_merge(["id" => $type], $row);
                
            foreach(ConfigNotification::getAllowedModes() as $type => $name)
                $toJson[$lang]["notifications"]["modes"][] = ["id" => $type, "name" => $name];
        }
        
        $fp = fopen(resource_path("js/data/values.json"), "w");
        fwrite($fp, json_encode($toJson, JSON_PRETTY_PRINT));
        fclose($fp);
    }
}
