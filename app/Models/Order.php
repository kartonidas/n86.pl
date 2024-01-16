<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Firm;
use App\Models\Invoice;
use App\Models\Subscription;

class Order extends Model
{
    public function getOrderAccountFirmData()
    {
        return Firm::where("uuid", $this->uuid)->first();
    }
    
    public function finish()
    {
        if($this->status == "new")
        {
            $this->status = "finished";
            $this->paid = date("Y-m-d H:i:s");
            $this->save();

            $invoice = false;
            switch($this->type) {
                case "subscription":
                    $subscription = Subscription::addPackageFromOrder($this);
                    $invoice = true;
                break;
            
                case "prolong":
                    $subscription = Subscription::prolongPackageFromOrder($this);
                    $invoice = true;                    
                break;
            
                case "extend":
                    $subscription = Subscription::extendPackageFromOrder($this);
                    $invoice = true;
                break;
            }
            
            if($invoice)
            {
                $items = [];
                $items[] = [
                    "name" => $this->name,
                    "amount" => $this->amount,
                    "vat" => $this->vat,
                    "gross" => $this->gross,
                    "qt" => 1,
                ];
                $invoiceId = Invoice::createInvoice($this);
                $this->invoice_id = $invoiceId;
                $this->save();
            }
        }
    }
}