<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Libraries\Data;
use App\Libraries\Helper;
use App\Models\ItemBill;
use App\Models\Rental;

class GenerateRentalBills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-rental-bills {--force : Ignore of the day of the month} {--month=current_month : Generate for specified month (format YYYY-MM)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate rental cyclical bills';

    /**
     * Execute the console command.
     */
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
                $rentals = Rental
                    ::withoutGlobalScopes()
                    ->where("status", Rental::STATUS_CURRENT)
                    ->where("payment", Rental::PAYMENT_CYCLICAL)
                    ->get();
                    
                $firstDayOfMonth = $time;
                $lastDayOfMonth = strtotime(date("Y-m-t 23:59:59", $time));
                
                foreach($rentals as $rental)
                {
                    $item = $rental->item()->first();
                    if(!$item || $item->mode == Item::MODE_ARCHIVED)
                        continue;
                        
                    if($rental->next_rental && $firstDayOfMonth <= $rental->next_rental && $lastDayOfMonth >= $rental->next_rental)
                    {
                        $cost = $rental->rent;
                        if($rental->end && $rental->end <= $lastDayOfMonth && $rental->last_month_different_amount > 0)
                            $cost = $rental->last_month_different_amount;
                        
                        $rent = new ItemBill;
                        $rent->uuid = $rental->uuid;
                        $rent->item_id = $rental->item_id;
                        $rent->rental_id = $rental->id;
                        $rent->bill_type_id = Data::getSystemBillTypes()["rent"][0];
                        $rent->payment_date = $rental->next_rental;
                        $rent->cost = $cost;
                        $rent->save();
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
