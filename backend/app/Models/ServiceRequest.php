<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RequestCategory;
use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceRequest extends Model
{
  use HasFactory;

  public const SORTABLE = ['created_at', 'updated_at', 'title', 'status'];

  protected $fillable = ['title', 'description', 'category', 'priority'];

  protected $attributes = ['status' => 'draft', 'priority' => 'medium'];

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
}
