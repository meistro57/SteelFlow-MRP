<?php

// Modules/Core/app/Traits/HasAuditFields.php

declare(strict_types=1);

namespace Modules\Core\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Events\ModelAuditEvent;

trait HasAuditFields
{
    public static function bootHasAuditFields(): void
    {
        static::creating(function (Model $model): void {
            $actorId = Auth::id();

            if ($actorId === null) {
                return;
            }

            if ($model->getAttribute('created_by') === null) {
                $model->setAttribute('created_by', $actorId);
            }

            if ($model->getAttribute('updated_by') === null) {
                $model->setAttribute('updated_by', $actorId);
            }
        });

        static::updating(function (Model $model): void {
            $actorId = Auth::id();

            if ($actorId === null) {
                return;
            }

            $model->setAttribute('updated_by', $actorId);
        });

        static::created(function (Model $model): void {
            $model->dispatchAuditEvent('created', $model->getAttributes(), []);
        });

        static::updated(function (Model $model): void {
            $model->dispatchAuditEvent('updated', $model->getChanges(), $model->getOriginal());
        });

        static::deleted(function (Model $model): void {
            $model->dispatchAuditEvent('deleted', [], $model->getOriginal());
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected function dispatchAuditEvent(string $action, array $changes, array $original): void
    {
        event(new ModelAuditEvent($this, $action, Auth::id(), $changes, $original));
    }
}
