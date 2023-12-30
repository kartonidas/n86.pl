<?php

namespace App\Console\Commands;

use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Libraries\Helper;
use App\Models\Firm;
use App\Models\Item;
use App\Models\ItemCyclicalFee;
use App\Models\ItemBill;
use App\Models\User;

class GenerateCyclicalFees extends Command
{
    protected $signature = 'app:generate-cyclical-fees {--force : Ignore of the day of the month} {--month=current_month : Generate for specified month (format YYYY-MM)}';

    protected $description = 'Generate cyclical fees';

    public function handle(): void
    {
        $force = (bool)$this->option("force");
        if(!$force && intval(date("d")) != 1)
        {
            $this->error("Can only run on the first day of the month!");
        }
        else
        {
            if(!$this->option("month") || $this->option("month") == "current_month")
                $time = Helper::setDateTime(date("Y-m-01"), "00:00:00", true);
            else
            {
                if(!$this->checkMonthOption())
                    $this->error("Invalid month format!");
                else
                    $time = Helper::setDateTime($this->option("month") . "-01", "00:00:00", true);
            }
            
            if($time > time())
                $this->error("You cannot generate future receivables!");
            else
            {
                $cyclicalFees = ItemCyclicalFee
                    ::where(function($query) use($time) {
                        $query
                            ->whereNull("last")
                            ->orWhereRaw("DATE_ADD(FROM_UNIXTIME(last), INTERVAL repeat_months MONTH) <= ?", $time);
                    })
                    ->where("created_at", "<=", date("Y-m-d H:i:s", $time))
                    ->withoutGlobalScopes()
                    ->get();
                
                if(!$cyclicalFees->isEmpty())
                {
                    foreach($cyclicalFees as $cyclicalFee)
                    {
                        $item = Item::withoutGlobalScopes()->find($cyclicalFee->item_id);
                        if(!$item)
                        {
                            // Być może trzeba usunąć ten wpis skoro nie istnieje nieruchomość?
                            continue;
                        }
                        
                        $paymentDate = (new DateTime())->setTimestamp($time)->setTime(23, 59, 59);
                        $paymentDate->setDate(date("Y"), date("m"), $cyclicalFee->payment_day);
                        
                        $rental = null;
                        if($cyclicalFee->tenant_cost)
                            $rental = $item->getCurrentRental();
                        
                        DB::transaction(function () use($paymentDate, $cyclicalFee, $rental) {
                            $bill = new ItemBill;
                            $bill->uuid = $cyclicalFee->uuid;
                            $bill->item_id = $cyclicalFee->item_id;
                            $bill->rental_id = $rental ? $rental->id : 0;
                            $bill->bill_type_id = $cyclicalFee->bill_type_id;
                            $bill->payment_date = $paymentDate->getTimestamp();
                            $bill->cost = $cyclicalFee->cost;
                            $bill->recipient_name = $cyclicalFee->recipient_name;
                            $bill->recipient_desciption = $cyclicalFee->recipient_desciption;
                            $bill->recipient_bank_account = $cyclicalFee->recipient_bank_account;
                            $bill->source_document_number = $cyclicalFee->source_document_number;
                            $bill->source_document_date = $cyclicalFee->source_document_date;
                            $bill->comments = $cyclicalFee->comments;
                            $bill->cyclical = 1;
                            $bill->save();
                        
                            $cyclicalFee->last = time();
                            $cyclicalFee->saveQuietly();
                        });
                    }
                }
            }
        }
    }
    
    private function checkMonthOption()
    {
        preg_match("/^([0-9]{4})-([0-9]{2})$/", $this->option("month"), $match);
        if(empty($match))
            return false;
        else
        {
            if(intval($match[2]) < 1 || intval($match[2]) > 12)
                return false;
        }
        
        return true;
    }
}
