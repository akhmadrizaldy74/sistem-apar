<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        // Created event
        static::created(function ($model) {
            self::logAudit('created', $model, 'Membuat', [
                'attributes' => $model->getAttributes(),
            ]);
        });

        // Updated event
        static::updated(function ($model) {
            $changes = $model->getChanges();
            $original = $model->getOriginal();

            // Remove timestamps from diff
            unset($changes['updated_at'], $changes['created_at']);
            unset($original['updated_at'], $original['created_at']);

            if (empty($changes)) return;

            $dirty = [];
            foreach ($changes as $key => $newValue) {
                if (in_array($key, ['updated_at', 'created_at'])) continue;
                $dirty[$key] = [
                    'old' => $original[$key] ?? null,
                    'new' => $newValue,
                ];
            }

            self::logAudit('updated', $model, 'Memperbarui', [
                'changes' => $dirty,
            ]);
        });

        // Deleted event
        static::deleted(function ($model) {
            self::logAudit('deleted', $model, 'Menghapus', [
                'attributes' => $model->getAttributes(),
            ]);
        });
    }

    protected static function logAudit(string $event, $model, string $action, array $properties = []): void
    {
        $subjectType = get_class($model);
        $subjectId = $model->getKey();

        $modelName = class_basename($subjectType);

        ActivityLog::log(
            description: "{$action} {$modelName} #{$subjectId}",
            logName: 'default',
            subjectType: $subjectType,
            subjectId: $subjectId,
            event: $event,
            properties: $properties,
        );
    }
}