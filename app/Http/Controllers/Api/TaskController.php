<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBulkTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{

    public function __construct(private TaskService $tasks) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->string('status')->toString() ?: null;
        return TaskResource::collection($this->tasks->list($request->user(), $status));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->tasks->create($request->user(), $request->validated());
        return (new TaskResource($task))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): TaskResource
    {
        Gate::authorize('view', $task);
        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        Gate::authorize('update', $task);
        return new TaskResource($this->tasks->update($task, $request->validated()));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): Response
    {
        Gate::authorize('delete', $task);
        $this->tasks->delete($task);

        return response()->noContent();
    }

    public function stats(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->tasks->stats($request->user())]);
    }

    public function bulkStore(StoreBulkTasksRequest $request): JsonResponse
    {
        $created = $this->tasks->createMany($request->user(), $request->validated('tasks'));

        return TaskResource::collection($created)->response()->setStatusCode(201);
    }
}
