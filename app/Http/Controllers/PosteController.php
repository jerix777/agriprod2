<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePosteRequest;
use App\Models\Poste;
use App\Services\MigrationMetadataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosteController extends Controller
{
    protected $routeName = 'poste';

    protected $metadataService;

    protected $nbLines = 2;

    public function __construct(MigrationMetadataService $metadataService)
    {
        $this->metadataService = $metadataService;
    }

    // Paramètres pour les en-têtes de table et les actions
    protected $tableHeaders = [
        ['key' => '#', 'label' => '#'],
        ['key' => 'id', 'label' => 'ID', 'hidden' => true],
        ['key' => 'nom_reseau', 'label' => 'Nom Réseau'],
        ['key' => 'numero_de_serie', 'label' => 'Numéro de Série'],
        ['key' => 'description', 'label' => 'Description'],
        ['key' => 'departement.libelle', 'label' => 'Département'],
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
        ['id' => 'orders-all-tab', 'href' => '#orders-all', 'aria-controls' => 'orders-all', 'aria-selected' => 'true', 'label' => 'Tout les Postes', 'active' => true],
        ['id' => 'orders-paid-tab', 'href' => '#orders-paid', 'aria-controls' => 'orders-paid', 'aria-selected' => 'false', 'label' => 'Activés', 'active' => false],
        ['id' => 'orders-pending-tab', 'href' => '#orders-pending', 'aria-controls' => 'orders-pending', 'aria-selected' => 'false', 'label' => 'Désactivés', 'active' => false],
        ['id' => 'orders-cancelled-tab', 'href' => '#orders-cancelled', 'aria-controls' => 'orders-cancelled', 'aria-selected' => 'false', 'label' => 'Supprimés', 'active' => false],
    ];

    public function index($nbAffiche = 10)
    {
        // $departements = Departement::where('status', 'activer')->get();

        $postes = Poste::with('departement')->paginate($nbAffiche);
        $activePostes = Poste::with('departement')->where('status', 'activer')->paginate($nbAffiche);
        $inactivePostes = Poste::with('departement')->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedPostes = Poste::with('departement')->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('postes.index', [
            'title' => 'Postes',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'poste.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'poste.create'),
            'addButtonText' => 'Ajouter un Poste',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'poste.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $postes,
                'paid' => $activePostes,
                'pending' => $inactivePostes,
                'cancelled' => $deletedPostes,
            ],
            'tableHeaders' => $this->tableHeaders,
            'tableActions' => $this->tableActions,
            'routeName' => $this->routeName,
        ]);
    }

    public function create()
    {
        $routeName = $this->routeName;
        $pageTitle = 'Postes';
        $sectionTitle = 'Ajout';
        $sectionIntro = 'Enregistrer un nouveau Poste ici.';

        $tableName = 'postes';
        $columns = $this->metadataService->getMigrationMetadata($tableName);
        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('postes.create', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function edit(Poste $Poste)
    {
        $routeName = $this->routeName;
        $pageTitle = 'Postes';
        $sectionTitle = 'Modification';
        $sectionIntro = 'Modifier le Poste ici.';

        $tableName = 'postes';
        $columns = $this->metadataService->getMigrationMetadata($tableName);
        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('postes.edit', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function store(SavePosteRequest $request)
    {
        try {
            $poste = new Poste;
            $poste->create($request->all());
            $message = 'Poste enregistré avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'poste.index')->with('success', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function update(int $id, SavePosteRequest $request)
    {
        try {
            $poste = Poste::findOrFail($id);
            $poste->update($request->all());
            $message = 'Poste mis à jour avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'poste.index')->with('success', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function search(Request $request, $nbAffiche = 10)
    {
        $query = $request->input('searchorders');
        $filter = $request->input('filter');

        // Base query with search filter
        $baseQuery = Poste::with('departement');

        if ($query) {
            $baseQuery->where('numero_de_serie', 'LIKE', "%$query%");
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

        // $departements = Departement::where('status', 'activer')->get();

        // Clone base query for each status
        $postesQuery = clone $baseQuery;
        $activePostesQuery = clone $baseQuery;
        $inactivePostesQuery = clone $baseQuery;
        $deletedPostesQuery = clone $baseQuery;

        // Apply status filters
        $postes = $postesQuery->paginate($nbAffiche);
        $activePostes = $activePostesQuery->where('status', 'activer')->paginate($nbAffiche);
        $inactivePostes = $inactivePostesQuery->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedPostes = $deletedPostesQuery->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('postes.index', [
            'title' => 'Postes',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'poste.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'poste.create'),
            'addButtonText' => 'Ajouter poste',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'poste.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $postes,
                'paid' => $activePostes,
                'pending' => $inactivePostes,
                'cancelled' => $deletedPostes,
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

        // Base query with search filter
        $baseQuery = Poste::with('departement');

        if ($query) {
            $baseQuery->where('numero_de_serie', 'LIKE', "%$query%");
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

        // $departements = Departement::where('status', 'activer')->get();

        // Clone base query for each status
        $postesQuery = clone $baseQuery;
        $activePostesQuery = clone $baseQuery;
        $inactivePostesQuery = clone $baseQuery;
        $deletedPostesQuery = clone $baseQuery;

        // Apply status filters
        $postes = $postesQuery->paginate($nbAffiche);
        $activePostes = $activePostesQuery->where('status', 'activer')->paginate($nbAffiche);
        $inactivePostes = $inactivePostesQuery->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedPostes = $deletedPostesQuery->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('postes.index', [
            'title' => 'Postes',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'poste.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'poste.create'),
            'addButtonText' => 'Ajouter poste',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'poste.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $postes,
                'paid' => $activePostes,
                'pending' => $inactivePostes,
                'cancelled' => $deletedPostes,
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
        $baseQuery = Poste::query();

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
        $PostesQuery = clone $baseQuery;
        $activePostesQuery = clone $baseQuery;
        $inactivePostesQuery = clone $baseQuery;
        $deletedPostesQuery = clone $baseQuery;

        // Apply status filters
        $postes = match ($selectedView) {
            'orders-all-tab' => $PostesQuery->paginate($nbAffiche),
            'orders-paid-tab' => $activePostesQuery->where('status', 'activer')->paginate($nbAffiche),
            'orders-pending-tab' => $inactivePostesQuery->where('status', 'desactiver')->paginate($nbAffiche),
            'orders-cancelled-tab' => $deletedPostesQuery->where('status', 'supprimer')->paginate($nbAffiche),
        };

        // share data to view
        $pdf = PDF::loadView('postes.print', compact('postes'));

        // download PDF file with download method
        return $pdf->download('postes.pdf');
    }
}
