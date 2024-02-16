<?php

namespace App\Http\Controllers;

use DateTime;
use DateInterval;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Exceptions\ObjectNotExist;
use App\Http\Requests\ReportChartRequest;
use App\Libraries\Data;
use App\Libraries\Helper;
use App\Models\Balance;
use App\Models\BalanceDocument;
use App\Models\Item;
use App\Models\ItemBill;
use App\Models\Rental;
use App\Models\User;


class ReportController extends Controller
{
    public function item($id)
    {
        $item = Item::find($id);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $itemBalanceId = null;
        $itemBalance = Balance::where("item_id", $item->id)->where("rental_id", 0)->first();
        if($itemBalance)
            $itemBalanceId = $itemBalance->id;
        
        $report = [
            "total_rent" => 0,
            "total_fees" => 0,
            "profit" => 0,
        ];
        
        $rentBillTypeId = Data::getSystemBillTypes()["rent"][0];
        $report["total_rent"] = ItemBill
            ::where("item_id", $id)
            ->where("paid", 1)
            ->where("bill_type_id", $rentBillTypeId)
            ->sum("cost");
            
        $report["total_fees"] = ItemBill
            ::where("item_id", $id)
            ->where("rental_id", 0)
            ->where("paid", 1)
            ->sum("cost");
        
        $report["profit"] = $report["total_rent"] - $report["total_fees"];
        
        foreach($report as $k => $r)
            $report[$k] = round($r, 2);
        
        $report["currency"] = $item->currency;
        
        return $report;
    }
    
    /*
     * Można wprowadzić dwa tryby:
     * - przekazujemy $year = pokazujemy statystyki dla podanego roku (tak jest teraz)
     * - przekazujemy 'last_year' = pokazujemy statystykę dla ostatnich 12 miesięcy
     */
    public function balanceItemData(ReportChartRequest $request, $id)
    {
        $item = Item::find($id);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $preparedData = $this->prepareData($request->validated(), ["sum" => 0, "deposit" => 0, "charge" => 0, "balance" => 0]);
        $report = $preparedData["report"];
        
        /*
         * Wszystkie przyjęte wpłaty pobieramy i grupujemy zgodnie z datą wpłaty (paid_date).
         * Wpłaty pobierane są z balance_documents
         */
        $deposits = BalanceDocument
            ::where("item_id", $item->id)
            ->where("object_type", BalanceDocument::OBJECT_TYPE_DEPOSIT)
            ->where("paid_date", ">=", $preparedData["start"]->getTimestamp())
            ->where("paid_date", "<=", $preparedData["end"]->getTimestamp())
            ->orderBy("paid_date", "ASC")
            ->get();
            
        foreach($deposits as $deposit)
        {
            $report[date("Y-m", $deposit->paid_date)]["deposit"] += $deposit->amount;
            $report[date("Y-m", $deposit->paid_date)]["balance"] += $deposit->amount;
        }
        
        // Rachunki pobieramy po terminie płatności
        /*
         * Wszystkie wygenerowane rachunki pobieramy i grupujemy zgodnie z terminem płatności (payment_date)
         * Rachunki pobieramy z item_bills
         */
        $bills = ItemBill
            ::where("item_id", $item->id)
            ->where("payment_date", ">=", $preparedData["start"]->getTimestamp())
            ->where("payment_date", "<=", $preparedData["end"]->getTimestamp())
            ->orderBy("payment_date", "ASC")
            ->get();
            
        foreach($bills as $bill)
        {
            $report[date("Y-m", $bill->payment_date)]["charge"] += abs($bill->cost);
            $report[date("Y-m", $bill->payment_date)]["balance"] += (-$bill->cost);
        }
        
        $firstBalanceDocument = BalanceDocument::where("item_id", $item->id)->orderBy("time", "ASC")->first();
        if($firstBalanceDocument)
        {
            $allowedYears = [];
            $firstYear = date("Y", $firstBalanceDocument->time);
            while($firstYear <= date("Y"))
                $allowedYears[] = intval($firstYear++);
        }
        rsort($allowedYears);
        
        $out = [
            "years" => empty($allowedYears) ? [intval(date("Y"))] : $allowedYears,
            "labels" => array_keys($report),
            "charge" => [
                "name" => __("Charge"),
                "values" => [],
            ],
            "deposit" => [
                "name" => __("Payment"),
                "values" => [],
            ],
            "balance" => [
                "name" => __("Balance"),
                "values" => [],
            ],
        ];
        
        $out["summary"] = [
            "charge" => 0,
            "deposit" => 0,
            "balance" => 0,
        ];
        foreach($report as $r)
        {
            $out["charge"]["values"][] = round($r["charge"], 2);
            $out["deposit"]["values"][] = round($r["deposit"], 2);
            $out["balance"]["values"][] = round($r["balance"], 2);
            
            $out["summary"]["charge"] += round($r["charge"], 2);
            $out["summary"]["deposit"] += round($r["deposit"], 2);
            $out["summary"]["balance"] += round($r["balance"], 2);
        }
        
        return $out;
    }
    
