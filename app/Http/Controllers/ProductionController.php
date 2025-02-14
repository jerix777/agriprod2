<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveProductionRequest;
use App\Models\Production;
use App\Services\MigrationMetadataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductionController extends Controller
{
    protected $routeName = 'production';

    protected $tableName = 'productions';

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
        ['key' => 'campagne.annee', 'label' => 'Campagne'],
        ['key' => 'parcelle.reference', 'label' => 'Parcelle'],
        ['key' => 'parcelle.producteur.nom', 'label' => 'Producteur'],
        ['key' => 'quantite', 'label' => 'Quantité (Kg)'],
        ['key' => 'qualite', 'label' => 'Qualité (%)'],
        ['key' => 'date_de_production', 'label' => 'Date de Production', 'type' => 'date', 'format' => 'd M Y'],
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
        ['id' => 'orders-all-tab', 'href' => '#orders-all', 'aria-controls' => 'orders-all', 'aria-selected' => 'true', 'label' => 'Toutes les Productions', 'active' => true],
        ['id' => 'orders-paid-tab', 'href' => '#orders-paid', 'aria-controls' => 'orders-paid', 'aria-selected' => 'false', 'label' => 'Activés', 'active' => false],
        ['id' => 'orders-pending-tab', 'href' => '#orders-pending', 'aria-controls' => 'orders-pending', 'aria-selected' => 'false', 'label' => 'Désactivés', 'active' => false],
        ['id' => 'orders-cancelled-tab', 'href' => '#orders-cancelled', 'aria-controls' => 'orders-cancelled', 'aria-selected' => 'false', 'label' => 'Supprimés', 'active' => false],
    ];

    public function index($nbAffiche = 10)
    {
        $allProductions = Production::with('campagne', 'parcelle', 'parcelle.producteur')->paginate($nbAffiche);
        $activeProductions = Production::with('campagne', 'parcelle', 'parcelle.producteur')->where('status', 'activer')->paginate($nbAffiche);
        $inactiveProductions = Production::with('campagne', 'parcelle', 'parcelle.producteur')->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedProductions = Production::with('campagne', 'parcelle', 'parcelle.producteur')->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('productions.index', [
            'title' => 'Productions',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'production.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'production.create'),
            'addButtonText' => 'Ajouter une Production',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'production.preview'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $allProductions,
                'paid' => $activeProductions,
                'pending' => $inactiveProductions,
                'cancelled' => $deletedProductions,
            ],
            'tableHeaders' => $this->tableHeaders,
            'tableActions' => $this->tableActions,
            'routeName' => $this->routeName,
        ]);
    }

    public function create()
    {
        $routeName = $this->routeName;
        $pageTitle = 'Productions';
        $sectionTitle = 'Ajout';
        $sectionIntro = 'Enregistrer une nouvelle Production ici.';

        $tableName = $this->tableName;
        $columns = $this->metadataService->getMigrationMetadata($this->tableName);
        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('productions.create', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function edit(Production $Production)
    {
        $routeName = $this->routeName;
        $pageTitle = 'Productions';
        $sectionTitle = 'Modification';
        $sectionIntro = 'Modifier une Production ici.';

        $tableName = $this->tableName;
        $columns = $this->metadataService->getMigrationMetadata($this->tableName);
        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('productions.edit', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function store(SaveProductionRequest $request)
    {
        try {
            $production = new Production;
            $production->create($request->all());
            $message = 'Production enregistrée avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'production.index')->with('success', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function update(SaveProductionRequest $request, int $id)
    {
        try {
            $production = Production::findOrFail($id);
            $production->update($request->all());
            $message = 'Production mise à jour avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'production.index')->with('succes', $message);
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
        $baseQuery = Production::query();

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
        $ProductionsQuery = clone $baseQuery;
        $activeProductionsQuery = clone $baseQuery;
        $inactiveProductionsQuery = clone $baseQuery;
        $deletedProductionsQuery = clone $baseQuery;

        // Apply status filters
        $allProductions = $ProductionsQuery->paginate($nbAffiche);
        $activeProductions = $activeProductionsQuery->where('status', 'activer')->paginate($nbAffiche);
        $inactiveProductions = $inactiveProductionsQuery->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedProductions = $deletedProductionsQuery->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('productions.index', [
            'title' => 'Productions',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'production.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'production.create'),
            'addButtonText' => 'Ajouter une Production',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'production.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $allProductions,
                'paid' => $activeProductions,
                'pending' => $inactiveProductions,
                'cancelled' => $deletedProductions,
            ],
            'tableHeaders' => $this->tableHeaders,
            'tableActions' => $this->tableActions,
            'routeName' => $this->routeName,
        ]);
    }

    public function print(Request $request, $nbAffiche = 10)
    {
        $selectedView = $request->input('selectedView', 'orders-all-tab'); // Default to 'orders-all-tab' if not provided

        $query = $request->input('searchorders');
        $filter = $request->input('filter');

        // Base query with search filter
        $baseQuery = Production::query();

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
        $ProductionsQuery = clone $baseQuery;
        $activeProductionsQuery = clone $baseQuery;
        $inactiveProductionsQuery = clone $baseQuery;
        $deletedProductionsQuery = clone $baseQuery;

        // Apply status filters
        $productions = match ($selectedView) {
            'orders-all-tab' => $ProductionsQuery->paginate($nbAffiche),
            'orders-paid-tab' => $activeProductionsQuery->where('status', 'activer')->paginate($nbAffiche),
            'orders-pending-tab' => $inactiveProductionsQuery->where('status', 'desactiver')->paginate($nbAffiche),
            'orders-cancelled-tab' => $deletedProductionsQuery->where('status', 'supprimer')->paginate($nbAffiche),
        };

        return view('productions.print', compact('productions'));
    }

    // Generate PDF
    public function previewPDF(Request $request)
    {
        $selectedView = $request->input('selectedView', 'orders-all-tab'); // Default to 'orders-all-tab' if not provided

        $query = $request->input('searchorders');
        $filter = $request->input('filter');

        // Base query with search filter
        $baseQuery = Production::query();

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
        $ProductionsQuery = clone $baseQuery;
        $activeProductionsQuery = clone $baseQuery;
        $inactiveProductionsQuery = clone $baseQuery;
        $deletedProductionsQuery = clone $baseQuery;

        // Apply status filters
        $productions = match ($selectedView) {
            'orders-all-tab' => $ProductionsQuery->paginate(0),
            'orders-paid-tab' => $activeProductionsQuery->where('status', 'activer')->paginate(0),
            'orders-pending-tab' => $inactiveProductionsQuery->where('status', 'desactiver')->paginate(0),
            'orders-cancelled-tab' => $deletedProductionsQuery->where('status', 'supprimer')->paginate(0),
        };

        // Configurer les options de Dompdf sous forme de tableau
        $options = [
            'defaultFont' => 'Arial',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ];

        // Charger la vue PDF et générer le PDF
        $pdf = PDF::loadView('productions.print', compact('productions'))
            ->setOptions($options)
            ->setPaper('A4', 'portrait'); // Configurer le papier en format A4

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="productions.pdf"');
    }
}
