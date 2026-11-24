<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return auth('api')->login($user);
    }

    public function test_user_can_register_and_login(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertCreated();

        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password123',
        ])->assertOk()->assertJsonStructure(['access_token']);
    }

    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'not-the-password',
        ])->assertUnauthorized();
    }

    public function test_tasks_require_authentication(): void
    {
        $this->getJson('/api/tasks')->assertUnauthorized();
    }

    public function test_user_can_create_a_task(): void
    {
        $user = User::factory()->create();

        $this->withToken($this->tokenFor($user))
            ->postJson('/api/tasks', ['title' => 'Write tests'])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Write tests')
            ->assertJsonPath('data.status', 'todo');
    }

    public function test_title_is_required(): void
    {
        $user = User::factory()->create();

        $this->withToken($this->tokenFor($user))
            ->postJson('/api/tasks', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_user_cannot_view_another_users_task(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $task = $owner->tasks()->create(['title' => 'Private task']);

        $this->withToken($this->tokenFor($intruder))
            ->getJson("/api/tasks/{$task->id}")
            ->assertForbidden();
    }
}
