<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuditableObserver
{
    /**
     * Handle model creation.
     */
    public function created(Model $model): void
    {
        $this->logAudit('create', $model, [], $model->getAttributes());
    }

    /**
     * Handle model updates.
     */
    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        $original = $model->getOriginal();

        // Build old values from original state
        $oldValues = [];
        foreach (array_keys($changes) as $key) {
            $oldValues[$key] = $original[$key] ?? null;
        }

        $this->logAudit('update', $model, $oldValues, $changes);
    }

    /**
     * Handle model deletion.
     */
    public function deleted(Model $model): void
    {
        $this->logAudit('delete', $model, $model->getAttributes(), []);
    }

    /**
     * Log the audit entry.
     */
    private function logAudit(string $action, Model $model, array $oldValues, array $newValues): void
    {
        // Skip logging for audit logs and sessions to avoid infinite loops
        if ($model instanceof AuditLog) {
            return;
        }

        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'model_type' => class_basename($model),
                'model_id' => $model->id,
                'old_values' => empty($oldValues) ? null : $oldValues,
                'new_values' => empty($newValues) ? null : $newValues,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Exception $e) {
            // Log audit failures silently to avoid breaking the request
            Log::warning('Failed to log audit entry', ['error' => $e->getMessage()]);
        }
    }
}
