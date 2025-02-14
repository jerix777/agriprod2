<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveDepartementRequest;
use App\Models\Departement;
use App\Services\MigrationMetadataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartementController extends Controller
{
    protected $routeName = 'departement';

    protected $metadataService;

    private $roleName = '';

    protected $nbLines = 2;

    public function __construct(MigrationMetadataService $metadataService)
    {
        $this->metadataService = $metadataService;
        $this->roleName = strtolower(Auth::user()->getRoleNames()->first());
    }

    // Paramètres pour les en-têtes de table et les actions
    protected $tableHeaders = [
        ['key' => '#', 'label' => '#'],
        ['key' => 'id', 'label' => 'ID', 'hidden' => true],
        ['key' => 'libelle', 'label' => 'Département'],
        ['key' => 'postes', 'label' => 'Nombre de Postes', 'count' => true],
        ['key' => 'created_at', 'label' => 'Créé le', 'type' => 'date', 'format' => 'd M Y H:i:s'],
        ['key' => 'updated_at', 'label' => 'Modifié le', 'type' => 'date', 'format' => 'd M Y H:i:s'],
        ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
        ['key' => 'actions', 'label' => 'Actions'],
    ];

    protected $tableActions = [
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
    ];

    // Paramètres pour les onglets
    protected $tabs = [
        ['id' => 'orders-all-tab', 'href' => '#orders-all', 'aria-controls' => 'orders-all', 'aria-selected' => 'true', 'label' => 'Tous les Départements', 'active' => true],
        ['id' => 'orders-paid-tab', 'href' => '#orders-paid', 'aria-controls' => 'orders-paid', 'aria-selected' => 'false', 'label' => 'Activés', 'active' => false],
        ['id' => 'orders-pending-tab', 'href' => '#orders-pending', 'aria-controls' => 'orders-pending', 'aria-selected' => 'false', 'label' => 'Désactivés', 'active' => false],
        ['id' => 'orders-cancelled-tab', 'href' => '#orders-cancelled', 'aria-controls' => 'orders-cancelled', 'aria-selected' => 'false', 'label' => 'Supprimés', 'active' => false],
    ];

    public function index($nbAffiche = 10)
    {
        $departements = Departement::with('postes')->paginate($nbAffiche);
        $activeDepartements = Departement::with('postes')->where('status', 'activer')->paginate($nbAffiche);
        $inactiveDepartements = Departement::with('postes')->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedDepartements = Departement::with('postes')->where('status', 'supprimer')->paginate($nbAffiche);

        $this->roleName = strtolower(Auth::user()->getRoleNames()->first());
        // Passer les données et les paramètres à la vue
        return view($this->roleName . '.' . $this->routeName . 's.index', [
            'title' => 'Départements',
            'searchAction' => route($this->roleName . '.' . $this->routeName . '.search'),
            'addAction' => route($this->roleName . '.' . $this->routeName . '.create'),
            'addButtonText' => 'Ajouter un Département',
            'printAction' => route($this->roleName . '.' . $this->routeName . '.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $departements,
                'paid' => $activeDepartements,
                'pending' => $inactiveDepartements,
                'cancelled' => $deletedDepartements,
            ],
            'tableHeaders' => $this->tableHeaders,
            'tableActions' => $this->tableActions,
            'routeName' => $this->routeName,
            'roleName' => $this->roleName,
        ]);
    }

    public function create()
    {
        $routeName = $this->routeName;
        $pageTitle = 'Départements';
        $sectionTitle = 'Ajout';
        $sectionIntro = 'Enregistrer un nouveau Département ici.';

        $tableName = 'departements';
        $columns = $this->metadataService->getMigrationMetadata($tableName);

        $relatedData = $this->metadataService->getRelatedData($columns);

        return view($this->roleName . '.' . $this->routeName . 's.create', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function edit(Departement $departement)
    {
        $routeName = $this->routeName;
        $tableName = 'departements';
        $columns = $this->metadataService->getMigrationMetadata($tableName);

        $relatedData = $this->metadataService->getRelatedData($columns);

        return view($this->roleName . '.' . $this->routeName . 's.edit', compact('columns', 'routeName', 'relatedData', 'relatedData'));
    }

    public function store(SaveDepartementRequest $request)
    {
        try {
            $departement = new Departement;
            $departement->create($request->all());
            $message = 'Département enregistré avec succès';

            return redirect()->route($this->roleName . '.' . $this->routeName . '.index')->with('success', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function update(int $id, SaveDepartementRequest $request)
    {
        try {
            $departement = Departement::findOrFail($id);
            $departement->update($request->all());
            $message = 'Département mis à jour avec succès';

            return redirect()->route($this->roleName . '.' . $this->routeName . '.index')->with('success', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function delete($id)
    {
        try {
            Departement::where('id', $id)->delete();
            $message = 'Département supprimé avec succès';

            return redirect()->route($this->roleName . '.' . $this->routeName . '.index')->with('success', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function search(Request $request, $nbAffiche = 10)
    {
        $query = $request->input('searchorders');
        $filter = $request->input('filter');

        $baseQuery = Departement::query();

        if ($query) {
            $baseQuery->where('libelle', 'LIKE', "%$query%");
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

        $departements = clone $baseQuery->paginate($nbAffiche);
        $activeDepartements = clone $baseQuery->where('status', 'activer')->paginate($nbAffiche);
        $inactiveDepartements = clone $baseQuery->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedDepartements = clone $baseQuery->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view($this->roleName . '.' . $this->routeName . 's.index', [
            'title' => 'Departements',
            'searchAction' => route($this->roleName . '.' . $this->routeName . '.search'),
            'addAction' => route($this->roleName . '.' . $this->routeName . '.create'),
            'addButtonText' => 'Ajouter un Département',
            'printAction' => route($this->roleName . '.' . $this->routeName . '.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $departements,
                'paid' => $activeDepartements,
                'pending' => $inactiveDepartements,
                'cancelled' => $deletedDepartements,
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

        $baseQuery = Departement::query();

        if ($query) {
            $baseQuery->where('libelle', 'LIKE', "%$query%");
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

        $departements = clone $baseQuery->paginate($nbAffiche);
        $activeDepartements = clone $baseQuery->where('status', 'activer')->paginate($nbAffiche);
        $inactiveDepartements = clone $baseQuery->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedDepartements = clone $baseQuery->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view($this->roleName . '.' . $this->routeName . 's.index', [
            'title' => 'Departements',
            'searchAction' => route($this->roleName . '.' . $this->routeName . '.search'),
            'addAction' => route($this->roleName . '.' . $this->routeName . '.create'),
            'addButtonText' => 'Ajouter un Département',
            'printAction' => route($this->roleName . '.' . $this->routeName . '.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $departements,
                'paid' => $activeDepartements,
                'pending' => $inactiveDepartements,
                'cancelled' => $deletedDepartements,
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
        $baseQuery = Departement::query();

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
        $DepartementsQuery = clone $baseQuery;
        $activeDepartementsQuery = clone $baseQuery;
        $inactiveDepartementsQuery = clone $baseQuery;
        $deletedDepartementsQuery = clone $baseQuery;

        // Apply status filters
        $departements = match ($selectedView) {
            'orders-all-tab' => $DepartementsQuery->paginate($nbAffiche),
            'orders-paid-tab' => $activeDepartementsQuery->where('status', 'activer')->paginate($nbAffiche),
            'orders-pending-tab' => $inactiveDepartementsQuery->where('status', 'desactiver')->paginate($nbAffiche),
            'orders-cancelled-tab' => $deletedDepartementsQuery->where('status', 'supprimer')->paginate($nbAffiche),
        };

        // share data to view
        $pdf = PDF::loadView('departements.print', compact('departements'));

        // download PDF file with download method
        return $pdf->download('departements.pdf');
    }
}
