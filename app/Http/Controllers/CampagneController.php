<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveCampagneRequest;
use App\Models\Campagne;
use App\Services\MigrationMetadataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampagneController extends Controller
{
    protected $routeName = 'campagne';

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
        ['key' => 'annee', 'label' => 'Année'],
        ['key' => 'theme', 'label' => 'Thème'],
        ['key' => 'campagne.productions.parcelles', 'label' => 'Nombre de producteurs'],
        ['key' => 'campagne.productions', 'label' => 'Nombre de parcelles'],
        ['key' => 'campagne.productions.quantite', 'label' => 'Production totale', 'sum' => true],
        ['key' => 'date_debut', 'label' => 'Ouverture Le', 'type' => 'date'],
        ['key' => 'date_fin', 'label' => 'Fermeture Le', 'type' => 'date'],
        // ['key' => 'created_at', 'label' => 'Créé le', 'type' => 'date', 'format' => 'd M Y H:i:s'],
        // ['key' => 'updated_at', 'label' => 'Modifié le', 'type' => 'date', 'format' => 'd M Y H:i:s'],
        ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
        ['key' => 'actions', 'label' => 'Actions'],
    ];

    protected $tableActions = [
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
    ];

    // Paramètres pour les onglets
    protected $tabs = [
        ['id' => 'orders-all-tab', 'href' => '#orders-all', 'aria-controls' => 'orders-all', 'aria-selected' => 'true', 'label' => 'Toutes les Campagnes', 'active' => true],
        ['id' => 'orders-paid-tab', 'href' => '#orders-paid', 'aria-controls' => 'orders-paid', 'aria-selected' => 'false', 'label' => 'Activés', 'active' => false],
        ['id' => 'orders-pending-tab', 'href' => '#orders-pending', 'aria-controls' => 'orders-pending', 'aria-selected' => 'false', 'label' => 'Désactivés', 'active' => false],
        ['id' => 'orders-cancelled-tab', 'href' => '#orders-cancelled', 'aria-controls' => 'orders-cancelled', 'aria-selected' => 'false', 'label' => 'Supprimés', 'active' => false],
    ];

    public function index($nbAffiche = 10)
    {
        $campagnes = Campagne::with('productions', 'productions.parcelle', 'productions.parcelle.producteur')->paginate($nbAffiche);
        $activeCampagnes = Campagne::with('productions')->where('status', 'activer')->paginate($nbAffiche);
        $inactiveCampagnes = Campagne::with('productions')->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedCampagnes = Campagne::with('productions')->where('status', 'supprimer')->paginate($nbAffiche);

        return view('campagnes.index', [
            'title' => 'Camapagnes',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'campagne.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'campagne.create'),
            'addButtonText' => 'Ajouter une Campagne',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'campagne.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $campagnes,
                'paid' => $activeCampagnes,
                'pending' => $inactiveCampagnes,
                'cancelled' => $deletedCampagnes,
            ],
            'tableHeaders' => $this->tableHeaders,
            'tableActions' => $this->tableActions,
            'routeName' => $this->routeName,
        ]);
    }

    public function create()
    {
        $routeName = $this->routeName;
        $pageTitle = 'Campagnes';
        $sectionTitle = 'Ajout';
        $sectionIntro = 'Enregistrer un nouveau Campagne ici.';

        $tableName = 'campagnes';
        $columns = $this->metadataService->getMigrationMetadata($tableName);
        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('campagnes.create', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function edit(Campagne $Campagne)
    {
        $routeName = $this->routeName;
        $pageTitle = 'Campagnes';
        $sectionTitle = 'Modification';
        $sectionIntro = 'Modifier la Campagne ici.';

        $tableName = 'campagnes';
        $columns = $this->metadataService->getMigrationMetadata($tableName);
        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('campagnes.edit', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function store(SaveCampagneRequest $request)
    {
        try {
            $campagne = new Campagne;
            $campagne->create($request->all());
            $message = 'Campagne enregistrée avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'campagne.index')->with('success', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function update(int $id, SaveCampagneRequest $request)
    {
        try {
            $campagne = Campagne::findOrFail($id);
            $campagne->update($request->all());
            $message = 'Campagne mis à jour avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'campagne.index')->with('succes', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function delete($id)
    {
        try {
            Campagne::where('id', $id)->delete();
            $message = 'Campagne supprimé avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'campagne.index')->with('succes', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function search(Request $request, $nbAffiche = 10)
    {
        $query = $request->input('searchorders');
        $filter = $request->input('filter');

        $baseQuery = Campagne::with('productions');

        if ($query) {
            $baseQuery->where(function ($q) use ($query) {
                $q->where('annee', 'LIKE', "%$query%")
                    ->orWhere('theme', 'LIKE', "%$query%");
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

        $campagnes = (clone $baseQuery)->paginate($nbAffiche);
        $activeCampagnes = (clone $baseQuery)->where('status', 'activer')->paginate($nbAffiche);
        $inactiveCampagnes = (clone $baseQuery)->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedCampagnes = (clone $baseQuery)->where('status', 'supprimer')->paginate($nbAffiche);

        return view('campagnes.index', [
            'title' => 'Camapagnes',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'campagne.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'campagne.create'),
            'addButtonText' => 'Ajouter une Campagne',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'campagne.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $campagnes,
                'paid' => $activeCampagnes,
                'pending' => $inactiveCampagnes,
                'cancelled' => $deletedCampagnes,
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

        $baseQuery = Campagne::with('productions');

        if ($query) {
            $baseQuery->where(function ($q) use ($query) {
                $q->where('annee', 'LIKE', "%$query%")
                    ->orWhere('theme', 'LIKE', "%$query%");
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

        $campagnes = (clone $baseQuery)->paginate($nbAffiche);
        $activeCampagnes = (clone $baseQuery)->where('status', 'activer')->paginate($nbAffiche);
        $inactiveCampagnes = (clone $baseQuery)->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedCampagnes = (clone $baseQuery)->where('status', 'supprimer')->paginate($nbAffiche);

        return view('campagnes.index', [
            'title' => 'Camapagnes',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'campagne.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'campagne.create'),
            'addButtonText' => 'Ajouter une Campagne',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'campagne.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $campagnes,
                'paid' => $activeCampagnes,
                'pending' => $inactiveCampagnes,
                'cancelled' => $deletedCampagnes,
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
        $baseQuery = Campagne::query();

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
        $CampagnesQuery = clone $baseQuery;
        $activeCampagnesQuery = clone $baseQuery;
        $inactiveCampagnesQuery = clone $baseQuery;
        $deletedCampagnesQuery = clone $baseQuery;

        // Apply status filters
        $campagnes = match ($selectedView) {
            'orders-all-tab' => $CampagnesQuery->paginate($nbAffiche),
            'orders-paid-tab' => $activeCampagnesQuery->where('status', 'activer')->paginate($nbAffiche),
            'orders-pending-tab' => $inactiveCampagnesQuery->where('status', 'desactiver')->paginate($nbAffiche),
            'orders-cancelled-tab' => $deletedCampagnesQuery->where('status', 'supprimer')->paginate($nbAffiche),
            default => 'Unknown type', // Gérer les types inattendus
        };

        // share data to view
        $pdf = PDF::loadView('campagnes.print', compact('campagnes'));

        // download PDF file with download method
        return $pdf->download('campagnes.pdf');
    }
}
