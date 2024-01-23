<?php

namespace App\Http\Controllers;

use Exception;
use App\Exceptions\ObjectNotExist;
//use App\Exceptions\ExceptionArray;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Http\Requests\CustomerInvoicesRequest;
use App\Http\Requests\StoreCustomerInvoicesRequest;
use App\Http\Requests\UpdateCustomerInvoicesRequest;

use App\Models\Account;
//use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerInvoiceItem;
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
            $row->type = $validated["type"];
            $row->sale_register_id = $validated["sale_register_id"];
            $row->created_user_id = Auth::user()->id;
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
    
            $items = $row->addItems($validated["items"]);
            
            return $row;
        });
        
        return $row;
    }

    public function update(UpdateCustomerInvoicesRequest $request, $id)
    {
        User::checkAccess("customer_invoices:update");

        $row = CustomerInvoice::find($id);
        if(!$row || $row->type == SaleRegister::TYPE_CORRECTION)
            throw new ObjectNotExist(__("Sale register does not exist"));

        if(!CustomerInvoice::checkOperation($row, "update"))
            return redirect()->route("panel.user_invoices.update", $id)->withErrors(["msg" => "Nie można edytować faktury"]);
        
        $validated = $request->validated();

        $row = DB::transaction(function () use($row, $validated) {
            foreach($validated as $field => $value)
            {
                if(Schema::hasColumn($row->getTable(), $field))
                $row->{$field} = $value;
            }
            $row->save();
    
            //$row->addItems($request->input("invoice.items", []));
            
            return $row;
        });

        return true;
    }

    public function invoiceCreateFromProformaSave(Request $request, $id)
    {
        User::checkAccess("customer_invoices:update");
        \App\Libraries\PanelMenu::setActiveItem("sell.invoices");

        $proforma = CustomerInvoice::find($id);
        if(!$proforma || $proforma->type != "proforma")
            return redirect()->route("panel.user_invoices")->withErrors(["msg" => "Proforma nie istnieje"]);

        if(!$proforma->canMakeFromProforma())
            return redirect()->route("panel.user_invoices")->withErrors(["msg" => "Dla wybranej proformy istnieje już faktura"]);

        $validator = \Validator::make($request->all(), $this->getValidateRule($request, "proforma"));
        if($validator->fails()) {
            return redirect()
                        ->route("panel.user_invoices.create_from_proforma", $id)
                        ->withErrors($validator)
                        ->withInput();
        }
        
        try
        {
            $row = DB::transaction(function () use($proforma, $request) {
                $saleRegister = ConfigSaleRegister::find($request->input("invoice.sale_register_id"));
        
                $row = new UserInvoices;
                $row->proforma_id = $proforma->id;
                $row->type = $saleRegister->type;
                $row->sale_register_id = $request->input("invoice.sale_register_id");
                $row->created_user_id = $request->input("invoice.created_user_id");
                $row->customer_id = $request->input("invoice.customer_id");
                $row->recipient_id = $request->input("invoice.recipient_id");
                $row->payer_id = $request->input("invoice.payer_id");
                $row->comment = $request->input("invoice.comment");
                $row->document_date = $request->input("invoice.document_date");
                $row->sell_date = $request->input("invoice.sell_date");
                $row->payment_date = $request->input("invoice.payment_date");
                $row->payment_type_id = $request->input("invoice.payment_type_id");
                $row->account_number = $request->input("invoice.account_number");
                $row->swift_number = $request->input("invoice.swift_number");
                $row->language = $request->input("invoice.language");
                $row->currency = $request->input("invoice.currency");
                $row->save();
        
                $row->addItems($request->input("invoice.items", []));
                
                if(Account::isWarehouseActive())
                {
                    $document = WarehouseManager::createFromInvoice("wz", $row, $row->getItems());
                    if($document)
                        $row->associateWarehouseDocuments($document);
                }
        
                // Jeśli są jakieś wplaty rozliczamy fakturę proformoą
                $payments = $proforma->getPayments();
                if(!$payments->isEmpty())
                    \App\Libraries\Settlement\Manager::settlement($proforma, $row);
                    
                return $row;
            });
        }
        catch(Exception $e)
        {
            $errors = $e instanceof ExceptionArray ? $e->_getMessage() : $e->getMessage();
            return redirect()->back()->withInput()->withErrors(["msg" => $errors]);
        }
        
        Helper::setMessage("user-invoices", "Faktura została zapisana");
        if($this->isApply())
            return redirect()->route("panel.user_invoices.update", $row->id);
        else
            return redirect()->route("panel.user_invoices", "proforma");
    }

    
    /*
     * Jeżeli ilości w korekcie różnaą się od ilości z orig FV, to wystawiamy dokument
     * Dokument wystawiamy za każdym razem jak ilości się zmieniły
     *
     * Przy tworzeniu porównujemy tylko ilości z originalnej faktury z tymi nowymi wartościami,
     * jak jakieś się różną to wystawiamy dokument
     *
     * Przy edycji porównujemy już same zmiany (nie ptarzymy na originalne wartości),
     * jeśli jakiś produkt zmienił ilośc +/- to wystawiamy odpowiedni dokument.
     */
    public function invoiceCorrectionCreateSave(Request $request, $id)
    {
        User::checkAccess("customer_invoices:create");
        \App\Libraries\PanelMenu::setActiveItem("sell.invoices");

        $invoice = UserInvoices::find($id);
        if(!$invoice)
            return redirect()->route("panel.correction_create")->withErrors(["msg" => "Wybrana faktura nie istnieje"]);

        if(!$invoice->canMakeCorrection())
            return redirect()->route("panel.user_invoices.correction_create")->withErrors(["msg" => "Dla wybranej faktury została już wystawiona korekta"]);

        $validator = \Validator::make($request->all(), $this->getValidateRule($request, "correction.create"));
        if($validator->fails()) {
            return redirect()
                        ->route("panel.user_invoices.correction_create", $id)
                        ->withErrors($validator)
                        ->withInput();
        }

        try
        {
            $row = DB::transaction(function () use($request, $invoice) {
                $row = new UserInvoices;
                $row->type = "correction";
                $row->sale_register_id = $request->input("invoice.sale_register_id");
                $row->created_user_id = $request->input("invoice.created_user_id");
                $row->customer_id = $invoice->customer_id;
                $row->recipient_id = $invoice->recipient_id;
                $row->payer_id = $invoice->payer_id;
                $row->comment = $request->input("invoice.comment");
                $row->document_date = $request->input("invoice.document_date");
                $row->sell_date = $request->input("invoice.sell_date");
                $row->payment_date = $request->input("invoice.payment_date");
                $row->payment_type_id = $invoice->payment_type_id;
                $row->account_number = $request->input("invoice.account_number");
                $row->swift_number = $invoice->swift_number;
                $row->language = $invoice->language;
                $row->currency = $invoice->currency;
                $row->save();
        
                $invoice->correction_id = $row->id;
                $invoice->save();
        
                $row->addItems($request->input("invoice.items", []), true);
                
                if(Account::isWarehouseActive())
                {
                    $itemsToWarehouseDocuments = WarehouseManager::compareItemsToCorrection($invoice->getItems(), $row->getItems());
                    
                    if(!empty($itemsToWarehouseDocuments["wz"]))
                    {
                        $document = WarehouseManager::createFromInvoice("wz", $row, $itemsToWarehouseDocuments["wz"]);
                        if($document)
                            $row->associateWarehouseDocuments($document);
                    }
                    
                    if(!empty($itemsToWarehouseDocuments["pw"]))
                    {
                        $document = WarehouseManager::createFromInvoice("pw", $row, $itemsToWarehouseDocuments["pw"]);
                        if($document)
                            $row->associateWarehouseDocuments($document);
                    }
                }
                
                return $row;
            });
        }
        catch(Exception $e)
        {
            $errors = $e instanceof ExceptionArray ? $e->_getMessage() : $e->getMessage();
            return redirect()->back()->withInput()->withErrors(["msg" => $errors]);
        }

        Helper::setMessage("user-invoices", "Korekta została zapisana");
        if($this->isApply())
            return redirect()->route("panel.user_invoices.correction_update", $row->id);
        else
            return redirect()->route("panel.user_invoices", "correction");
    }

    public function invoiceCorrectionUpdateSave(Request $request, $id)
    {
        User::checkAccess("customer_invoices:update");
        \App\Libraries\PanelMenu::setActiveItem("sell.invoices");

        $row = UserInvoices::find($id);
        if(!$row || $row->type != "correction")
            return redirect()->route("panel.correction_create")->withErrors(["msg" => "Wybrana faktura nie istnieje"]);

        $validator = \Validator::make($request->all(), $this->getValidateRule($request, "correction.update"));
        if($validator->fails()) {
            return redirect()
                        ->route("panel.user_invoices.correction_update", $id)
                        ->withErrors($validator)
                        ->withInput();
        }

        try
        {
            $row = DB::transaction(function () use($request, $row) {
                $row->created_user_id = $request->input("invoice.created_user_id");
                $row->comment = $request->input("invoice.comment");
                $row->document_date = $request->input("invoice.document_date");
                $row->sell_date = $request->input("invoice.sell_date");
                $row->payment_date = $request->input("invoice.payment_date");
                $row->account_number = $request->input("invoice.account_number");
                $row->save();
                
                $oldItems = $row->getItems();
        
                $row->addItems($request->input("invoice.items", []));
        
                if(Account::isWarehouseActive())
                {
                    $itemsToWarehouseDocuments = WarehouseManager::compareItemsToCorrection($oldItems, $row->getItems());
                    
                    if(!empty($itemsToWarehouseDocuments["wz"]))
                    {
                        $document = WarehouseManager::createFromInvoice("wz", $row, $itemsToWarehouseDocuments["wz"]);
                        if($document)
                            $row->associateWarehouseDocuments($document);
                    }
                    
                    if(!empty($itemsToWarehouseDocuments["pw"]))
                    {
                        $document = WarehouseManager::createFromInvoice("pw", $row, $itemsToWarehouseDocuments["pw"]);
                        if($document)
                            $row->associateWarehouseDocuments($document);
                    }
                }
                
                return $row;
            });
        }
        catch(Exception $e)
        {
            $errors = $e instanceof ExceptionArray ? $e->_getMessage() : $e->getMessage();
            return redirect()->back()->withInput()->withErrors(["msg" => $errors]);
        }

        Helper::setMessage("user-invoices", "Korekta została zapisana");
        if($this->isApply())
            return redirect()->route("panel.user_invoices.correction_update", $row->id);
        else
            return redirect()->route("panel.user_invoices", $row->type);
    }

    public function delete(Request $request, $id)
    {
        User::checkAccess("customer_invoices:delete");

        $row = UserInvoices::find($id);
        if(!$row)
            return redirect()->route("panel.user_invoices")->withErrors(["msg" => "Faktura nie istnieje"]);

        $type = $row->type;

        if(!$row->canDelete())
            return redirect()->route("panel.user_invoices", $type)->withErrors(["msg" => "Nie można usunąć wybranej faktury"]);

        $row->delete();
        Helper::setMessage("product-services", "Faktura została usunięta");
        return redirect()->route("panel.user_invoices", $type);
    }

    public function getValidateRule(\Illuminate\Http\Request $request, $action = "create")
    {
        $rule = [];
        if(in_array($action, ["create", "proforma", "correction.create"]))
        {
            $saleRegisteiresIds = [];
            $saleRegisteires = ConfigSaleRegister::orderBy("name", "ASC");

            if($action == "create")
                $saleRegisteires->where("type", "!=", "correction");
            elseif($action == "correction.create")
                $saleRegisteires->where("type", "correction");
            else
                $saleRegisteires->where("type", "invoice");

            $saleRegisteires = $saleRegisteires->get();

            foreach($saleRegisteires as $row)
                $saleRegisteiresIds[] = $row->id;
            $rule["invoice.sale_register_id"] = ["required", Rule::in($saleRegisteiresIds)];
        }

        $rule["invoice.created_user_id"] = ["required", Rule::in(array_keys(User::getEmployees(true)))];

        if(!in_array($action, ["correction.create", "correction.update"]))
        {
            $rule["invoice.customer_id"] = ["required", new \App\Rules\Customer()];
            $rule["invoice.recipient_id"] = ["nullable", new \App\Rules\Customer()];
            $rule["invoice.payer_id"] = ["nullable", new \App\Rules\Customer()];
        }

        $rule["invoice.comment"] = ["nullable", "max:5000"];
        $rule["invoice.document_date"] = ["required", "date_format:Y-m-d"];
        $rule["invoice.sell_date"] = ["required", "date_format:Y-m-d"];
        $rule["invoice.payment_date"] = ["required", "date_format:Y-m-d"];

        if(!in_array($action, ["correction.create", "correction.update"]))
        {
            $rule["invoice.payment_type_id"] = ["required", Rule::in(array_keys(Dictionary::getDictionary("payment_type", true)))];
            $rule["invoice.language"] = ["required", Rule::in(array_keys(config("panel.invoice.languages")))];
            $rule["invoice.currency"] = ["required", Rule::in(config("panel.invoice.currencies"))];
        }

        $allowedUnitTypes = array_keys(Dictionary::getDictionary("unit_type", true));
        $allowedVatRates = array_keys(Dictionary::getDictionary("vat_rate", true));
        
        $rule["invoice.items.*.product_name"] = ["required"];
        $rule["invoice.items.*.quantity"] = ["required", new \App\Rules\Numeric("estimate.items.quantity")];
        $rule["invoice.items.*.unit_type_id"] = ["required", Rule::in($allowedUnitTypes)];
        $rule["invoice.items.*.net_amount"] = ["required", new \App\Rules\Numeric("estimate.items.net_amount")];
        $rule["invoice.items.*.vat_rate_id"] = ["required", Rule::in($allowedVatRates)];
        
        if(in_array($action, ["create.warehouse_document"]))
        {
            $rule["invoice.warehouse_document_items.*.unit_type_id"] = ["required", Rule::in($allowedUnitTypes)];
            $rule["invoice.warehouse_document_items.*.net_amount"] = ["required", new \App\Rules\Numeric("estimate.items.net_amount")];
            $rule["invoice.warehouse_document_items.*.vat_rate_id"] = ["required", Rule::in($allowedVatRates)];
        }

        return $rule;
    }

    public function invoicePrint(Request $request, $id)
    {
        User::checkAccess("customer_invoices:list");

        $row = UserInvoices::find($id);
        if(!$row)
            return redirect()->route("panel.correction_create")->withErrors(["msg" => "Wybrana faktura nie istnieje"]);

        \App\Libraries\InvoicePrinter::generatePDF($row);
    }

    public function paymentUpdate(Request $request, $id)
    {
        User::checkAccess("customer_invoices:update");

        $out = [];
        $invoice = UserInvoices::find($id);
        if(!$invoice) {
            $out["success"] = false;
            $out["error"] = ["_message" => "Faktura nie istnieje"];
        } else {
            $validator = \Validator::make($request->all(), $this->getPaymentValidateRule($request, $invoice->type));
            if($validator->fails()) {
                $errors = [];
                foreach($validator->messages()->getMessages() as $field => $messages) {
                    foreach($messages AS $message) {
                        if(!isset($errors[$field]))
                            $errors[$field] = $message;
                    }
                }
                $out["success"] = false;
                $out["error"] = $errors;
            }
            else
            {
                $amount = abs(str_replace(",", ".", $request->input("payment.amount")));
                $operation = "payment";
                if(!empty($request->input("payment.operation")) && $invoice->type == "correction")
                    $operation = $request->input("payment.operation");

                if($operation == "paycheck")
                    $amount = -$amount;

                if(UserInvoices::checkOperation($invoice, "payment:add"))
                {
                    $row = new CashRegisterHistory;
                    $row->operation = $operation;
                    $row->customer_id = $invoice->customer_id;
                    $row->payer_id = $invoice->customer_id;
                    $row->cash_register_id = $request->input("payment.cash_register_id");
                    $row->added_by = Auth::user()->id;
                    $row->payment_date = $request->input("payment.payment_date");
                    $row->amount = $amount;
                    $row->balance = $amount;
                    $row->title = $request->input("payment.title");
                    $row->description = $request->input("payment.description");
                    $row->save();
                    $out["success"] = true;

                    $row->settlePayment($invoice, $row->payment_date);
                }

                $out["total_payments"] = $invoice->getTotalPayments();
                $out["cash_register_summary"] = $invoice->getCashRegisterSummary();
            }
        }

        return json_encode($out);
    }

    public function getPaymentValidateRule(Request $request, $type)
    {
        $rule = [];
        $rule["payment.amount"] = "required|numeric|min:0.01";
        if($type == "correction")
            $rule["payment.operation"] = "required|in:payment,paycheck";

        $operation = $request->input("payment.operation", "payment");
        $allowedCashRegisterId = [];
        foreach(ConfigCashRegister::getRegistries($operation) as $cR)
            $allowedCashRegisterId[] = $cR->id;
        $rule["payment.cash_register_id"] = ["required", Rule::in($allowedCashRegisterId)];

        $rule["payment.payment_date"] = "required|date_format:Y-m-d";
        $rule["payment.title"] = "nullable|max:200";
        return $rule;
    }
    
    public function invoiceCreateFromWarehouseDocuments(Request $request, $ids)
    {
        User::checkAccess("customer_invoices:create");
        
        $ids = explode(",", $ids);
        $ids = array_unique(array_filter(array_map("trim", $ids)));
        
        try
        {
            $type = "invoice";
            $documents = $this->getDocumentsToInvoice($ids);
            
            $documentItems = $this->getDocumentsProducts($documents);
            
            $paymentDate = new \DateTime();
            $paymentDate->add(new \DateInterval("P" . config("panel.default.invoice.payment_days") . "D"));
            
            $formData = [
                "invoice" => [
                    "created_user_id" => Auth::user()->id,
                    "document_date" => date("Y-m-d"),
                    "sell_date" => date("Y-m-d"),
                    "payment_date" => $paymentDate->format("Y-m-d"),
                    "sale_register_id" => ConfigSaleRegister::getDefaultValue($type),
                    "items" => [],
                    "payment_type_id" => Dictionary::getDictionaryDefaultValue("payment_type"),
                    "customer_id" => $documents[0]->customer_id,
                ],
            ];
    
            $defaultBankAccount = BankAccount::getDefaultBankAccount();
            if($defaultBankAccount)
            {
                $formData["invoice"]["account_number"] = $defaultBankAccount["number"];
                $formData["invoice"]["swift_number"] = $defaultBankAccount["swift"];
            }
    
            $old = \Request::old();
            if($old)
                $formData = array_merge($formData, $old);
                
            if(!empty($documentItems) && !empty($formData["invoice"]["warehouse_document_items"]))
            {
                $oldDocumentItems = $formData["invoice"]["warehouse_document_items"];
                foreach($documentItems as $i => $documentItem)
                {
                    if(!empty($oldDocumentItems[$documentItem["id"]]))
                        $documentItems[$i] = array_merge($documentItem, $oldDocumentItems[$documentItem["id"]]);
                }
            }
    
            $selectpickerValues = [];
            $selectpickerValues["customer"] = Customer::find($documents[0]->customer_id);
            if(!empty($formData["invoice"]["recipient_id"]))
                $selectpickerValues["recipient"] = Customer::find($formData["invoice"]["recipient_id"]);
            if(!empty($formData["invoice"]["payer_id"]))
                $selectpickerValues["payer"] = Customer::find($formData["invoice"]["payer_id"]);
            
            $vData = [
                "form" => $formData,
                "sale_registries" => ConfigSaleRegister::where("type", $type)->orderBy("name", "ASC")->get(),
                "employees" => User::getEmployees(true),
                "dictionary" => [
                    "payment_types" => Dictionary::getDictionary("payment_type", true),
                    "gtu_codes" => config("panel.gtu"),
                    "vat_rates" => Dictionary::getDictionary("vat_rate", true),
                    "unit_types" => Dictionary::getDictionary("unit_type", true),
                    "languages" => config("panel.invoice.languages"),
                    "currencies" => config("panel.invoice.currencies"),
                    "device_types" => Dictionary::getDictionary("device_type", true),
                    "repair_statuses" => Dictionary::getDictionary("repair_status", true),
                ],
                "default_payment_days" => config("panel.default.invoice.payment_days"),
                "warehouse_document_items" => $documentItems,
                "products" => Product::getProductsList(true, ["id", "price", "vat_rate_id", "unit_type_id", "gtu", "name"]),
                "action" => "create",
                "current_sale_document_type" => $type,
                "form_state" =>  UserInvoices::getAllowedOperations(),
                "devices" => Device::orderBy("name", "ASC")->get(),
                "servicemans" => User::getServiceman(true),
                "bank_accounts" => BankAccount::getInvoiceBankAccounts(),
                "selectpickerValues" => $selectpickerValues,
                "defaults" => [
                    "vat_rate_id" => Dictionary::getDictionaryDefaultValue("vat_rate"),
                    "unit_type_id" => Dictionary::getDictionaryDefaultValue("unit_type"),
                ],
                "is_warehouse" => Account::isWarehouseActive(),
            ];
            
            $this->calculateSummary($vData);
            
            return view("panel.user-invoices.warehouse_document.create", $vData);
        }
        catch(Exception $e)
        {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
    
    public function invoiceCreateFromWarehouseDocumentPost(Request $request, $ids)
    {
        User::checkAccess("customer_invoices:create");
        
        $ids = explode(",", $ids);
        $ids = array_unique(array_filter(array_map("trim", $ids)));
        
        try
        {
            $type = "invoice";
            $documents = $this->getDocumentsToInvoice($ids);
            
            $documentItems = $this->getDocumentsProducts($documents);
            
            $validator = \Validator::make($request->all(), $this->getValidateRule($request, "create.warehouse_document"));
            if($validator->fails()) {
                return redirect()
                            ->route("panel.user_invoices.create.documents", implode(",", $ids))
                            ->withErrors($validator)
                            ->withInput();
            }
            
            $row = DB::transaction(function () use($request, $documentItems, $documents){
                $saleRegister = ConfigSaleRegister::find($request->input("invoice.sale_register_id"));
                
                $row = new UserInvoices;
                $row->type = $saleRegister->type;
                $row->sale_register_id = $request->input("invoice.sale_register_id");
                $row->created_user_id = $request->input("invoice.created_user_id");
                $row->customer_id = $request->input("invoice.customer_id");
                $row->recipient_id = $request->input("invoice.recipient_id");
                $row->payer_id = $request->input("invoice.payer_id");
                $row->comment = $request->input("invoice.comment");
                $row->document_date = $request->input("invoice.document_date");
                $row->sell_date = $request->input("invoice.sell_date");
                $row->payment_date = $request->input("invoice.payment_date");
                $row->payment_type_id = $request->input("invoice.payment_type_id");
                $row->account_number = $request->input("invoice.account_number");
                $row->swift_number = $request->input("invoice.swift_number");
                $row->language = $request->input("invoice.language");
                $row->currency = $request->input("invoice.currency");
                $row->save();
                
                $items1 = $request->input("invoice.items", []);
                $items2 = $request->input("invoice.warehouse_document_items", []);
                if(!empty($documentItems) && !empty($items2))
                {
                    foreach($documentItems as $i => $documentItem)
                    {
                        if(!empty($items2[$documentItem["id"]]))
                        {
                            $items2[$documentItem["id"]]["id"] = -1;
                            $items2[$documentItem["id"]]["product_name"] = $documentItem["name"];
                            $items2[$documentItem["id"]]["product_id"] = $documentItem["product_id"];
                            $items2[$documentItem["id"]]["quantity"] = $documentItem["quantity"];
                            $items2[$documentItem["id"]]["from_warehouse_document"] = true;
                        }
                    }
                }
                $items = array_merge($items1, $items2);
                $row->addItems($items);
                
                $newWarehouseDocumentItems = $row->getItemsToWarehouseDocument();
                if(!$newWarehouseDocumentItems->isEmpty())
                {
                    $document = WarehouseManager::createFromInvoice($row, $newWarehouseDocumentItems);
                    if($document)
                        $row->associateWarehouseDocuments($document);
                }
                $row->associateWarehouseDocuments($documents);
                
                $row->source = UserInvoices::SOURCE_WAREHOUSE_DOCUMENT;
                $row->save();
                
                return $row;
            });
        }
        catch(Exception $e)
        {
            return redirect()->back()->withErrors($e->getMessage());
        }
        
        Helper::setMessage("user-invoices", "Faktura została zapisana");
        if($this->isApply())
            return redirect()->route("panel.user_invoices.update", $row->id);
        else
            return redirect()->route("panel.user_invoices", $row->type);
    }
    
    private function calculateSummary(&$vData)
    {
        $totalNet = $totalGross = 0;
        if(!empty($vData["form"]["invoice"]["items"]))
        {
            foreach($vData["form"]["invoice"]["items"] as $item)
            {
                $totalNet += $item["net_amount"] * $item["quantity"];
                $totalGross += $item["gross_amount"] * $item["quantity"];
            }
        }
        
        if(!empty($vData["warehouse_document_items"]))
        {
            foreach($vData["warehouse_document_items"] as $documentItem)
            {
                $totalNet += $documentItem["net_amount"] * $documentItem["quantity"];
                $totalGross += $documentItem["gross_amount"] * $documentItem["quantity"];
            }
        }
        
        $vData["form"]["invoice"]["net_amount"] = $totalNet;
        $vData["form"]["invoice"]["gross_amount"] = $totalGross;
        $vData["form"]["invoice"]["balance"] = $totalGross;
    }
}
