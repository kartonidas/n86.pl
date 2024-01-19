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
use App\Libraries\Helper;
use App\Models\BalanceDocument;
use App\Models\Item;
use App\Models\Rental;
use App\Models\User;


class ReportController extends Controller
{
    /*
     * Można wprowadzić dwa tryby:
     * - przekazujemy $year = pokazujemy statystyki dla podanego roku (tak jest teraz)
     * - przekazujemy 'last_year' = pokazujemy statystykę dla ostatnich 12 miesięcy
     */
    public function chartItemData(ReportChartRequest $request, $id)
    {
        $item = Item::find($id);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $preparedData = $this->prepareData($request->validated());
        $report = $preparedData["report"];
        $balanceDocuments = BalanceDocument
            ::where("item_id", $item->id)
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
                
            $report[date("Y-m", $balanceDocument->time)]["balance"] += $balanceDocument->amount;
        }
        
        $firstBalanceDocument = BalanceDocument::where("item_id", $item->id)->orderBy("time", "ASC")->first();
        if($firstBalanceDocument)
        {
            $allowedYears = [];
            $firstYear = date("Y", $firstBalanceDocument->time);
            while($firstYear <= date("Y"))
                $allowedYears[] = intval($firstYear++);
        }
        
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
                "name" => __("Deposit"),
                "values" => [],
            ],
            "balance" => [
                "name" => __("Balance"),
                "values" => [],
            ],
        ];
        $sumValues = $chargeValues = $depositValues = [];
        foreach($report as $r)
        {
            $out["sum"]["values"][] = round($r["sum"], 2);
            $out["charge"]["values"][] = round($r["charge"], 2);
            $out["deposit"]["values"][] = round($r["deposit"], 2);
            $out["balance"]["values"][] = round($r["balance"], 2);
        }
        return $out;
    }
    
    public function chartRentalData(ReportChartRequest $request, $id)
    {
        $rental = Rental::find($id);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $balance = $rental->getBalanceRow();
        if(!$balance)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $preparedData = $this->prepareData($request->validated());
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
                "name" => __("Deposit"),
                "values" => [],
            ],
            "balance" => [
                "name" => __("Balance"),
                "values" => [],
            ],
        ];
        $sumValues = $chargeValues = $depositValues = [];
        foreach($report as $r)
        {
            $out["sum"]["values"][] = round($r["sum"], 2);
            $out["charge"]["values"][] = round($r["charge"], 2);
            $out["deposit"]["values"][] = round($r["deposit"], 2);
            $out["balance"]["values"][] = round($r["balance"], 2);
        }
        return $out;
    }
    
    private function prepareData($params) : array
    {
        $period = "year";
        if(empty($params["year"]) && empty($params["period"]))
            $year = date("Y");
        else
        {
            if(!empty($params["year"]))
                $year = $params["year"];
            if(!empty($params["period"]))
                $period = $params["period"];
        }
        
        if($period == "year")
        {
            $start = (new DateTime(date($year . "-01-01")))->setTime(00, 00, 00);
            $end = (new DateTime(date($year . "-12-t")))->setTime(23, 59, 59);          
        }
        else
        {
            $end = (new DateTime(date("Y-m-t")))->setTime(23, 59, 59);          
            
            $start = clone $end;
            $start = $start->sub(new DateInterval("P12M"))->add(new DateInterval("P1D"))->setTime(00, 00, 00);
        }
        
        
        $report = [];
        $months = 12;
        $startForEmptyReportArray = clone $start;
        while($months > 0)
        {
            $report[$startForEmptyReportArray->format("Y-m")] = [
                "sum" => 0,
                "deposit" => 0,
                "charge" => 0,
                "balance" => 0,
            ];
            $startForEmptyReportArray->add(new DateInterval("P1M"));
            $months--;
        }
        
        return [
            "start" => $start,
            "end" => $end,
            "period" => $period,
            "year" => $year ?? null,
            "report" => $report,
        ];
    }
}