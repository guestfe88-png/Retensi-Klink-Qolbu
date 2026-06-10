<?php

namespace App\Providers;

use App\Models\Berkas;
use App\Models\User;
use App\Policies\BerkasPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Berkas::class, BerkasPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
