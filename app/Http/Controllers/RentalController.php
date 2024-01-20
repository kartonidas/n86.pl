<?php

namespace App\Http\Controllers;

use PDF;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use DateTime;
use DateInterval;

use App\Exceptions\InvalidStatus;
use App\Exceptions\ObjectNotExist;
use App\Http\Requests\BillPaymentRequest;
use App\Http\Requests\RentalBillRequest;
use App\Http\Requests\RentalPaymentRequest;
use App\Http\Requests\RentalRequest;
use App\Http\Requests\RentalDepositRequest;
use App\Http\Requests\RentalDocumentsRequest;
use App\Http\Requests\RentalTemplateDocumentRequest;
use App\Http\Requests\StoreItemBillRequest;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\StoreRentalDocumentRequest;
use App\Http\Requests\StoreRentRequest;
use App\Http\Requests\StoreRentalRequest;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\TerminationRequest;
use App\Http\Requests\UpdateItemBillRequest;
use App\Http\Requests\UpdateRentalRequest;
use App\Http\Requests\UpdateRentalDocumentRequest;
use App\Libraries\Balance\Balance;
use App\Libraries\Balance\Object\DepositObject;
use App\Libraries\Helper;
use App\Libraries\TemplateManager;
use App\Models\BalanceDocument;
use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Item;
use App\Models\ItemBill;
use App\Models\Rental;
use App\Traits\Sortable;
use App\Models\User;

class RentalController extends Controller
{
    use Sortable;
    
    public function list(RentalRequest $request)
    {
        User::checkAccess("rent:list");
        
        $validated = $request->validated();
        
        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $rentals = Rental::whereRaw("1=1");
        
        if(!empty($validated["search"]))
        {
            if(!empty($validated["search"]["item_id"]))
                $rentals->where("item_id", $validated["search"]["item_id"]);
            if(!empty($validated["search"]["tenant_id"]))
                $rentals->where("tenant_id", $validated["search"]["tenant_id"]);
            if(!empty($validated["search"]["status"]))
            {
                if($validated["search"]["status"] == "during_termination")
                    $rentals->where("status", Rental::STATUS_CURRENT)->where("termination", 1);
                else
                    $rentals->where("status", $validated["search"]["status"]);
            }
            if(!empty($validated["search"]["status_arr"]))
                $rentals->whereIn("status", $validated["search"]["status_arr"]);
            
            if(!empty($validated["search"]["item_name"]) || !empty($validated["search"]["item_address"]) || !empty($validated["search"]["item_type"]))
            {
                $items = Item::select("id");
                
                if(!empty($validated["search"]["item_name"]))
                    $items->where("name", "LIKE", "%" . $validated["search"]["item_name"] . "%");
                
                if(!empty($validated["search"]["item_address"]))
                {
                    $searchItemAddress = array_filter(explode(" ", $validated["search"]["item_address"]));
                    $items->where(function($q) use($searchItemAddress) {
                        $q
                            ->where("street", "REGEXP", implode("|", $searchItemAddress))
                            ->orWhere("city", "REGEXP", implode("|", $searchItemAddress));
                    });
                }
                
                if(!empty($validated["search"]["item_type"]))
                    $items->where("type", $validated["search"]["item_type"]);
                
                $itemIds = $items->pluck("id")->all();
                $rentals->whereIn("item_id", !empty($itemIds) ? $itemIds : [-1]);
            }
            
            if(!empty($validated["search"]["tenant_name"]) || !empty($validated["search"]["tenant_address"]) || !empty($validated["search"]["tenant_type"]))
            {
                $tenants = Customer::tenant();
                
                if(!empty($validated["search"]["tenant_name"]))
                    $tenants->where("name", "LIKE", "%" . $validated["search"]["tenant_name"] . "%");
                
                if(!empty($validated["search"]["tenant_address"]))
                {
                    $searchCustomerAddress = array_filter(explode(" ", $validated["search"]["tenant_address"]));
                    $tenants->where(function($q) use($searchCustomerAddress) {
                        $q
                            ->where("street", "REGEXP", implode("|", $searchCustomerAddress))
                            ->orWhere("city", "REGEXP", implode("|", $searchCustomerAddress));
                    });
                }
                
                if(!empty($validated["search"]["tenant_type"]))
                    $tenants->where("type", $validated["search"]["tenant_type"]);
                
                $tenantIds = $tenants->pluck("id")->all();
                $rentals->whereIn("tenant_id", !empty($tenantIds) ? $tenantIds : [-1]);
            }
            
            if(!empty($validated["search"]["start"]))
            {
                $start = Helper::setDateTime($validated["search"]["start"], "00:00:00", true);
                $rentals->where("start", ">=", $start);
            }
            
            if(!empty($validated["search"]["end"]))
            {
                $end = Helper::setDateTime($validated["search"]["end"], "23:59:59", true);
                $rentals->where("end", "<=", $end);
            }
            
            if(!empty($validated["search"]["number"]))
                $rentals->where("full_number", "LIKE", "%" . $validated["search"]["number"] . "%");
        }
        
        $total = $rentals->count();
        
        $orderBy = $this->getOrderBy($request, Rental::class, "full_number,desc");
        $rentals = $rentals->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
        
        foreach($rentals as $i => $rental)
        {
            $rentals[$i]->prepareViewData();
            $rentals[$i]->tenant = $rental->getTenant();
            $rentals[$i]->item = $rental->getItem();
            $rentals[$i]->can_delete = $rental->canDelete();
        }
        
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $rentals,
        ];
            
