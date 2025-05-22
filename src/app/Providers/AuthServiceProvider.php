<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Policies\CategoryPolicy;
use App\Policies\ItemPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Category::class => CategoryPolicy::class,
        Supplier::class => SupplierPolicy::class,
        Item::class => ItemPolicy::class,
        Transaction::class => TransactionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define a super-admin role that can do anything
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
        });
    }
}
