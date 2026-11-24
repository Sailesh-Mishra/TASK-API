<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\TaskStatus;

class Task extends Model
{
    protected $fillable = ['title', 'description', 'status'];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    protected $attributes = ['status' => TaskStatus::Todo->value];
    protected function casts(): array
    {
        return ['status' => TaskStatus::class];
    }
}