    public function profitItemData(ReportChartRequest $request, $id)
    {
        $item = Item::find($id);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $columns = [
            "profit" => 0,
            "fee_paid" => 0,
            "fee_unpaid" => 0,
            "rent_paid" => 0,
            "rent_unpaid" => 0,
        ];
        $preparedData = $this->prepareData($request->validated(), $columns);
        $report = $preparedData["report"];
        
        $rentBillTypeId = Data::getSystemBillTypes()["rent"][0];
        $bills = ItemBill
            ::where("item_id", $id)
            ->where(function($q) use($rentBillTypeId) {
                $q
                    ->where("bill_type_id", $rentBillTypeId)
                    ->orWhere("rental_id", 0);
            })
            ->where("payment_date", ">=", $preparedData["start"]->getTimestamp())
            ->where("payment_date", "<=", $preparedData["end"]->getTimestamp())
            ->orderBy("payment_date", "ASC")
            ->get();
        
        foreach($bills as $bill)
        {
            if($bill->bill_type_id == $rentBillTypeId)
                $report[date("Y-m", $bill->payment_date)]["rent_" . ($bill->paid ? "paid" : "unpaid")] += abs($bill->cost);
            else
                $report[date("Y-m", $bill->payment_date)]["fee_" . ($bill->paid ? "paid" : "unpaid")] += abs($bill->cost);
        }
        
        $firstBill = ItemBill
            ::where("item_id", $id)
            ->where(function($q) use($rentBillTypeId) {
                $q
                    ->where("bill_type_id", $rentBillTypeId)
                    ->orWhere("rental_id", 0);
            })
            ->orderBy("payment_date", "ASC")
            ->first();
        if($firstBill)
        {
            $allowedYears = [];
            $firstYear = date("Y", $firstBill->paid_date);
            while($firstYear <= date("Y"))
                $allowedYears[] = intval($firstYear++);
        }
        rsort($allowedYears);

        $out = [
            "years" => empty($allowedYears) ? [intval(date("Y"))] : $allowedYears,
            "labels" => array_keys($report),
            "title" => $preparedData["title"],
            "fee_paid" => [
                "name" => __("Paid fees"),
                "values" => [],
            ],
            "fee_unpaid" => [
                "name" => __("Unpaid fees"),
                "values" => [],
            ],
            "rent_paid" => [
                "name" => __("Paid rent"),
                "values" => [],
            ],
            "rent_unpaid" => [
                "name" => __("Unpaid rent"),
                "values" => [],
            ],
            "profit" => [
                "name" => __("Profit"),
                "values" => [],
            ],
            "expected_profit" => [
                "name" => __("Expected profit"),
                "values" => [],
            ],
        ];

        $out["summary"] = [
            "profit" => 0,
            "minus" => 0,
            "plus" => 0,
            "currency" => $item->currency,
        ];
        
        
        foreach($report as $r)
        {
            $out["fee_paid"]["values"][] = round($r["fee_paid"], 2);
            $out["fee_unpaid"]["values"][] = round($r["fee_unpaid"], 2);
            $out["rent_paid"]["values"][] = round($r["rent_paid"], 2);
            $out["rent_unpaid"]["values"][] = round($r["rent_unpaid"], 2);
            $out["profit"]["values"][] = round($r["rent_paid"]-$r["fee_paid"], 2);
            $out["expected_profit"]["values"][] = round(($r["rent_paid"]+$r["rent_unpaid"])-($r["fee_paid"]+$r["fee_unpaid"]), 2);
            
            $out["summary"]["plus"] += round($r["rent_paid"], 2);
            $out["summary"]["minus"] += round($r["fee_paid"], 2);
            $out["summary"]["profit"] += round($r["rent_paid"]-$r["fee_paid"], 2);
        }
        
        return $out;
    }
    
    public function balanceRentalData(ReportChartRequest $request, $id)
    {
        $rental = Rental::find($id);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $balance = $rental->getBalanceRow();
        if(!$balance)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $preparedData = $this->prepareData($request->validated(), ["sum" => 0, "deposit" => 0, "charge" => 0, "balance" => 0]);
        $report = $preparedData["report"];
        $balanceDocuments = BalanceDocument
            ::where("item_id", $rental->item_id)
            ->where("balance_id", $balance->id)
            ->where("time", ">=", $preparedData["start"]->getTimestamp())
            ->where("time", "<=", $preparedData["end"]->getTimestamp())
            ->orderBy("time", "ASC")
            ->get();
            
        foreach($balanceDocuments as $balanceDocument)
        {
            $report[date("Y-m", $balanceDocument->time)]["sum"] += abs($balanceDocument->amount);
            
            if($balanceDocument->object_type == BalanceDocument::OBJECT_TYPE_BILL)
                $report[date("Y-m", $balanceDocument->time)]["charge"] += abs($balanceDocument->amount);
                
            if($balanceDocument->object_type == BalanceDocument::OBJECT_TYPE_DEPOSIT)
                $report[date("Y-m", $balanceDocument->time)]["deposit"] += abs($balanceDocument->amount);
        }
        
        $firstBalanceDocument = BalanceDocument::where("item_id", $item->id)->orderBy("time", "ASC")->first();
        if($firstBalanceDocument)
        {
            $allowedYears = [];
            $firstYear = date("Y", $firstBalanceDocument->time);
            while($firstYear <= date("Y"))
                $allowedYears[] = intval($firstYear++);
        }
        rsort($allowedYears);
        
        $out = [
            "years" => empty($allowedYears) ? [intval(date("Y"))] : $allowedYears,
            "labels" => array_keys($report),
            "sum" => [
                "name" => __("Sum"),
                "values" => [],
            ],
            "charge" => [
                "name" => __("Charge"),
                "values" => [],
            ],
            "deposit" => [
                "name" => __("Payment"),
                "values" => [],
            ],
            "balance" => [
                "name" => __("Balance"),
                "values" => [],
            ],
        ];

        foreach($report as $r)
        {
            $out["sum"]["values"][] = round($r["sum"], 2);
            $out["charge"]["values"][] = round($r["charge"], 2);
            $out["deposit"]["values"][] = round($r["deposit"], 2);
            $out["balance"]["values"][] = round($r["balance"], 2);
        }
        return $out;
    }
    
