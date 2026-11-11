<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityEvent;
use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property ActivityEvent $event
 * @property RequestStatus|null $from_status
 * @property RequestStatus|null $to_status
 * @property Carbon $created_at
 */
class ActivityLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'service_request_id',
        'actor_id',
        'event',
        'from_status',
        'to_status',
        'comment',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'event' => ActivityEvent::class,
            'from_status' => RequestStatus::class,
            'to_status' => RequestStatus::class,
            'metadata' => 'array',
        ];
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
