<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function log(string $action, Model $model, ?array $old = null, ?array $new = null): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => Request::ip(),
        ]);
    }
}
