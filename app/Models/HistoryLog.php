<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryLog extends Model
{
    public $timestamps = false;
    protected $table = "history_logs";
}
