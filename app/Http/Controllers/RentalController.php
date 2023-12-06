<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Exceptions\ObjectNotExist;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\StoreRentRequest;
use App\Http\Requests\StoreRentalRequest;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateRentalRequest;

class RentalController extends Controller
{
    public function validateData(StoreRentalRequest $request)
    {
        return true;
    }
    
    public function rent(StoreRentRequest $request)
    {
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
        
        print_r($itemData);
        print_r($tenantData);
        print_r($rentData);
        
        
        // TODO: zapis wynajmu
        // Trzeba pamiętac o walidacji dat!!!!! nie może być data przeszła, daty wynajmu nie moga się pokrywać
        
        
    }
}