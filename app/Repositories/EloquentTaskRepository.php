<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    private const PER_PAGE = 10;
    public function paginateForUser(User $user, ?string $status): LengthAwarePaginator
    {
        return $user->tasks()->when($status, fn($q, $s) => $q->where('status', $s))->latest()->paginate(self::PER_PAGE);
    }
    public function paginateAll(string $status): LengthAwarePaginator
    {
        return Task::with('user:id,name')->when($status, fn($q, $s) => $q->where('status', $s))->latest()->paginate(self::PER_PAGE);
    }
    public function create(User $user, array $data): Task
    {
        return $user->tasks()->create($data);
    }
    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task;
    }
    public function delete(Task $task): void
    {
        $task->delete();
    }
    public function countByStatus(User $user): array
    {
        return $user->tasks()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }
}
