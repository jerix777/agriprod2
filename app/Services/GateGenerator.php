<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class GateGenerator extends Gate
{
    public function generate($models, $actions)
    {
        foreach ($models as $model) {
            $permTo = strtolower($model);

            foreach ($actions as $action) {
                $ability = $action.' '.$permTo;
                Gate::define($ability, function (User $user) use ($ability) {
                    return $user->hasPermissionTo($ability);
                });
            }
        }
    }

    protected function getModelNames(): array
    {
        $path = app_path('Models').'/*.php';

        return collect(glob($path))->map(fn ($file) => basename($file, '.php'))->toArray();
    }
}
