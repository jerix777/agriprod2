<?php

namespace App\Providers;

// use Illuminate\Foundation\Auth\User as Authentificable;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Culture::class => \App\Policies\CulturePolicy::class,
        \App\Models\Demande::class => \App\Policies\DemandePolicy::class,
        \App\Models\Departement::class => \App\Policies\DepartementPolicy::class,
        \App\Models\Employee::class => \App\Policies\EmployeePolicy::class,
        \App\Models\Genre::class => \App\Policies\GenrePolicy::class,
        \App\Models\Parcelle::class => \App\Policies\ParcellePolicy::class,
        \App\Models\Piece::class => \App\Policies\PiecePolicy::class,
        \App\Models\Poste::class => \App\Policies\PostePolicy::class,
        \App\Models\Producteur::class => \App\Policies\ProducteurPolicy::class,
        \App\Models\Production::class => \App\Policies\ProductionPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistre les politiques
        $this->registerPolicies();

        //URL::forceScheme('https');
    }
}