        return $out;
    }
    
    public function validateData(StoreRentalRequest $request)
    {
        $validated = $request->validated();
        Rental::checkDates($validated, $validated["item_id"] ?? null);
        return true;
    }
    
    public function rent(StoreRentRequest $request)
    {
        User::checkAccess("rent:create");
        
        $rentData = $request->all();
        
        $request->replace($rentData["item"]);
        $storeItemRequest = app(StoreItemRequest::class);
        $itemData = $storeItemRequest->validated();
        
        $request->replace($rentData["tenant"]);
        $storeTenantRequest = app(StoreTenantRequest::class);
        $tenantData = $storeTenantRequest->validated();
        
        $request->replace($rentData["rent"]);
        $storeRentalRequest = app(StoreRentalRequest::class);
        $rentData = $storeRentalRequest->validated();
        
        Rental::checkDates($rentData, $itemData["id"] ?? null);
        
        $rental = DB::transaction(function () use($itemData, $tenantData, $rentData) {
            if(empty($itemData["id"]))
                $itemData["id"] = $this->createItem($itemData);
            
            if(empty($tenantData["id"]))
                $tenantData["id"] = $this->createTenant($tenantData);
                
            $rental = new Rental;
            $rental->item_id = $itemData["id"];
            $rental->tenant_id = $tenantData["id"];
            $rental->document_date = $rentData["document_date"];
            $rental->start = Helper::setDateTime($rentData["start_date"], "00:00:00", true);
            $rental->period = $rentData["period"];
            $rental->months = $rentData["months"] ?? null;
            $rental->end = $rentData["period"] == Rental::PERIOD_DATE ? Helper::setDateTime($rentData["end_date"], "23:59:59", true) : null;
            $rental->termination_period = $rentData["termination_period"];
            $rental->termination_months = $rentData["termination_months"] ?? null;
            $rental->termination_days = $rentData["termination_days"] ?? null;
            $rental->deposit = $rentData["deposit"] ?? null;
            $rental->payment = $rentData["payment"];
            $rental->rent = $rentData["rent"];
            $rental->first_month_different_amount = $rentData["first_month_different_amount_value"] ?? null;
            $rental->last_month_different_amount = $rentData["last_month_different_amount_value"] ?? null;
            $rental->payment_day = $rentData["payment_day"] ?? null;
            $rental->first_payment_date = Helper::setDateTime($rentData["first_payment_date"], "23:59:59", true);
            $rental->number_of_people = $rentData["number_of_people"];
            $rental->comments = $rentData["comments"] ?? null;
            $rental->setEndDate();
            $rental->save();
            
            $rental->setCurrentRentalImmediately();
            $rental->generateInitialItemBills();
            
            return $rental;
        });
        
        return $rental->id;
    }
    
    public function get(Request $request, int $rentalId)
    {
        User::checkAccess("rent:list");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $rental->prepareViewData();
        $rental->tenant = $rental->getTenant();
        $rental->item = $rental->getItem();
        $rental->can_update = $rental->canUpdate();
        $rental->has_paid_deposit = $rental->hasPaidDeposit();
        $rental->has_generated_rent = $rental->hasGeneratedRent();
        return $rental;
    }
    
    public function update(UpdateRentalRequest $request, int $rentalId)
    {
        User::checkAccess("rent:update");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        if(!in_array($rental->status, [Rental::STATUS_CURRENT, Rental::STATUS_WAITING]))
            throw new InvalidStatus(__("Cannot update rental"));
        
        $validated = $request->validated();
        
        Rental::checkDates($validated, $rental->item_id, $rental->id);
        
        $rental->document_date = $validated["document_date"];
        $rental->start = Helper::setDateTime($validated["start_date"], "00:00:00", true);
        $rental->period = $validated["period"];
        $rental->months = $validated["months"] ?? null;
        $rental->end = $validated["period"] == Rental::PERIOD_DATE ? Helper::setDateTime($validated["end_date"], "23:59:59", true) : null;
        $rental->termination_period = $validated["termination_period"];
        $rental->termination_months = $validated["termination_months"] ?? null;
        $rental->termination_days = $validated["termination_days"] ?? null;
        $rental->rent = $validated["rent"];
        
        if(!$rental->hasPaidDeposit())
            $rental->deposit = $validated["deposit"] ?? null;
            
        if(!$rental->hasGeneratedRent())
        {
            $rental->first_month_different_amount = $validated["first_month_different_amount_value"] ?? null;
            $rental->last_month_different_amount = $validated["last_month_different_amount_value"] ?? null;
            $rental->first_payment_date = Helper::setDateTime($validated["first_payment_date"], "23:59:59", true);
        }
        
        $rental->payment_day = $validated["payment_day"] ?? null;
        $rental->number_of_people = $validated["number_of_people"];
        $rental->comments = $validated["comments"] ?? null;
        $rental->setEndDate();
        $rental->save();
        
        $rental->updateRentalStatusFlag();
        $rental->updateItemBills();
        
        return true;
    }
    
    private function createItem($data)
    {
        $item = new Item;
        $item->type = $data["type"];
        $item->name = $data["name"];
        $item->street = $data["street"];
        $item->house_no = $data["house_no"] ?? null;
        $item->apartment_no = $data["apartment_no"] ?? null;
        $item->city = $data["city"];
        $item->zip = $data["zip"];
        $item->country = $data["country"] ?? null;
        $item->area = $data["area"] ?? null;
        $item->ownership_type = $data["ownership_type"];
        $item->customer_id = $data["ownership_type"] == Item::OWNERSHIP_MANAGE ? ($data["customer_id"] ?? null) : null;
        $item->room_rental = $data["room_rental"] ?? 0;
        $item->num_rooms = $data["num_rooms"] ?? null;
        $item->description = $data["description"] ?? null;
        $item->default_rent = $data["default_rent"] ?? null;
        $item->default_deposit = $data["default_deposit"] ?? null;
        $item->comments = $data["comments"] ?? null;
        $item->save();
        
        return $item->id;
    }
    
    private function createTenant($data)
    {
        $tenant = new Customer;
        $tenant->role = Customer::ROLE_TENANT;
        $tenant->type = $data["type"];
        $tenant->name = $data["name"];
        $tenant->street = $data["street"] ?? null;
        $tenant->house_no = $data["house_no"] ?? null;
        $tenant->apartment_no = $data["apartment_no"] ?? null;
        $tenant->city = $data["city"] ?? null;
        $tenant->zip = $data["zip"] ?? null;
        $tenant->country = $data["country"] ?? null;
        $tenant->nip = $data["nip"] ?? null;
        $tenant->regon = $data["regon"] ?? null;
        $tenant->pesel = $data["pesel"] ?? null;
        $tenant->document_type = $data["document_type"] ?? null;
        $tenant->document_number = $data["document_number"] ?? null;
        $tenant->document_extra = $data["document_extra"] ?? null;
        $tenant->comments = $data["comments"] ?? null;
        $tenant->send_sms = $data["send_sms"] ?? 0;
        $tenant->send_email = $data["send_email"] ?? 0;
        $tenant->save();
        
        if(!empty($data["contacts"]))
            $tenant->updateContact($data["contacts"]);
            
        return $tenant->id;
    }
    
    public function delete(Request $request, int $rentalId)
    {
        User::checkAccess("rent:delete");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $rental->delete();
        return true;
    }
    
    public function termination(TerminationRequest $request, int $rentalId)
    {
        User::checkAccess("rent:update");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $validated = $request->validated();
        
        if($validated["mode"] == "immediately")
            $rental->terminateImmediately($validated["termination_reason"] ?? "");
        else
            $rental->terminate(Helper::setDateTime($validated["end_date"], "23:59:59", true), $validated["termination_reason"] ?? "");
        
        return true;
    }
    
    public function bills(RentalBillRequest $request, int $rentalId)
    {
        User::checkAccess("rent:list");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $validated = $request->validated();

        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $rentalBills = ItemBill
            ::where("rental_id", $rentalId);
            
        if(!empty($validated["search"]))
        {
            if(isset($validated["search"]["paid"]))
                $rentalBills->where("paid", !empty($validated["search"]["paid"]) ? 1 : 0);
        }
        
        $total = $rentalBills->count();
        
        $rentalBills = $rentalBills->take($size)
            ->skip(($page-1)*$size)
            ->orderBy("paid", "ASC")
            ->orderByRaw("CASE WHEN paid = 1 THEN payment_date ELSE -payment_date END DESC")
            ->get();
            
        foreach($rentalBills as $i => $itemBill)
        {
            $rentalBills[$i]->prepareViewData();
            $rentalBills[$i]->can_delete = $itemBill->canDelete();
            $rentalBills[$i]->bill_type = $itemBill->getBillType();
            $rentalBills[$i]->out_off_date = $itemBill->isOutOfDate();
            $rentalBills[$i]->balance_document = $itemBill->getBalanceDocument();
        }
            
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $rentalBills,
        ];
            
        return $out;
    }
    
    public function billCreate(StoreItemBillRequest $request, int $rentalId)
    {
        User::checkAccess("rent:update");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $item = Item::find($rental->item_id);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $validated = $request->validated();
        $validated["rental_id"] = $rental->id;
        
        $bill = DB::transaction(function () use($item, $validated) {
            return $item->addBill($validated);
        });
        
        return $bill->id;
    }
    
    public function billGet(Request $request, int $rentalId, int $billId)
    {
        User::checkAccess("rent:list");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $bill = ItemBill::find($billId);
        if(!$bill || $bill->rental_id != $rental->id)
            throw new ObjectNotExist(__("Bill does not exist"));
        
        $bill->prepareViewData();
        $bill->bill_type = $bill->getBillType();
        $bill->out_off_date = $bill->isOutOfDate();
        
        return $bill;
    }
    
    public function billUpdate(UpdateItemBillRequest $request, int $rentalId, int $billId)
    {
        User::checkAccess("rent:update");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $bill = ItemBill::find($billId);
        if(!$bill || $bill->rental_id != $rental->id)
            throw new ObjectNotExist(__("Bill does not exist"));
        
        if($bill->paid)
            throw new InvalidStatus(__("Cannot edit paid bill"));
        
        $validated = $request->validated();
        
        foreach($validated as $field => $value)
        {
            if(!empty($value) && ($field == "payment_date" || $field == "source_document_date"))
                $value = Helper::setDateTime($value, $field == "source_document_date" ? "12:00:00" : "23:59:59", true);
            $bill->{$field} = $value;
        }
        $bill->save();
        
        return true;
    }
    
    public function billDelete(Request $request, int $rentalId, int $billId)
    {
        User::checkAccess("rent:update");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $bill = ItemBill::find($billId);
        if(!$bill || $bill->rental_id != $rental->id)
            throw new ObjectNotExist(__("Bill does not exist"));
        
        $bill->delete();
        return true;
    }
    
    public function billPayment(BillPaymentRequest $request, int $rentalId, int $billId)
    {
        User::checkAccess("rent:update");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $bill = ItemBill::find($billId);
        if(!$bill || $bill->rental_id != $rental->id)
            throw new ObjectNotExist(__("Bill does not exist"));
        
        if($bill->paid)
            throw new InvalidStatus(__("Bill is already paid"));
        
        $validated = $request->validated();
        
        switch($validated["type"])
        {
            case "deposit":
                if(!$bill->rental_id)
                    throw new InvalidStatus(__("Cannot charge the tenant"));
                
                $bill->deposit($validated["cost"], $validated["paid_date"], $validated["payment_method"]);
            break;
        
            case "setpaid":
                $bill->paid();
            break;
        }
        
        return true;
    }
    
    public function generateTemplateDocument(RentalTemplateDocumentRequest $request, int $rentalId)
    {
        User::checkAccess("rent:update");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $validated = $request->validated();
        
        $documentTemplate = DocumentTemplate::find($validated["template"]);
        if(!$documentTemplate || $documentTemplate->type != $validated["type"])
            throw new ObjectNotExist(__("Template does not exists"));
        
        $tenant = $rental->getTenant();
        $item = $rental->getItem();
        $title = DocumentTemplate::getTypes()[$validated["type"]];
        $title .= ": " . $rental->full_number;
        if($tenant) $title .= ", " . $tenant->name;
        if($item) $title .= ", " . $item->name;
        
        return [
            "content" => $documentTemplate->generateDocument($rental),
            "title" => $title
        ];
    }
    
    public function addDocument(StoreRentalDocumentRequest $request, int $rentalId)
    {
        User::checkAccess("rent:update");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $validated = $request->validated();
        
        $document = new Document;
        $document->item_id = $rental->item_id;
        $document->rental_id = $rental->id;
        $document->user_id = Auth::user()->id;
        $document->title = $validated["title"];
        $document->content = $validated["content"];
        $document->type = $validated["type"];
        $document->save();
        
        return $document->id;
    }
    
    public function updateDocument(UpdateRentalDocumentRequest $request, int $rentalId, int $documentId)
    {
        User::checkAccess("rent:update");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $document = Document::find($documentId);
        if(!$document || $document->rental_id != $rentalId)
            throw new ObjectNotExist(__("Document does not exist"));
        
        $validated = $request->validated();
        
        $document->title = $validated["title"];
        $document->content = $validated["content"];
        $document->type = $validated["type"];
        $document->save();
        
        return true;
    }
    
    public function getDocuments(RentalDocumentsRequest $request, int $rentalId)
    {
        User::checkAccess("rent:list");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $validated = $request->validated();
        
        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $documents = Document::select("id", "item_id", "rental_id", "title", "type", "user_id", "created_at", "updated_at");
        $documents->where("rental_id", $rentalId);
        $total = $documents->count();
        
        $orderBy = $this->getOrderBy($request, Document::class, "created_at,desc");
        $documents = $documents->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
        
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $documents,
        ];
            
        return $out;
    }
    
    public function deleteDocument(Request $request, int $rentalId, int $documentId)
    {
        User::checkAccess("rent:update");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $document = Document::find($documentId);
        if(!$document || $document->rental_id != $rentalId)
            throw new ObjectNotExist(__("Document does not exist"));
        
        $document->delete();
        
        return true;
    }
    
    public function getDocument(Request $request, int $rentalId, int $documentId)
    {
        User::checkAccess("rent:update");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $document = Document::find($documentId);
        if(!$document || $document->rental_id != $rentalId)
            throw new ObjectNotExist(__("Document does not exist"));
        
        return $document;
    }
    
    public function getDocumentPdf(Request $request, int $rentalId, int $documentId)
    {
        User::checkAccess("rent:update");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $document = Document::find($documentId);
        if(!$document || $document->rental_id != $rentalId)
            throw new ObjectNotExist(__("Document does not exist"));
        
        $manager = TemplateManager::getTemplate($rental);
        $html = $manager->generateHtml($document->content);
        
        $pdf = PDF::loadView("pdf.rental_document", ["content" => $html]);
        $pdf->getMpdf()->SetTitle(Helper::__no_pl($document->title) . ".pdf");
        $pdf->stream(Helper::__no_pl($document->title) . ".pdf");
    }
    
    public function payments(RentalPaymentRequest $request, int $rentalId)
    {
        User::checkAccess("rent:list");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $validated = $request->validated();
        
        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $balance = $rental->getBalanceRow();
        
        $payments = BalanceDocument
            ::where("item_id", $rental->item_id)
            ->where("balance_id", $balance ? $balance->id : -1)
            ->where("object_type", BalanceDocument::OBJECT_TYPE_DEPOSIT);
        
        $total = $payments->count();
        
        $orderBy = $this->getOrderBy($request, BalanceDocument::class, "created_at,desc");
        $payments = $payments->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
            
        foreach($payments as $k => $payment)
        {
            $payments[$k]->prepareViewData();
            $associatedBills = [];
            $deposits = $payment->getDepositAssociatedDocument();
            if($deposits)
            {
                foreach($deposits as $deposit)
                {
                    if($deposit->object_type == BalanceDocument::OBJECT_TYPE_BILL)
                    {
                        $bill = ItemBill::find($deposit->object_id);
                        if($bill)
                        {
                            $bill->bill_type = $bill->getBillType();
                            $associatedBills[] = $bill;
                        }
                    }
                }
            }
            
            $payments[$k]->associated_documents = $associatedBills;
            $payments[$k]->created_by = $payment->getCreatedBy();
        }
            
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $payments,
        ];
            
        return $out;
    }
    
    public function deletePayment(Request $request, int $rentalId, int $paymentId)
    {
        User::checkAccess("rent:update");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $balanceDocument = BalanceDocument::find($paymentId);
        if(!$balanceDocument || $balanceDocument->item_id != $rental->item_id || $balanceDocument->object_type != BalanceDocument::OBJECT_TYPE_DEPOSIT)
            throw new ObjectNotExist(__("Payment does not exist"));
        
        $balanceDocument->delete();
    }
    
    public function deposit(RentalDepositRequest $request, int $rentalId)
    {
        User::checkAccess("rent:update");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $validated = $request->validated();
        
        $documentIds = [];
        if(!empty($validated["documents"]))
        {
            $documentsAmount = 0;
            $documents = BalanceDocument::whereIn("id", $validated["documents"])->get();
            foreach($documents as $document)
            {
                $documentsAmount += abs($document->amount);
                $documentIds[] = $document->id;
            }
                
            if($validated["cost"] < $documentsAmount)
                throw new InvalidStatus(__("Value of the documents exceeds the amount paid"));
        }
        
        $deposit = DepositObject::makeFromParams($rental->item_id, $rental->id, BalanceDocument::OBJECT_TYPE_DEPOSIT, 0, $validated["cost"]);
        
        $deposit->setDocumentIds($documentIds);
        $deposit->setPayment(Helper::setDateTime($validated["paid_date"], "00:00:00", true), $validated["payment_method"]);
        
        if(isset($validated["comments"]))
            $deposit->setComment($validated["comments"]);
        
        Balance::deposit($deposit)->create();
        
        return true;
    }
    
    public function getDates(Request $request, $itemId)
    {
        User::checkAccess("rental:list");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $rentals = Rental
            ::where("item_id", $item->id)
            ->whereIn("status", [Rental::STATUS_CURRENT, Rental::STATUS_WAITING])
            ->orderBy("start", "ASC")
            ->get();
            
        $dates = [];
        foreach($rentals as $rental)
        {
            $fromDate = (new DateTime())->setTimestamp($rental->start);
            
            if($rental->termination)
            {
                $toDate = (new DateTime())->setTimestamp($rental->termination_time);
            }
            else
            {
                if($rental->period == Rental::PERIOD_INDETERMINATE)
                    $toDate = (new DateTime())->setTimestamp($rental->start)->add(new DateInterval("P20Y"));
                else
                    $toDate = (new DateTime())->setTimestamp($rental->end);
            }
            
            $dates[] = [
                $fromDate->format("Y-m-d"),
                $toDate->format("Y-m-d"),
            ];
        }
        
        return $dates;
    }
}