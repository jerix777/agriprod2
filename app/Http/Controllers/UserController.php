<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveAdminRequest;
use App\Models\User;
use App\Services\MigrationMetadataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{
    use AuthorizesRequests;
    protected $routeName = 'administrateur';

    protected $metadataService;

    protected $nbLines = 2;

    public function __construct(MigrationMetadataService $metadataService)
    {
        $this->metadataService = $metadataService;
        // Restreindre l'accès à certaines actions par rôle/permission
        $this->middleware('can:view user')->only(['index', 'showOwn', 'showAll']);
        $this->middleware('can:edit user')->only(['edit', 'update']);
        $this->middleware('can:delete user')->only(['destroy']);
        $this->middleware('can:create user')->only(['create', 'store']);
    }

    protected $tableHeaders = [
        ['key' => '#', 'label' => '#'],
        ['key' => 'id', 'label' => 'ID', 'hidden' => true],
        ['key' => 'matricule', 'label' => 'Matricule'],
        ['key' => 'nom', 'label' => 'Nom'],
        ['key' => 'prenoms', 'label' => 'Prénoms'],
        ['key' => 'fonction', 'label' => 'Fonction d\'origine'],
        ['key' => 'poste.departement.libelle', 'label' => 'Département'],
        ['key' => 'poste.numero_de_serie', 'label' => 'Poste Occupé'],
        ['key' => 'poste.role.libelle', 'label' => 'Rôle'],
        // ['key' => 'created_at', 'label' => 'Créé le', 'type' => 'date', 'format' => 'd M Y H:i:s'],
        ['key' => 'updated_at', 'label' => 'Modifié le', 'type' => 'date', 'format' => 'd M Y H:i:s'],
        ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
        ['key' => 'actions', 'label' => 'Actions'],
    ];

    protected $tableActions = [
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
    ];

    protected $tabs = [
        ['id' => 'orders-all-tab', 'href' => '#orders-all', 'aria-controls' => 'orders-all', 'aria-selected' => 'true', 'label' => 'Tous les Employés', 'active' => true],
        ['id' => 'orders-paid-tab', 'href' => '#orders-paid', 'aria-controls' => 'orders-paid', 'aria-selected' => 'false', 'label' => 'Activés', 'active' => false],
        ['id' => 'orders-pending-tab', 'href' => '#orders-pending', 'aria-controls' => 'orders-pending', 'aria-selected' => 'false', 'label' => 'Désactivés', 'active' => false],
        ['id' => 'orders-cancelled-tab', 'href' => '#orders-cancelled', 'aria-controls' => 'orders-cancelled', 'aria-selected' => 'false', 'label' => 'Supprimés', 'active' => false],
    ];

    public function index($nbAffiche = 10)
    {
        $this->authorize('view', User::class);
        $admins = User::with('postes.departement', 'postes.role')->paginate($nbAffiche);
        $activeUsers = User::with('postes.departement', 'postes.role')->where('status', 'activer')->paginate($nbAffiche);
        $inactiveUsers = User::with('postes.departement', 'postes.role')->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedUsers = User::with('postes.departement', 'postes.role')->where('status', 'supprimer')->paginate($nbAffiche);

        return view('administrateur.index', [
            'title' => 'Personnels',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'user.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'user.create'),
            'addButtonText' => 'Ajouter un Employé',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'user.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $admins,
                'paid' => $activeUsers,
                'pending' => $inactiveUsers,
                'cancelled' => $deletedUsers,
            ],
            'tableHeaders' => $this->tableHeaders,
            'tableActions' => $this->tableActions,
            'routeName' => $this->routeName,
        ]);
    }

    public function create()
    {
        $routeName = $this->routeName;
        $pageTitle = 'Users';
        $sectionTitle = 'Ajout';
        $sectionIntro = 'Enregistrer un nouveau Employé ici.';

        $tableName = 'admins';
        $columns = $this->metadataService->getMigrationMetadata($tableName);
        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('admins.create', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function edit(User $User)
    {
        $routeName = $this->routeName;
        $pageTitle = 'Users';
        $sectionTitle = 'Modification';
        $sectionIntro = 'Modifier l\'Employé ici.';

        $tableName = 'admins';
        $columns = $this->metadataService->getMigrationMetadata($tableName);
        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('admins.edit', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function store(SaveAdminRequest $request)
    {
        try {
            $admin = new User;
            $admin->create($request->all());
            $message = 'Employé enregistré avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'admin.index')->with('success', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function update(int $id, SaveAdminRequest $request)
    {
        try {
            $admin = User::findOrFail($id);
            $admin->update($request->all());
            $message = 'Employé mis à jour avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'admin.index')->with('succes', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function delete($id)
    {
        try {
            User::where('id', $id)->delete();
            $message = 'Employé supprimé avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'admin.index')->with('succes', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function search(Request $request, $nbAffiche = 10)
    {
        $query = $request->input('searchorders');
        $filter = $request->input('filter');

        $baseQuery = User::with('poste.departement', 'poste.role');

        if ($query) {
            $baseQuery->where(function ($q) use ($query) {
                $q->where('nom', 'LIKE', "%$query%")
                    ->orWhere('prenoms', 'LIKE', "%$query%")
                    ->orWhere('matricule', 'LIKE', "%$query%");
            });
        }

        switch ($filter) {
            case 'this_day':
                $baseQuery->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
                break;
            case 'this_week':
                $baseQuery->where('created_at', '>=', Carbon::now()->startOfWeek());
                break;
            case 'this_month':
                $baseQuery->where('created_at', '>=', Carbon::now()->startOfMonth());
                break;
            case 'this_year':
                $baseQuery->where('created_at', '>=', Carbon::now()->startOfYear());
                break;
            case 'all':
            default:
                break;
        }

        $admins = (clone $baseQuery)->paginate($nbAffiche);
        $activeUsers = (clone $baseQuery)->where('status', 'activer')->paginate($nbAffiche);
        $inactiveUsers = (clone $baseQuery)->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedUsers = (clone $baseQuery)->where('status', 'supprimer')->paginate($nbAffiche);

        return view('admins.index', [
            'title' => 'Personnels',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'admin.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'admin.create'),
            'addButtonText' => 'Ajouter un Employé',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'admin.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $admins,
                'paid' => $activeUsers,
                'pending' => $inactiveUsers,
                'cancelled' => $deletedUsers,
            ],
            'tableHeaders' => $this->tableHeaders,
            'tableActions' => $this->tableActions,
            'routeName' => $this->routeName,
        ]);
    }

    public function print(Request $request, $nbAffiche = 10)
    {
        $query = $request->input('searchorders');
        $filter = $request->input('filter');

        $baseQuery = User::with('poste.departement', 'poste.role');

        if ($query) {
            $baseQuery->where(function ($q) use ($query) {
                $q->where('nom', 'LIKE', "%$query%")
                    ->orWhere('prenoms', 'LIKE', "%$query%")
                    ->orWhere('matricule', 'LIKE', "%$query%");
            });
        }

        switch ($filter) {
            case 'this_day':
                $baseQuery->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
                break;
            case 'this_week':
                $baseQuery->where('created_at', '>=', Carbon::now()->startOfWeek());
                break;
            case 'this_month':
                $baseQuery->where('created_at', '>=', Carbon::now()->startOfMonth());
                break;
            case 'this_year':
                $baseQuery->where('created_at', '>=', Carbon::now()->startOfYear());
                break;
            case 'all':
            default:
                break;
        }

        $admins = $baseQuery->paginate($nbAffiche);
        $activeUsers = (clone $baseQuery)->where('status', 'activer')->paginate($nbAffiche);
        $inactiveUsers = (clone $baseQuery)->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedUsers = (clone $baseQuery)->where('status', 'supprimer')->paginate($nbAffiche);

        return view('admins.index', [
            'title' => 'Personnels',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'admin.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'admin.create'),
            'addButtonText' => 'Ajouter un Employé',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'admin.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $admins,
                'paid' => $activeUsers,
                'pending' => $inactiveUsers,
                'cancelled' => $deletedUsers,
            ],
            'tableHeaders' => $this->tableHeaders,
            'tableActions' => $this->tableActions,
            'routeName' => $this->routeName,
        ]);
    }

    public function createPDF(Request $request, $nbAffiche = 10)
    {
        $selectedView = $request->input('selectedView', 'orders-all-tab'); // Default to 'orders-all-tab' if not provided

        $query = $request->input('searchorders');
        $filter = $request->input('filter');

        // Base query with search filter
        $baseQuery = User::query();

        if ($query) {
            $baseQuery->where('libelle', 'LIKE', "%$query%");
        }

        // Apply date filter
        switch ($filter) {
            case 'this_day':
                $baseQuery->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
                break;
            case 'this_week':
                $baseQuery->where('created_at', '>=', Carbon::now()->startOfWeek());
                break;
            case 'this_month':
                $baseQuery->where('created_at', '>=', Carbon::now()->startOfMonth());
                break;
            case 'this_year':
                $baseQuery->where('created_at', '>=', Carbon::now()->startOfYear());
                break;
            case 'all':
            default:
                // Pas de filtrage supplémentaire
                break;
        }

        // Clone base query for each status
        $UsersQuery = clone $baseQuery;
        $activeUsersQuery = clone $baseQuery;
        $inactiveUsersQuery = clone $baseQuery;
        $deletedUsersQuery = clone $baseQuery;

        // Apply status filters
        $admins = match ($selectedView) {
            'orders-all-tab' => $UsersQuery->paginate($nbAffiche),
            'orders-paid-tab' => $activeUsersQuery->where('status', 'activer')->paginate($nbAffiche),
            'orders-pending-tab' => $inactiveUsersQuery->where('status', 'desactiver')->paginate($nbAffiche),
            'orders-cancelled-tab' => $deletedUsersQuery->where('status', 'supprimer')->paginate($nbAffiche),
        };

        // share data to view
        $pdf = PDF::loadView('admins.print', compact('admins'));

        // download PDF file with download method
        return $pdf->download('admins.pdf');
    }

    // Afficher le profil de l'utilisateur authentifié
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        // Valider et mettre à jour l'utilisateur
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            // autres champs que vous souhaitez mettre à jour...
        ]);

        $user->update($data);

        return response()->json(['message' => 'Profil mis à jour', 'user' => $user]);
    }
}
