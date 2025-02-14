<?php

namespace App\Services;

use App\Http\Controllers\AppController;
use Illuminate\Support\Facades\Route;

class RouteGenerator
{
    public function generate($models, $appRoles): void
    {
        Route::middleware(['auth'])->group(function () use ($models, $appRoles) {
            foreach ($appRoles as $appRole) {
                // Génère les routes dynamiques
                $roleName = strtolower($appRole->name);

                foreach ($models as $model) {
                    $modelName = strtolower($model);
                    $accessAbility = "access {$modelName}";
                    $controller = 'App\\Http\\Controllers\\'.ucfirst($modelName).'Controller';
                    Route::get($roleName.'/', [AppController::class, 'index'])->name("{$roleName}.index");
                    Route::get($roleName.'/dashboard', [AppController::class, 'index'])->name("{$roleName}.dashboard");

                    if ($appRole->hasPermissionTo($accessAbility)) {
                        Route::get("{$roleName}/{$modelName}s/dashboard", [AppController::class, 'index'])->name("{$roleName}.{$modelName}.dashboard");
                        // Ajout du préfixe du rôle pour éviter les conflits de route
                        Route::group([
                            'middleware' => ['role:'.ucfirst($roleName)],
                            'prefix' => "{$roleName}/{$modelName}s", // Préfixer avec le nom du rôle
                        ], function () use ($appRole, $roleName, $modelName, $controller) {

                            $this->defineRoutes($modelName, $controller, $appRole, $roleName);
                        });
                    }
                }
            }
        });
    }

    private function defineRoutes($modelName, $controller, $appRole, $roleName): void
    {
        Route::get('/', [$controller, 'index'])->name("{$roleName}.{$modelName}.index");

        if ($appRole->hasPermissionTo("create {$modelName}")) {
            Route::get('/create', [$controller, 'create'])->name("{$roleName}.{$modelName}.create");
            Route::post('/create', [$controller, 'store'])->name("{$roleName}.{$modelName}.store");
        }

        if ($appRole->hasPermissionTo("update {$modelName}")) {
            Route::get('/edit/{id}', [$controller, 'edit'])->name("{$roleName}.{$modelName}.edit");
            Route::put('/edit/{id}', [$controller, 'update'])->name("{$roleName}.{$modelName}.update");
        }

        if ($appRole->hasPermissionTo("delete {$modelName}")) {
            Route::get('/delete/{id}', [$controller, 'delete'])->name("{$roleName}.{$modelName}.delete");
        }

        if ($appRole->hasPermissionTo("search {$modelName}")) {
            Route::get('/search', [$controller, 'search'])->name("{$roleName}.{$modelName}.search");
        }

        if ($appRole->hasPermissionTo("print {$modelName}")) {
            Route::get('/print', [$controller, 'print'])->name("{$roleName}.{$modelName}.print");
        }
    }
}
