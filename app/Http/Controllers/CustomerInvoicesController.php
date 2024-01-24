<?php

namespace App\Http\Controllers;

use Exception;
use App\Exceptions\ObjectNotExist;
use App\Exceptions\InvalidStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Http\Requests\CustomerInvoicesRequest;
use App\Http\Requests\StoreCustomerCorrectionRequest;
use App\Http\Requests\StoreCustomerInvoicesFromProformaRequest;
use App\Http\Requests\StoreCustomerInvoicesRequest;
use App\Http\Requests\UpdateCustomerCorrectionRequest;
use App\Http\Requests\UpdateCustomerInvoiceDataRequest;
use App\Http\Requests\UpdateCustomerInvoicesRequest;
use App\Libraries\CustomerInvoicePrinter;
use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerInvoiceItem;
use App\Models\FirmInvoicingData;
use App\Models\SaleRegister;
use App\Models\User;
use App\Traits\Sortable;

class CustomerInvoicesController extends Controller
{
    use Sortable;
    
    public function list(CustomerInvoicesRequest $request, $type = null)
    {
        User::checkAccess("customer_invoices:list");
        
        $validated = $request->validated();
        
        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $userInvoices = CustomerInvoice::whereRaw("1=1");
        
        if(!empty($validated["search"]))
        {
            if(!empty($validated["search"]["type"]))
                $userInvoices->where("type", $validated["search"]["type"]);
            if(!empty($validated["search"]["number"]))
                $userInvoices->where("full_number", "LIKE", "%" . $validated["search"]["number"] . "%");
            if(!empty($validated["search"]["customer_id"]))
                $userInvoices->where("customer_id", $validated["search"]["customer_id"]);
            if(!empty($validated["search"]["date_from"]))
                $userInvoices->where("document_date", ">=", $validated["search"]["date_from"]);
            if(!empty($validated["search"]["date_to"]))
                $userInvoices->where("document_date", "<=", $validated["search"]["date_to"]);
            if(!empty($validated["search"]["sale_register_id"]))
                $userInvoices->where("sale_register_id", $validated["search"]["sale_register_id"]);
            if(!empty($validated["search"]["created_user_id"]))
                $userInvoices->where("created_user_id", $validated["search"]["created_user_id"]);
        }
        
        $total = $userInvoices->count();
        
        $orderBy = $this->getOrderBy($request, CustomerInvoice::class, "document_date,desc");
        $userInvoices = $userInvoices->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
        
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $userInvoices,
        ];
            
