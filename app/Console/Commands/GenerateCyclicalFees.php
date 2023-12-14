<?php

namespace App\Console\Commands;

use DateTime;
use Illuminate\Console\Command;
use App\Models\ItemCyclicalFee;
use App\Models\ItemBill;

class GenerateCyclicalFees extends Command
{
    protected $signature = 'app:generate-cyclical-fees';

    protected $description = 'Generate cyclical fees';

    public function handle(): void
    {
        $time = time();
        $cyclicalFees = ItemCyclicalFee
            ::where("beginning", "<=", $time)
            ->where(function($query) use($time) {
                $query
                    ->whereNull("last_generated")
                    ->orWhereRaw("DATE_ADD(FROM_UNIXTIME(last_generated), INTERVAL repeat_months MONTH) <= ?", $time);
            })
            ->withoutGlobalScopes()
            ->get();
        
        if(!$cyclicalFees->isEmpty())
        {
            foreach($cyclicalFees as $cyclicalFee)
            {
                $paymentDate = new DateTime();
                $paymentDate->setDate(date("Y"), date("m"), $cyclicalFee->payment_day);
                
                $bill = new ItemBill;
                $bill->uuid = $cyclicalFee->uuid;
                $bill->item_id = $cyclicalFee->item_id;
                $bill->bill_type_id = $cyclicalFee->bill_type_id;
                $bill->payment_date = $paymentDate->getTimestamp();
                $bill->cost = $cyclicalFee->cost;
                $bill->recipient_name = $cyclicalFee->recipient_name;
                $bill->recipient_desciption = $cyclicalFee->recipient_desciption;
                $bill->recipient_bank_account = $cyclicalFee->recipient_bank_account;
                $bill->source_document_number = $cyclicalFee->source_document_number;
                $bill->source_document_date = $cyclicalFee->source_document_date;
                $bill->comments = $cyclicalFee->comments;
                $bill->save();
                
                //$balance = new \App\Libraries\Documents\Balance($bill);
                //$balance->charge();
                
                
                //$cyclicalFee->last_generated = time();
                //$cyclicalFee->saveQuietly();
            }
        }
    }
}
