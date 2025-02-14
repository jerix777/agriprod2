<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveParcelleRequest;
use App\Models\Parcelle;
use App\Services\MigrationMetadataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParcelleController extends Controller
{
    protected $routeName = 'parcelle';

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
        ['key' => 'matricule', 'label' => 'Matricule'],
        ['key' => 'superficie', 'label' => 'Superficie (ha)'],
        ['key' => 'localisation', 'label' => 'Localisation'],
        ['key' => 'producteur.matricule', 'label' => 'Propriétaire'],
        // ['key' => 'created_at', 'label' => 'Créé le', 'type' => 'date', 'format' => 'd M Y H:i:s'],
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
        ['id' => 'orders-all-tab', 'href' => '#orders-all', 'aria-controls' => 'orders-all', 'aria-selected' => 'true', 'label' => 'Toutes les Parcelles', 'active' => true],
        ['id' => 'orders-paid-tab', 'href' => '#orders-paid', 'aria-controls' => 'orders-paid', 'aria-selected' => 'false', 'label' => 'Activés', 'active' => false],
        ['id' => 'orders-pending-tab', 'href' => '#orders-pending', 'aria-controls' => 'orders-pending', 'aria-selected' => 'false', 'label' => 'Désactivés', 'active' => false],
        ['id' => 'orders-cancelled-tab', 'href' => '#orders-cancelled', 'aria-controls' => 'orders-cancelled', 'aria-selected' => 'false', 'label' => 'Supprimés', 'active' => false],
    ];

    public function index($nbAffiche = 10)
    {
        $parcelles = Parcelle::with('producteur')->paginate($nbAffiche);
        $activeParcelles = Parcelle::with('producteur')->where('status', 'activer')->paginate($nbAffiche);
        $inactiveParcelles = Parcelle::with('producteur')->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedParcelles = Parcelle::with('producteur')->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('parcelles.index', [
            'title' => 'Parcelles',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'parcelle.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'parcelle.create'),
            'addButtonText' => 'Ajouter un Parcelle',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'parcelle.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $parcelles,
                'paid' => $activeParcelles,
                'pending' => $inactiveParcelles,
                'cancelled' => $deletedParcelles,
            ],
            'tableHeaders' => $this->tableHeaders,
            'tableActions' => $this->tableActions,
            'routeName' => $this->routeName,
        ]);
    }

    public function create()
    {
        $routeName = $this->routeName;
        $pageTitle = 'Parcelles';
        $sectionTitle = 'Ajout';
        $sectionIntro = 'Enregistrer un nouveau Parcelle ici.';

        $tableName = 'parcelles';
        $columns = $this->metadataService->getMigrationMetadata($tableName);

        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('parcelles.create', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function edit(Parcelle $parcelle)
    {
        $routeName = $this->routeName;
        $tableName = 'parcelles';
        $columns = $this->metadataService->getMigrationMetadata($tableName);

        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('parcelles.edit', compact('columns', 'routeName', 'routeName', 'relatedData'));
    }

    public function store(SaveParcelleRequest $request)
    {
        try {
            $parcelle = new Parcelle;
            $parcelle->create($request->all());
            $message = 'Parcelle enregistré avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'parcelle.index')->with('success', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function update(int $id, SaveParcelleRequest $request)
    {
        try {
            $parcelle = Parcelle::findOrFail($id);
            $parcelle->update($request->all());
            $message = 'Parcelle mis à jour avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'parcelle.index')->with('success', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function delete($id)
    {
        try {
            Parcelle::where('id', $id)->delete();
            $message = 'Parcelle supprimé avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'parcelle.index')->with('success', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function search(Request $request, $nbAffiche = 10)
    {
        $query = $request->input('searchorders');
        $filter = $request->input('filter');

        $baseQuery = Parcelle::with('parcelles')->query();

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

        $parcelles = clone $baseQuery->paginate($nbAffiche);
        $activeParcelles = clone $baseQuery->where('status', 'activer')->paginate($nbAffiche);
        $inactiveParcelles = clone $baseQuery->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedParcelles = clone $baseQuery->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('parcelles.index', [
            'title' => 'Parcelles',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'parcelle.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'parcelle.create'),
            'addButtonText' => 'Ajouter un Parcelle',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'parcelle.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $parcelles,
                'paid' => $activeParcelles,
                'pending' => $inactiveParcelles,
                'cancelled' => $deletedParcelles,
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

        $baseQuery = Parcelle::with('parcelles')->query();

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

        $parcelles = clone $baseQuery->paginate($nbAffiche);
        $activeParcelles = clone $baseQuery->where('status', 'activer')->paginate($nbAffiche);
        $inactiveParcelles = clone $baseQuery->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedParcelles = clone $baseQuery->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('parcelles.index', [
            'title' => 'Parcelles',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'parcelle.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'parcelle.create'),
            'addButtonText' => 'Ajouter un Parcelle',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'parcelle.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $parcelles,
                'paid' => $activeParcelles,
                'pending' => $inactiveParcelles,
                'cancelled' => $deletedParcelles,
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
        $baseQuery = Parcelle::query();

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
        $ParcellesQuery = clone $baseQuery;
        $activeParcellesQuery = clone $baseQuery;
        $inactiveParcellesQuery = clone $baseQuery;
        $deletedParcellesQuery = clone $baseQuery;

        // Apply status filters
        $parcelles = match ($selectedView) {
            'orders-all-tab' => $ParcellesQuery->paginate($nbAffiche),
            'orders-paid-tab' => $activeParcellesQuery->where('status', 'activer')->paginate($nbAffiche),
            'orders-pending-tab' => $inactiveParcellesQuery->where('status', 'desactiver')->paginate($nbAffiche),
            'orders-cancelled-tab' => $deletedParcellesQuery->where('status', 'supprimer')->paginate($nbAffiche),
        };

        // share data to view
        $pdf = PDF::loadView('parcelles.print', compact('parcelles'));

        // download PDF file with download method
        return $pdf->download('parcelles.pdf');
    }
}