        return $out;
    }

    public function create(StoreCustomerInvoicesRequest $request)
    {
        User::checkAccess("customer_invoices:create");
        
        $validated = $request->validated();
        
        $row = DB::transaction(function () use($validated) {
            $row = new CustomerInvoice;
            $row->firm_invoicing_data_id = CustomerInvoice::getCurrentFirmInvoicingDataId();
            $row->type = $validated["type"];
            $row->sale_register_id = $validated["sale_register_id"];
            $row->created_user_id = $validated["created_user_id"];
            $row->customer_id = $validated["customer_id"];
            $row->recipient_id = $validated["recipient_id"] ?? null;
            $row->payer_id = $validated["payer_id"] ?? null;
            $row->comment = $validated["comment"] ?? null;
            $row->document_date = $validated["document_date"];
            $row->sell_date = $validated["sell_date"];
            $row->payment_date = $validated["payment_date"];
            $row->payment_type_id = $validated["payment_type_id"];
            $row->account_number = $validated["account_number"] ?? null;
            $row->swift_number = $validated["swift_number"] ?? null;
            $row->language = $validated["language"];
            $row->currency = $validated["currency"];
            $row->save();
    
            $row->addItems($validated["items"]);
            
            return $row;
        });
        
        return $row->id;
    }
    
    public function get(Request $request, $id)
    {
        User::checkAccess("customer_invoices:list");
        
        $row = CustomerInvoice::find($id);
        if(!$row)
            throw new ObjectNotExist(__("Invoice does not exist"));
        
        $row->items = $row->items()->get();
        
        return $row;
    }

    public function update(UpdateCustomerInvoicesRequest $request, $id)
    {
        User::checkAccess("customer_invoices:update");

        $row = CustomerInvoice::find($id);
        if(!$row || $row->type == SaleRegister::TYPE_CORRECTION)
            throw new ObjectNotExist(__("Invoice does not exist"));

        if(!CustomerInvoice::checkOperation($row, "update"))
            return redirect()->route("panel.user_invoices.update", $id)->withErrors(["msg" => "Nie można edytować faktury"]);
        
        $validated = $request->validated();

        $row = DB::transaction(function () use($row, $validated) {
            $row->created_user_id = $validated["created_user_id"];
            $row->customer_id = $validated["customer_id"];
            $row->recipient_id = $validated["recipient_id"] ?? null;
            $row->payer_id = $validated["payer_id"] ?? null;
            $row->comment = $validated["comment"] ?? null;
            $row->document_date = $validated["document_date"];
            $row->sell_date = $validated["sell_date"];
            $row->payment_date = $validated["payment_date"];
            $row->payment_type_id = $validated["payment_type_id"];
            $row->account_number = $validated["account_number"] ?? null;
            $row->swift_number = $validated["swift_number"] ?? null;
            $row->language = $validated["language"];
            $row->currency = $validated["currency"];
            $row->save();
    
            $row->addItems($validated["items"]);
            
            return $row;
        });

        return true;
    }
    
    public function fromProforma(StoreCustomerInvoicesFromProformaRequest $request, $proformaId)
    {
        User::checkAccess("customer_invoices:create");
        
        $proforma = CustomerInvoice::find($proformaId);
        if(!$proforma || $proforma->type != SaleRegister::TYPE_PROFORMA)
            throw new ObjectNotExist(__("Proforma does not exist"));
        
        $validated = $request->validated();
        
        $row = DB::transaction(function () use($validated, $proforma) {
            $row = new CustomerInvoice;
            $row->type = SaleRegister::TYPE_INVOICE;
            $row->firm_invoicing_data_id = $proforma->firm_invoicing_data_id;
            $row->proforma_id = $proforma->id;
            $row->sale_register_id = $validated["sale_register_id"];
            $row->created_user_id = $validated["created_user_id"];
            $row->customer_id = $validated["customer_id"];
            $row->recipient_id = $validated["recipient_id"] ?? null;
            $row->payer_id = $validated["payer_id"] ?? null;
            $row->comment = $validated["comment"] ?? null;
            $row->document_date = $validated["document_date"];
            $row->sell_date = $validated["sell_date"];
            $row->payment_date = $validated["payment_date"];
            $row->payment_type_id = $validated["payment_type_id"];
            $row->account_number = $validated["account_number"] ?? null;
            $row->swift_number = $validated["swift_number"] ?? null;
            $row->language = $validated["language"];
            $row->currency = $validated["currency"];
            $row->save();
    
            $row->addItems($validated["items"]);
            
            return $row;
        });
        
        return $row->id;
    }

    public function correctionCreate(StoreCustomerCorrectionRequest $request, $invoiceId)
    {
        User::checkAccess("customer_invoices:create");

        $invoice = CustomerInvoice::find($invoiceId);
        if(!$invoice)
            throw new ObjectNotExist(__("Invoice to correction does not exist"));
            
        if(!$invoice->canMakeCorrection())
            throw new ObjectNotExist(__("Cannot correction selected invoice"));
        
        $validated = $request->validated();

        $row = DB::transaction(function () use($validated, $invoice) {
            $row = new CustomerInvoice;
            $row->firm_invoicing_data_id = CustomerInvoice::getCurrentFirmInvoicingDataId();
            $row->type = SaleRegister::TYPE_CORRECTION;
            $row->sale_register_id = $validated["sale_register_id"];
            $row->created_user_id = $validated["created_user_id"];
            $row->customer_id = $invoice->customer_id;
            $row->recipient_id = $invoice->recipient_id;
            $row->payer_id = $invoice->payer_id;
            $row->comment = $validated["comment"] ?? null;
            $row->document_date = $validated["document_date"];
            $row->sell_date = $validated["sell_date"];
            $row->payment_date = $validated["payment_date"];
            $row->payment_type_id = $invoice->payment_type_id;
            $row->account_number = $validated["account_number"] ?? null;
            $row->swift_number = $validated["swift_number"] ?? null;
            $row->language = $invoice->language;
            $row->currency = $invoice->currency;
            $row->save();
    
            $invoice->correction_id = $row->id;
            $invoice->save();
    
            $row->addItems($validated["items"], true);
            
            return $row;
        });
        
        return $row->id;
    }

    public function correctionUpdate(UpdateCustomerCorrectionRequest $request, $correctionId)
    {
        User::checkAccess("customer_invoices:update");

        $row = CustomerInvoice::find($correctionId);
        if(!$row || $row->type != SaleRegister::TYPE_CORRECTION)
            throw new ObjectNotExist(__("Correction does not exist"));

        $validated = $request->validated();
            
        $row = DB::transaction(function () use($validated, $row) {
            $row->created_user_id = $validated["created_user_id"];
            $row->comment = $validated["comment"] ?? null;
            $row->document_date = $validated["document_date"];
            $row->sell_date = $validated["sell_date"];
            $row->payment_date = $validated["payment_date"];
            $row->account_number = $validated["account_number"] ?? null;
            $row->save();
            
            $row->addItems($validated["items"]);
    
            return $row;
        });
        
        return true;
    }

    public function delete(Request $request, $id)
    {
        User::checkAccess("customer_invoices:delete");

        $row = CustomerInvoice::find($id);
        if(!$row)
            throw new ObjectNotExist(__("Invoice does not exist"));

        if(!$row->canDelete())
            throw new InvalidStatus(__("Cannot delete invoice"));

        $row->delete();
        
        return true;
    }

    public function getPdf(Request $request, $id)
    {
        User::checkAccess("customer_invoices:list");

        $row = CustomerInvoice::find($id);
        if(!$row)
            throw new ObjectNotExist(__("Invoice does not exist"));

        CustomerInvoicePrinter::generatePDF($row);
    }
    
    public function customerInvoiceData(Request $request)
    {
        return FirmInvoicingData::customerInvoice()->first();
    }
    
    public function customerInvoiceDataUpdate(UpdateCustomerInvoiceDataRequest $request)
    {
        User::checkAccess("customer_invoices:update");
        
        $invoicingData = FirmInvoicingData::customerInvoice()->first();
        if(!$invoicingData)
        {
            $invoicingData = new FirmInvoicingData;
            $invoicingData->object = FirmInvoicingData::OBJECT_CUSTOMER_INVOICE;
        }
        
        $validated = $request->validated();
        foreach($validated as $field => $value)
            $invoicingData->{$field} = $value;
        
        if($invoicingData->isDirty())
        {
            if($invoicingData->id > 0)
            {
                $invoicingData->replicate()->save();
                $invoicingData->delete();
            }
            else
                $invoicingData->save();
        }
        
        return true;
    }
}
