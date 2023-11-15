<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    const DOCUMENT_TYPE_ID = "id";
    const DOCUMENT_TYPE_PASSPORT = "passport";

    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    public function scopeApiFields(Builder $query): void
    {
        $query->select("id", "active", "name", "street", "house_no", "apartment_no", "city", "zip", "nip", "document_type", "document_number", "created_at");
    }
}