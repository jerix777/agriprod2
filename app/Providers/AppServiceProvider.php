<?php

namespace App\Providers;

use App\View\Components\Header;
use App\View\Components\Message;
use App\View\Components\Paginer;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $actions = [
        'access', 'view', 'edit', 'create', 'update', 'delete',
    ];

    // protected $policies = [
    //     \App\Models\User::class => \App\Policies\UserPolicy::class,
    //     \App\Models\Culture::class => \App\Policies\CulturePolicy::class,
    //     \App\Models\Demande::class => \App\Policies\DemandePolicy::class,
    //     \App\Models\Departement::class => \App\Policies\DepartementPolicy::class,
    //     \App\Models\Employee::class => \App\Policies\EmployeePolicy::class,
    //     \App\Models\Genre::class => \App\Policies\GenrePolicy::class,
    //     \App\Models\Parcelle::class => \App\Policies\ParcellePolicy::class,
    //     \App\Models\Piece::class => \App\Policies\PiecePolicy::class,
    //     \App\Models\Poste::class => \App\Policies\PostePolicy::class,
    //     \App\Models\Producteur::class => \App\Policies\ProducteurPolicy::class,
    //     \App\Models\Production::class => \App\Policies\ProductionPolicy::class,
    // ];
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // if $this->app->isLocaal(){
        //     $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        // }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // //URL::forceScheme('https');
        // Gate::before(function ($user, $ability) {
        //     return $user->hasRole($ability) ? true : null;
        // });

        // Permission::get()->each(function ($permission) {
        //     Gate::define($permission->name, function (User $user) use ($permission) {
        //         return $user->hasPermissionTo($permission);
        //     });
        // });

        // // Enregistre les gates des modèles
        // (new GateGenerator())->generate($this->getModelNames(), $this->actions);

        Blade::component('header', Header::class);
        //Blade::component('footer', \App\View\Components\Footer::class);
        Blade::component('paginer', Paginer::class);
    }

    protected function getModelNames(): array
    {
        $path = app_path('Models').'/*.php';

        return collect(glob($path))->map(fn ($file) => basename($file, '.php'))->toArray();
    }
}
