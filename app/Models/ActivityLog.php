<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'event',
        'properties',
        'batch_uuid',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
        'subject_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        if ($this->subject_type) {
            return $this->subject_type::find($this->subject_id);
        }
        return null;
    }

    public static function log(
        string $description,
        ?string $logName = 'default',
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?string $event = null,
        ?array $properties = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): self {
        return static::create([
            'user_id' => auth()->id(),
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'event' => $event,
            'properties' => $properties,
            'ip_address' => $ip ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
        ]);
    }
}