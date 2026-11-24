<?php

namespace App\Providers;

use App\Models\User;
use App\Repositories\EloquentTaskRepository;
use App\Repositories\TaskRepositoryInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TaskRepositoryInterface::class, EloquentTaskRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-users', fn(User $user) => $user->isAdmin());
        RateLimiter::for('login', fn(Request $request) =>
        Limit::perMinute(5)->by(strtolower((string) $request->input('email')) . '|' . $request->ip()));

        RateLimiter::for('register', fn(Request $request) =>
        Limit::perHour(10)->by($request->ip()));

        RateLimiter::for('api', fn(Request $request) =>
        Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
        Model::preventLazyLoading(! app()->isProduction());
    }
}
