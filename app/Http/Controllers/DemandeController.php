<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveDemandeRequest;
use App\Models\Demande;
use App\Services\MigrationMetadataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemandeController extends Controller
{
    protected $routeName = 'demande';

    protected $tableName = 'demandes';

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
        ['key' => 'poste.employer.matricule', 'label' => 'Matricule'],
        ['key' => 'employer.reference', 'label' => 'Parcelle'],
        ['key' => 'poste.employer.nom', 'label' => 'Producteur'],
        ['key' => 'quantite', 'label' => 'Quantité (Kg)'],
        ['key' => 'qualite', 'label' => 'Qualité (%)'],
        ['key' => 'date_de_demande', 'label' => 'Date de Demande', 'type' => 'date', 'format' => 'd M Y'],
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
        ['id' => 'orders-all-tab', 'href' => '#orders-all', 'aria-controls' => 'orders-all', 'aria-selected' => 'true', 'label' => 'Toutes les Demandes', 'active' => true],
        ['id' => 'orders-paid-tab', 'href' => '#orders-paid', 'aria-controls' => 'orders-paid', 'aria-selected' => 'false', 'label' => 'En attente', 'active' => false],
        ['id' => 'orders-pending-tab', 'href' => '#orders-pending', 'aria-controls' => 'orders-pending', 'aria-selected' => 'false', 'label' => 'Validées', 'active' => false],
        ['id' => 'orders-cancelled-tab', 'href' => '#orders-cancelled', 'aria-controls' => 'orders-cancelled', 'aria-selected' => 'false', 'label' => 'Supprimées', 'active' => false],
    ];

    /**
     * Display a listing of the resource.
     */
    public function index($nbAffiche = 10)
    {
        $allDemandes = Demande::with('poste', 'employer', 'poste.employer')->paginate($nbAffiche);
        $activeDemandes = Demande::with('poste', 'employer', 'poste.employer')->where('status', 'activer')->paginate($nbAffiche);
        $inactiveDemandes = Demande::with('poste', 'employer', 'poste.employer')->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedDemandes = Demande::with('poste', 'employer', 'poste.employer')->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('demandes.index', [
            'title' => 'Demandes',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'demande.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'demande.create'),
            'addButtonText' => 'Ajouter une Demande',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'demande.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $allDemandes,
                'paid' => $activeDemandes,
                'pending' => $inactiveDemandes,
                'cancelled' => $deletedDemandes,
            ],
            'tableHeaders' => $this->tableHeaders,
            'tableActions' => $this->tableActions,
            'routeName' => $this->routeName,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $routeName = $this->routeName;
        $pageTitle = 'Demandes';
        $sectionTitle = 'Ajout';
        $sectionIntro = 'Enregistrer une nouvelle Demande ici.';

        $tableName = $this->tableName;
        $columns = $this->metadataService->getMigrationMetadata($this->tableName);
        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('demandes.create', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SaveDemandeRequest $request)
    {
        try {
            $demande = new Demande;
            $demande->create($request->all());
            $message = 'Demande enregistrée avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'demande.index')->with('success', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Demande $Demande)
    {
        $routeName = $this->routeName;
        $pageTitle = 'Demandes';
        $sectionTitle = 'Modification';
        $sectionIntro = 'Modifier une Demande ici.';

        $tableName = $this->tableName;
        $columns = $this->metadataService->getMigrationMetadata($this->tableName);
        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('demandes.edit', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SaveDemandeRequest $request, int $id)
    {
        try {
            $demande = Demande::findOrFail($id);
            $demande->update($request->all());
            $message = 'Demande mise à jour avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'demande.index')->with('succes', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function search(Request $request, $nbAffiche = 10)
    {
        $query = $request->input('searchorders');
        $filter = $request->input('filter');

        // Base query with search filter
        $baseQuery = Demande::query();

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
        $DemandesQuery = clone $baseQuery;
        $activeDemandesQuery = clone $baseQuery;
        $inactiveDemandesQuery = clone $baseQuery;
        $deletedDemandesQuery = clone $baseQuery;

        // Apply status filters
        $allDemandes = $DemandesQuery->paginate($nbAffiche);
        $activeDemandes = $activeDemandesQuery->where('status', 'activer')->paginate($nbAffiche);
        $inactiveDemandes = $inactiveDemandesQuery->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedDemandes = $deletedDemandesQuery->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('demandes.index', [
            'title' => 'Demandes',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'demande.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'demande.create'),
            'addButtonText' => 'Ajouter une Demande',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'demande.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $allDemandes,
                'paid' => $activeDemandes,
                'pending' => $inactiveDemandes,
                'cancelled' => $deletedDemandes,
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
        $baseQuery = Demande::query();

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
        $DemandesQuery = clone $baseQuery;
        $activeDemandesQuery = clone $baseQuery;
        $inactiveDemandesQuery = clone $baseQuery;
        $deletedDemandesQuery = clone $baseQuery;

        // Apply status filters
        $allDemandes = $DemandesQuery->paginate($nbAffiche);
        $activeDemandes = $activeDemandesQuery->where('status', 'activer')->paginate($nbAffiche);
        $inactiveDemandes = $inactiveDemandesQuery->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedDemandes = $deletedDemandesQuery->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('demandes.index', [
            'title' => 'Demandes',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'demande.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'demande.create'),
            'addButtonText' => 'Ajouter une Demande',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'demande.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $allDemandes,
                'paid' => $activeDemandes,
                'pending' => $inactiveDemandes,
                'cancelled' => $deletedDemandes,
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
        $baseQuery = Demande::query();

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
        $DemandesQuery = clone $baseQuery;
        $activeDemandesQuery = clone $baseQuery;
        $inactiveDemandesQuery = clone $baseQuery;
        $deletedDemandesQuery = clone $baseQuery;

        // Apply status filters
        $demandes = match ($selectedView) {
            'orders-all-tab' => $DemandesQuery->paginate($nbAffiche),
            'orders-paid-tab' => $activeDemandesQuery->where('status', 'activer')->paginate($nbAffiche),
            'orders-pending-tab' => $inactiveDemandesQuery->where('status', 'desactiver')->paginate($nbAffiche),
            'orders-cancelled-tab' => $deletedDemandesQuery->where('status', 'supprimer')->paginate($nbAffiche),
        };

        // share data to view
        $pdf = PDF::loadView('demandes.print', compact('demandes'));

        // download PDF file with download method
        return $pdf->download('demandes.pdf');
    }
}
