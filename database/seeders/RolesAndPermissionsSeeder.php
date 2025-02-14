<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    protected $actions = ['access', 'view', 'edit', 'create', 'update', 'delete', 'search', 'print'];

    protected $roleModels = [
        'Developpeur' => 'all',
        'Administrateur' => [
            'dashboard', 'profil', 'account', 'settings', 'message', 'employee', 'poste', 'campagne', 'culture', 'parcelle', 'production', 'producteur', 'user', 'departement',
        ],
        'Manager' => [
            'dashboard', 'profil', 'account', 'settings', 'message', 'employee', 'poste', 'campagne', 'culture', 'parcelle', 'production', 'producteur',
        ],
        'Fonctionnaire' => [
            'dashboard', 'profil', 'account', 'settings', 'message', 'poste', 'campagne', 'culture', 'parcelle', 'production', 'producteur',
        ],
        'Employee' => [
            'dashboard', 'profil', 'account', 'settings', 'message', 'poste', 'campagne', 'culture', 'parcelle', 'production', 'producteur',
        ],
        'Producteur' => [
            'dashboard', 'profil', 'account', 'settings', 'message', 'parcelle', 'production',
        ],
        'Visiteur' => [],
    ];

    protected function getModelNames(): array
    {
        $path = app_path('Models' . DIRECTORY_SEPARATOR . '*.php');

        return collect(glob($path))->map(fn($file) => basename($file, '.php'))->toArray();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Génère dynamiquement les permissions pour les modèles
        foreach ($this->getModelNames() as $model) {
            foreach ($this->actions as $action) {
                Permission::firstOrCreate(['name' => "{$action} " . strtolower($model)]);
            }
        }

        // Génère dynamiquement les permissions spécifiques aux rôles
        foreach ($this->roleModels as $routes) {
            if ($routes !== 'all') {
                foreach ($routes as $route) {
                    foreach ($this->actions as $action) {
                        Permission::firstOrCreate(['name' => "{$action} {$route}"]);
                    }
                }
            }
        }

        // Crée les rôles et assigne les permissions s'ils n'existent pas déjà
        foreach ($this->roleModels as $roleName => $roleModels) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            if ($roleModels === 'all') {
                // Assigner toutes les permissions disponibles au rôle
                $role->syncPermissions(Permission::all());
            } else {
                // Crée un tableau pour stocker les permissions à assigner
                $permissions = [];

                foreach ($roleModels as $model) {
                    foreach ($this->actions as $action) {
                        $permissions[] = "{$action} {$model}";
                    }
                }

                // Synchronise les permissions avec le rôle
                $role->syncPermissions($permissions);
            }
        }

        // Rafraîchir le cache des permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Assigner les rôles aux utilisateurs
        $user1 = User::find(1);
        $user2 = User::find(2);

        if ($user1) {
            $user1->assignRole('Developpeur');
        }

        if ($user2) {
            $user2->assignRole('Manager');
        }
    }
}
