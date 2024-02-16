<?php

namespace App\Console\Commands;

use DateTime;
use DateInterval;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Libraries\Helper;
use App\Models\Dictionary;
use App\Models\Firm;
use App\Models\Item;
use App\Models\ItemBill;
use App\Models\User;

class GenerateBillsForReports extends Command
{
    protected $signature = 'app:generate-sample-bills {item_id} {start} {end}';

    protected $description = 'Generate sample bills';

    public function handle(): void
    {
        try
        {
            $this->validateParameters();
            $months = $this->calculateMonths(new DateTime($this->argument("start")), new DateTime($this->argument("end")));
            
            $start = new DateTime($this->argument("start"));
            $end = new DateTime($this->argument("end"));
            
            $item = Item::withoutGlobalScopes()->find($this->argument("item_id"));
            if(!$item)
                throw new Exception("Item does not exists!");
        
            $rental = $item->getCurrentRental();
            if(!$rental)
                throw new Exception("Item does't have active rental!");
            
            $firm = Firm::where("uuid", $item->uuid)->first();
            if(!$firm)
                throw new Exception("Firm does not exists!");
            
            $user = User::where("firm_id", $firm->id)->where("owner", 1)->first();
            if(!$user)
                throw new Exception("User does not exists!");
            
            if(Auth::onceUsingId($user->id))
            {
                $this->removeOldBills($item);
                
                $dictionaryIds = Dictionary::where("type", "bills")->pluck("id")->all();
                
                for($i = 0; $i < $months; $i++)
                {
                    $paymentDate = new DateTime;
                    $paymentDate->setDate($start->format("Y"), $start->format("m"), "01");
                    if($paymentDate < (new DateTime($this->argument("start"))))
                        $paymentDate->setDate($start->format("Y"), $start->format("m"), $start->format("d"));
                    
                    $bill = new ItemBill;
                    $bill->uuid = $item->uuid;
                    $bill->item_id = $item->id;
                    $bill->rental_id = $rental->id;
                    $bill->bill_type_id = -1;
                    $bill->payment_date = $paymentDate->getTimestamp();
                    $bill->cost = $item->default_rent;
                    $bill->cyclical = 0;
                    $bill->save();
                    
                    $balance = $bill->getBalanceDocument();
                    $balance->time = $bill->payment_date;
                    $balance->saveQuietly();
                    
                    $addCost = rand(1, 3);
                    for($j = 0; $j <= $addCost; $j++)
                    {
                        $bill = new ItemBill;
                        $bill->uuid = $item->uuid;
                        $bill->item_id = $item->id;
                        $bill->rental_id = 0;
                        $bill->bill_type_id = $dictionaryIds[rand(0, count($dictionaryIds)-1)];
                        $bill->payment_date = $paymentDate->getTimestamp();
                        $bill->cost = rand(100, 300);
                        $bill->cyclical = 0;
                        $bill->save();
                        
                        $balance = $bill->getBalanceDocument();
                        $balance->time = $bill->payment_date;
                        $balance->saveQuietly();
                    }
                    
                    $addCost = rand(1, 3);
                    for($j = 0; $j <= $addCost; $j++)
                    {
                        $bill = new ItemBill;
                        $bill->uuid = $item->uuid;
                        $bill->item_id = $item->id;
                        $bill->rental_id = $rental->id;
                        $bill->bill_type_id = $dictionaryIds[rand(0, count($dictionaryIds)-1)];
                        $bill->payment_date = $paymentDate->getTimestamp();
                        $bill->cost = rand(100, 300);
                        $bill->cyclical = 0;
                        $bill->save();
                        
                        $balance = $bill->getBalanceDocument();
                        $balance->time = $bill->payment_date;
                        $balance->saveQuietly();
                    }
                    
                    $start->add(new DateInterval("P1M"));
                }
            }
        }
        catch(Exception $e)
        {
            $this->error($e->getMessage());
        }
    }
    
    private function validateParameters()
    {
        if(!Helper::_validateDate($this->argument("start")))
            throw new Exception("Invalid start date format!");
        
        if(!Helper::_validateDate($this->argument("end")))
            throw new Exception("Invalid end date format!");
        
        if(strtotime($this->argument("start")) >= strtotime($this->argument("end")))
            throw new Exception("End date must older than start!");
    }
    
    private function calculateMonths(DateTime $start, DateTime $end)
    {
        $months = 0;
        $start->setDate($start->format("Y"), $start->format("m"), "01");
        
        while(true)
        {
            if($start > $end)
                break;
            
            $months++;
            $start->add(new DateInterval("P1M"));
        }
        return $months;
    }
    
    private function removeOldBills(Item $item)
    {
        $bills = ItemBill::where("item_id", $item->id)->get();
        foreach($bills as $bill)
            $bill->delete();
    }
}
