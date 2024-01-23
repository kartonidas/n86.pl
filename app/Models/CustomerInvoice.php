<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\SaleRegister;
use App\Models\Customer;
use App\Models\CustomerInvoiceItem;
use App\Models\Settlement;
use App\Models\Numbering;
use App\Traits\NumberingTrait;

class CustomerInvoice extends Model
{
    use NumberingTrait;
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }

    protected $hidden = ["uuid"];
    
    const SOURCE_DIRECT = "direct";

    public function getMaskNumber()
    {
        $config = SaleRegister::find($this->sale_register_id);
        
        $out = [];
        $out["mask"] = $config->mask;
        $out["continuation"] = $config->continuation;
        return $out;
    }

    public function addItems($items = [], $force = false)
    {
        CustomerInvoiceItem::addItems($this, $items, $force);

        $summary = $this->calculateSummary();
        $this->net_amount = $summary["net"];
        $this->gross_amount = $summary["gross"];
        $this->net_amount_discount = $summary["net_discount"];
        $this->gross_amount_discount = $summary["gross_discount"];
        $this->saveQuietly();

        $this->recalculateBalance();
    }

    private function calculateSummary()
    {
        $summary = [
            "net" => 0,
            "gross" => 0,
            "net_discount" => 0,
            "gross_discount" => 0,
        ];

        $items = $this->getItems();
        if(!$items->isEmpty())
        {

            $usedIds = [-1];
            foreach($items as $item)
            {
                $quantity = $item->quantity;
                $discount = $item->discount;

                if(floatval($discount) > 0)
                {
                    $summary["net"] += $item->total_net_amount_discount;
                    $summary["gross"] += $item->total_gross_amount_discount;

                    $summary["net_discount"] += $item->total_net_amount - $item->total_net_amount_discount;
                    $summary["gross_discount"] += $item->total_gross_amount - $item->total_gross_amount_discount;
                }
                else
                {
                    $summary["net"] += $item->total_net_amount;
                    $summary["gross"] += $item->total_gross_amount;
                }
            }
        }
        return $summary;
    }

    public function canDelete()
    {
        switch($this->type)
        {
            // Jeśli do proformy została wystawiona faktura to nie pozalamy usunąc
            case "proforma":
                if(self::where("proforma_id", $this->id)->count())
                    return false;
            break;

            // Jeśli do faktury wystawiona jest korekta to nie możemy usnąc faktury źródłowej
            case "invoice":
                if($this->correction_id)
                    return false;
            break;
        }
        
        // Jeśli faktura ma przypisane wpłaty nie możemy usunąć faktury
        if(Settlement::where("document", "cash_register")->where("object", "invoice")->where("object_id", $this->id)->count())
            return false;

        return true;
    }

    public function canMakeFromProforma()
    {
        if($this->type == "proforma" && !self::where("proforma_id", $this->id)->count())
            return true;

        return false;
    }

    public function canMakeCorrection()
    {
        if($this->type == "invoice" && !$this->correction_id)
            return true;

        return false;
    }

    public function getProformaNumber()
    {
        if($this->proforma_id)
        {
            $proforma = self::find($this->proforma_id);
            if($proforma)
                return $proforma->full_number;
        }
        return "";
    }

    public function getCorrectionNumber()
    {
        if($this->correction_id)
        {
            $correction = self::find($this->correction_id);
            if($correction)
                return $correction->full_number;
        }
        return "";
    }

    private static $cachedData = [];
    public function prepareData()
    {
        if(empty(self::$cachedData["customer"][$this->customer_id]))
            self::$cachedData["customer"][$this->customer_id] = Customer::find($this->customer_id);

        if(empty(self::$cachedData["sale_register"][$this->sale_register_id]))
            self::$cachedData["sale_register"][$this->sale_register_id] = SaleRegister::find($this->sale_register_id);

        $recipientId = $this->customer_id != $this->recipient_id && $this->recipient_id ? $this->recipient_id : $this->customer_id;
        $payerId = $this->customer_id != $this->payer_id && $this->payer_id ? $this->payer_id : $this->customer_id;
        if(empty(self::$cachedData["recipient"][$recipientId]))
            self::$cachedData["recipient"][$recipientId] = Customer::find($recipientId);

        if(empty(self::$cachedData["payer"][$payerId]))
            self::$cachedData["payer"][$payerId] = Customer::find($payerId);

        $this->customer = self::$cachedData["customer"][$this->customer_id];
        $this->recipient = self::$cachedData["recipient"][$recipientId];
        $this->payer = self::$cachedData["payer"][$payerId];
        $this->sale_register = self::$cachedData["sale_register"][$this->sale_register_id];
    }

    public function getItems($array = false)
    {
        $items = CustomerInvoiceItem::where("customer_invoice_id", $this->id)->orderBy("created_at", "DESC")->get();

        if($array)
        {
            $out = [];
            foreach($items as $item)
            {
                $product = $item->getProduct();
                $item = $item->toArray();
                unset($item["account_uuid"]);
                unset($item["created_at"]);
                unset($item["update_at"]);

                if($product)
                    $item["product_type"] = $product->type;
                $out[$item["id"]] = $item;
            }
            return $out;
        }

        return $items;
    }
    
    public function getAssignedServiceTaskIds()
    {
        $ids = [];
        $items = $this->getItems();
        if(!$items->isEmpty())
        {
            foreach($items as $item)
            {
                if(!empty($item->service_task_id))
                    $ids[] = $item->service_task_id;
            }
        }
        $ids = array_unique($ids);
        return $ids;
    }

    public static function getAssignedServiceTaskRows($ids)
    {
        if(empty($ids))
            $ids = [-1];
        return ServiceTask::whereIn("id", $ids)->get();
    }

    public function getGroupedItems()
    {
        $out = [];
        $items = $this->getItems(true);

        foreach($items as $item)
        {
            if(empty($out[$item["vat_rate_id"]]))
            {
                $out[$item["vat_rate_id"]] = [
                    "vat_rate_id" => $item["vat_rate_id"],
                    "vat_rate_value" => $item["vat_rate_value"],
                    "net_amount" => 0,
                    "gross_amount" => 0,
                    "gross_value" => 0,
                ];
            }

            $netAmount = $item["total_net_amount"];
            $grossAmount = $item["total_gross_amount"];
            if($item["discount"] > 0)
            {
                $netAmount = $item["total_net_amount_discount"];
                $grossAmount = $item["total_gross_amount_discount"];
            }

            $out[$item["vat_rate_id"]]["net_amount"] += $netAmount;
            $out[$item["vat_rate_id"]]["gross_amount"] += $grossAmount;
        }

        foreach($out as $k => $item)
            $out[$k]["gross_value"] = $item["gross_amount"] - $item["net_amount"];

        uasort($out, function($a, $b) {
            if($a["vat_rate_value"] == $b["vat_rate_value"])
                return 0;
            return (intval($a["vat_rate_value"]) > intval($b["vat_rate_value"])) ? -1 : 1;
        });

        return $out;
    }

    public function delete()
    {
        if($this->canDelete())
        {
            return DB::transaction(function () {
                CustomerInvoiceItem::where("customer_invoice_id", $this->id)->delete();
                Settlement::where("object", "invoice")->where("object_id", $this->id)->delete();
                Numbering::where("type", "invoice")->where("object_id", $this->id)->delete();
    
                if($this->type == "correction")
                {
                    $invoices = self::where("correction_id", $this->id)->get();
                    if(!$invoices->isEmpty())
                    {
                        foreach($invoices as $invoice)
                        {
                            $invoice->correction_id = NULL;
                            $invoice->save();
                        }
                    }
                }
                
                return parent::delete();
            });
        }
    }

    public function getCorrectedInvoice()
    {
        $row = [];
        if($this->type == "correction")
        {
            $invoice = self::where("type", "invoice")->where("correction_id", $this->id)->first();
            if($invoice)
            {
                $row = $invoice->toArray();

                unset($row["id"]);
                unset($row["created_at"]);
                unset($row["updated_at"]);
                unset($row["proforma_id"]);
                unset($row["correction_id"]);

                $row["type"] = $this->type;
                $row["number"] = $this->number;
                $row["full_number"] = $this->full_number;
                $row["sale_register_id"] = $this->sale_register_id;
                $row["created_user_id"] = $this->created_user_id;
                $row["comment"] = $this->comment;
                $row["document_date"] = $this->document_date;
                $row["sell_date"] = $this->sell_date;
                $row["payment_date"] = $this->payment_date;
                $row["account_number"] = $this->account_number;

                $row["net_amount"] = $this->net_amount;
                $row["gross_amount"] = $this->gross_amount;
                $row["net_amount_discount"] = $this->net_amount_discount;
                $row["gross_amount_discount"] = $this->gross_amount_discount;
                $row["balance"] = $this->balance;
                $row["total_payments"] = $this->total_payments;
                $row["balance_correction"] = $this->balance_correction;
            }
        }
        return $row;
    }

    public function getCorrectionSource()
    {
        if($this->type == "correction")
        {
            $invoice = self::where("type", "invoice")->where("correction_id", $this->id)->first();
            if($invoice)
                return $invoice;
        }
        throw new \Exception("Faktura nie istnieje");
    }

    public static function getInvoiceNextNumber($saleRegisterId)
    {
        $currentYear = date("Y");
        $currentMonth = date("m");

        $config = SaleRegister::find($saleRegisterId);

        $maskConfig = [
            "mask" => $config->mask,
            "continuation" => $config->continuation,
        ];
        $fullNumber = $maskConfig["mask"];

        $lastNumberQuery = Numbering::where("type", "invoice")->where("invoice_sale_register_id", $saleRegisterId);
        switch($maskConfig["continuation"])
        {
            case "month":
                $lastNumberQuery->where("date", $currentYear . "-" . $currentMonth);
            break;
            case "year":
                $lastNumberQuery->whereRaw("SUBSTRING(date, 1, 4) = ?", $currentYear);
            break;
        }

        $number = $lastNumberQuery->max("number") + 1;

        preg_match("/@N([1-9]+)?/i", $maskConfig["mask"], $matches);
        if($matches)
            $fullNumber = str_replace($matches[0], !empty($matches[1]) ? str_pad($number, $matches[1], "0", STR_PAD_LEFT) : $number, $fullNumber);

        $fullNumber = str_ireplace("@M", $currentMonth, $fullNumber);
        $fullNumber = str_ireplace("@Y", $currentYear, $fullNumber);

        return $fullNumber;
    }

    public function recalculateBalance()
    {
        // jesli wpłaty sa wieksze od należności oznacznym fakture jako oplaconą

        $total_payments = Settlement::where("object", "invoice")->where("object_id", $this->id)->sum("amount");

        $grossAmount = $this->gross_amount;

        if($this->type == "correction")
        {
             $correctedInvoice = $this->getCorrectionSource();
             if($correctedInvoice)
             {
                 $grossAmountDiff = $this->gross_amount - $correctedInvoice->gross_amount;
                 if($grossAmountDiff < 0)
                 {
                     $paymentRow = Settlement::where("object", "invoice")->where("object_id", $correctedInvoice->id)->where("correction_id", $this->id)->first();
                     if(!$paymentRow)
                     {
                         $paymentRow = new Settlement;
                         $paymentRow->object = "invoice";
                         $paymentRow->object_id = $correctedInvoice->id;
                         $paymentRow->payment_date = date("Y-m-d H:i:s");
                         $paymentRow->correction_id = $this->id;
                     }
                     $paymentRow->amount = abs($grossAmountDiff);
                     $paymentRow->saveQuietly();

                     $correctedInvoice->recalculateBalance();
                 }
                 else
                 {
                     $paymentRow = Settlement::where("object", "invoice")->where("object_id", $correctedInvoice->id)->where("correction_id", $this->id)->first();
                     if($paymentRow)
                        $paymentRow->delete();
                 }
                 $this->balance = $grossAmountDiff;
                 $this->balance_correction = $grossAmountDiff - $total_payments;
             }
        }
        else
        {
            $balance = $grossAmount - $total_payments;
            $this->balance = $balance;
        }

        $this->total_payments = $total_payments;
        $this->saveQuietly();
    }

    public function isLastNumber()
    {
        $lastId = Numbering::where("type", "invoice")->where("invoice_sale_register_id", $this->sale_register_id)->max("id");
        $numberingRow = Numbering::select("id")->where("type", "invoice")->where("object_id", $this->id)->first();

        if($numberingRow->id != $lastId)
            return false;

        return true;
    }

    public function getPayments()
    {
        return Settlement::where("object", "invoice")->where("object_id", $this->id)->orderBy("payment_date", "DESC")->get();
    }

    public function getTotalPayments()
    {
        return Settlement::where("object", "invoice")->where("object_id", $this->id)->sum("amount");
    }

    public static function getAllowedOperations(CustomerInvoice $invoice = null)
    {
        $operations = [
            "update" => true,
            "payment:add" => true,
            "payment:update" => true,
            "payment:delete" => true,
            "item:add" => true,
            "item:update" => true,
            "item:delete" => true,
        ];

        if($invoice)
        {
            switch($invoice->type)
            {
                case "proforma":
                    if(self::where("proforma_id", $invoice->id)->count())
                    {
                        foreach($operations as $op => $state)
                            $operations[$op] = false;
                    }
                break;

                case "invoice":
                    if($invoice->correction_id)
                    {
                        $operations["update"] = false;
                        $operations["item:add"] = false;
                        $operations["item:update"] = false;
                        $operations["item:delete"] = false;
                    }
                break;

                case "correction":
                    $operations["update"] = false;
                    $operations["item:add"] = false;
                    $operations["item:update"] = true;
                    $operations["item:delete"] = false;
                break;
            }
        }
        return $operations;
    }

    public static function checkOperation(CustomerInvoice $invoice = null, $operation)
    {
        $allowed = self::getAllowedOperations($invoice);

        if(!empty($allowed[$operation]))
            return true;
        return false;
    }

    public function getCashRegisterSummary()
    {
        return [
            "payment" => Settlement::where("object", "invoice")->where("object_id", $this->id)->where("amount", ">", 0)->sum("amount"),
            "paycheck" => abs(Settlement::where("object", "invoice")->where("object_id", $this->id)->where("amount", "<", 0)->sum("amount")),
        ];
    }
}
