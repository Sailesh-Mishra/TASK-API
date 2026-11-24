<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class TaskService
{
    private const STATS_KEY = 'user:%d:task_stats';
    private const STATS_TTL = 300;   // seconds

    public function __construct(private TaskRepositoryInterface $tasks)
    {
        // throw new \Exception('Not implemented');
    }

    public function list(User $user, ?string $status): LengthAwarePaginator
    {
        return $user->isAdmin() ? $this->tasks->paginateAll($status) : $this->tasks->paginateForUser($user, $status);
    }

    public function stats(User $user): array
    {
        return Cache::remember(sprintf(self::STATS_KEY, $user->id), self::STATS_TTL, function () use ($user) {
            $counts = $this->tasks->countByStatus($user);
            return collect(TaskStatus::cases())
                ->mapWithKeys(fn(TaskStatus $s) => [$s->value => (int)($counts[$s->value] ?? 0)])->all();
        });
    }

    public function create(User $user, array $data): Task
    {
        $task =  $this->tasks->create($user, $data);
        $this->forgetStats($user->id);
        return $task;
    }

    public function createMany(User $user, array $items): Collection
    {
        $created = DB::transaction(
            fn() => collect($items)->map(fn(array $item) => $this->tasks->create($user, $item))
        );
        $this->forgetStats($user->id);
        return $created;
    }

    public function update(Task $task, array $data): Task
    {
        $task =  $this->tasks->update($task, $data);
        $this->forgetStats($task->user_id);
        return $task;
    }

    public function delete(Task $task): void
    {
        $this->tasks->delete($task);
        $this->forgetStats($task->user_id);
    }

    private function forgetStats(int $userId): void
    {
        Cache::forget(sprintf(self::STATS_KEY, $userId));
    }
}
