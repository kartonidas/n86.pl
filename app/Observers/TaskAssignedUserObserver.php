<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\TaskAssignedUser;

class TaskAssignedUserObserver
{
    public function created(TaskAssignedUser $row): void
    {
        if($row->user_id != Auth::user()->id)
            Notification::notify($row->user_id, Auth::user()->id, $row->task_id, "task:assign");
    }
}
