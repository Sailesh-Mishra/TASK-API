<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    public function paginateForUser(User $user, ?string $status): LengthAwarePaginator;
    public function paginateAll(string $status): LengthAwarePaginator;
    public function create(User $user, array $data): Task;
    public function update(Task $task, array $data): Task;
    public function delete(Task $task): void;
    public function countByStatus(User $user): array;
}
