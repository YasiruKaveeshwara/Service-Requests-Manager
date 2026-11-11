<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RequestCategory;
use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property RequestStatus $status
 * @property RequestCategory $category
 * @property RequestPriority $priority
 * @property Carbon|null $submitted_at
 * @property Carbon|null $completed_at
 * @property-read User $requester
 * @property-read User|null $assignee
 */
class ServiceRequest extends Model
{
    use HasFactory;

    public const SORTABLE = ['created_at', 'updated_at', 'title', 'status'];

    protected $fillable = ['title', 'description', 'category', 'priority'];

    protected $attributes = ['status' => 'draft', 'priority' => 'medium'];

    protected static function booted(): void
    {
        static::deleting(function (self $serviceRequest): void {
            $serviceRequest->comments()->delete();
            $serviceRequest->activityLogs()->delete();
        });
    }

    protected function casts(): array
    {
        return [
            'status' => RequestStatus::class,
            'category' => RequestCategory::class,
            'priority' => RequestPriority::class,
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class)->orderBy('id');
    }

    public function scopeVisibleTo(Builder $query, User $user): void
    {
        match ($user->role) {
            UserRole::Employee => $query->where('requester_id', $user->id),
            UserRole::Manager => $query->whereNotNull('submitted_at')
                ->whereIn('requester_id', fn($sub) => $sub->select('id')->from('users')->where('manager_id', $user->id)),
            UserRole::Operations => $query->whereIn('status', RequestStatus::operationsVisibleValues()),
        };
    }

    /** @param array<string, mixed> $filters */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['status'] ?? null, fn(Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['assignee_id'] ?? null, fn(Builder $q, $id) => $q->where('assignee_id', (int) $id))
            ->when($filters['search'] ?? null, function (Builder $q, string $term): void {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $q->where(fn(Builder $w) => $w->where('title', 'ilike', $like)->orWhere('description', 'ilike', $like));
            });
    }

    public function loadDetails(): static
    {
        return $this->load(['requester', 'assignee', 'comments.user', 'activityLogs.actor']);
    }
}