    public function profitRentalData(ReportChartRequest $request, $id)
    {
        $rental = Rental::find($id);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $balance = $rental->getBalanceRow();
        if(!$balance)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $preparedData = $this->prepareData($request->validated(), ["profit" => 0, "minus" => 0, "plus" => 0]);
        $report = $preparedData["report"];
        
        $rentBillTypeId = Data::getSystemBillTypes()["rent"][0];
        
        // Pobieramy wszystkie czynsze
        $rentBills = ItemBill
            ::where("rental_id", $id)
            ->where("bill_type_id", $rentBillTypeId)
            ->where("payment_date", ">=", $preparedData["start"]->getTimestamp())
            ->where("payment_date", "<=", $preparedData["end"]->getTimestamp())
            ->orderBy("created_at", "ASC")
            ->get();
            
        foreach($rentBills as $bill)
            $report[date("Y-m", $bill->payment_date)]["plus"] += abs($bill->cost);
            
        // Pobieramy wszystkie opaty nie przypisane do najemcy (z okresu kiedy trwał wynajem)
        $rentalStart = $rental->start;
        if($rental->termination)
            $rentalEnd = $rental->termination_time;
        else
            $rentalEnd = $rental->period != Rental::PERIOD_INDETERMINATE ? $rental->end : null;
                
        $costBills = ItemBill
            ::where("item_id", $rental->item_id)
            ->where("rental_id", 0)
            ->where("bill_type_id", "!=", $rentBillTypeId)
            ->where("payment_date", ">=", $preparedData["start"]->getTimestamp())
            ->where("payment_date", "<=", $preparedData["end"]->getTimestamp())
            ->where("created_at", ">=", $rentalStart)
            ->where("created_at", "<=", $rentalEnd)
            ->orderBy("created_at", "ASC")
            ->get();
            
        foreach($costBills as $bill)
            $report[date("Y-m", $bill->payment_date)]["minus"] += abs($bill->cost);
        
        $out = [
            "years" => empty($allowedYears) ? [intval(date("Y"))] : $allowedYears,
            "labels" => array_keys($report),
            "minus" => [
                "name" => __("Charge"),
                "values" => [],
            ],
            "plus" => [
                "name" => __("Deposit"),
                "values" => [],
            ],
            "profit" => [
                "name" => __("Profit"),
                "values" => [],
            ],
        ];

        foreach($report as $r)
        {
            $out["minus"]["values"][] = round($r["minus"], 2);
            $out["plus"]["values"][] = round($r["plus"], 2);
            $out["profit"]["values"][] = round($r["plus"]-$r["minus"], 2);
        }
        
        return $out;
    }
    
    private function prepareData($params, array $columns) : array
    {
        $period = $params["period"] ?? date("Y");
        
        if(is_numeric($period))
        {
            $start = (new DateTime(date($period . "-01-01")))->setTime(00, 00, 00);
            $end = (new DateTime(date($period . "-12-t")))->setTime(23, 59, 59);          
        }
        else
        {
            $end = (new DateTime(date("Y-m-t")))->setTime(23, 59, 59);          
            
            $start = clone $end;
            $start = $start->sub(new DateInterval("P12M"))->add(new DateInterval("P1D"))->setTime(00, 00, 00);
            $start->setDate($start->format("Y"), $start->format("m"), "01");
        }
        
        $report = [];
        $months = 12;
        $startForEmptyReportArray = clone $start;
        while($months > 0)
        {
            $report[$startForEmptyReportArray->format("Y-m")] = $columns;
            $startForEmptyReportArray->add(new DateInterval("P1M"));
            $months--;
        }
        
        return [
            "start" => $start,
            "end" => $end,
            "period" => $period,
            "report" => $report,
            "title" => is_numeric($period) ? (__("Summary of the year: ") . $period) : __("Summary of the last 12 months")
        ];
    }
}