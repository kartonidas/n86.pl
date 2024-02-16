<?php

namespace App\Http\Controllers;

use PDF;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Exceptions\Exception;
use App\Exceptions\ObjectNotExist;
use App\Http\Requests\DocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Libraries\TemplateManager;
use App\Libraries\Helper;
use App\Models\Document;
use App\Models\Item;
use App\Models\Rental;
use App\Models\User;
use App\Traits\Sortable;

class DocumentController extends Controller
{
    use Sortable;
    
    public function list(DocumentRequest $request) {
        User::checkAccess("document:list");
        
        $validated = $request->validated();
        
        $size = $validated["size"] ?? config("api.list.size");
        $skip = isset($validated["first"]) ? $validated["first"] : (($validated["page"] ?? 1)-1)*$size;
        
        $documents = Document::whereRaw("1=1");
            
        if(!empty($validated["search"]))
        {
            if(!empty($validated["search"]["title"]))
                $documents->where("title", "LIKE", "%" . $validated["search"]["title"] . "%");
                
            if(!empty($validated["search"]["item_name"]))
            {
                $itemIds = Item::where("name", "LIKE", "%" . $validated["search"]["item_name"] . "%")->pluck("id")->all();
                $documents->whereIn("item_id", $itemIds);
            }
                
            if(!empty($validated["search"]["type"]))
                $documents->where("type", $validated["search"]["type"]);
        }
            
        $total = $documents->count();
        
        $orderBy = $this->getOrderBy($request, Document::class, "created_at,desc");
        $documents = $documents->take($size)
            ->skip($skip)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
            
        foreach($documents as $i => $document)
        {
            $documents[$i]->rental = $document->rental->first();
            $documents[$i]->item = $document->item->first();
            unset($documents[$i]->content);
        }
        
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "data" => $documents,
        ];
            
        return $out;
    }
    
    public function getDocumentPdf(Request $request, int $documentId)
    {
        User::checkAccess("document:list");
        
        $document = Document::find($documentId);
        if(!$document)
            throw new ObjectNotExist(__("Document does not exist"));
        
        $rental = Rental::find($document->rental_id);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $manager = TemplateManager::getTemplate($rental);
        $html = $manager->generateHtml($document->content);
        
        $pdf = PDF::loadView("pdf.rental_document", ["content" => $html]);
        $pdf->getMpdf()->SetTitle(Helper::__no_pl($document->title) . ".pdf");
        $pdf->stream(Helper::__no_pl($document->title) . ".pdf");
    }
    
    public function get(Request $request, int $documentd)
    {
        User::checkAccess("document:list");
        
        $document = Document::find($documentd);
        if(!$document)
            throw new ObjectNotExist(__("Document does not exist"));
        
        return $document;
    }
    
    public function update(UpdateDocumentRequest $request, int $documentId)
    {
        User::checkAccess("document:update");
        
        $document = Document::find($documentId);
        if(!$document)
            throw new ObjectNotExist(__("Document does not exist"));
        
        $validated = $request->validated();
        
        $document->title = $validated["title"];
        $document->content = $validated["content"];
        $document->save();
        
        return true;
    }
    
    public function delete(Request $request, int $documentId)
    {
        User::checkAccess("document:delete");
        $document = Document::find($documentId);

        if(!$document)
            throw new ObjectNotExist(__("Document does not exist"));
        
        $document->delete();
        
        return true;
    }
}