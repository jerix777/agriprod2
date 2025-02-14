<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveCultureRequest;
use App\Models\Culture;
use App\Services\MigrationMetadataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CultureController extends Controller
{
    protected $routeName = 'culture';

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
        ['key' => 'identifiant_unique', 'label' => 'UID'],
        ['key' => 'nom_commun', 'label' => 'Nom commun'],
        ['key' => 'nom_scientifique', 'label' => 'Nom scientifique'],
        ['key' => 'parcelles', 'label' => 'Parcelles cultivées'],
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
        ['id' => 'orders-all-tab', 'href' => '#orders-all', 'aria-controls' => 'orders-all', 'aria-selected' => 'true', 'label' => 'Toutes les Cultures', 'active' => true],
        ['id' => 'orders-paid-tab', 'href' => '#orders-paid', 'aria-controls' => 'orders-paid', 'aria-selected' => 'false', 'label' => 'Activés', 'active' => false],
        ['id' => 'orders-pending-tab', 'href' => '#orders-pending', 'aria-controls' => 'orders-pending', 'aria-selected' => 'false', 'label' => 'Désactivés', 'active' => false],
        ['id' => 'orders-cancelled-tab', 'href' => '#orders-cancelled', 'aria-controls' => 'orders-cancelled', 'aria-selected' => 'false', 'label' => 'Supprimés', 'active' => false],
    ];

    public function index($nbAffiche = 10)
    {
        $cultures = Culture::paginate($nbAffiche);
        $activeCultures = Culture::where('status', 'activer')->paginate($nbAffiche);
        $inactiveCultures = Culture::where('status', 'desactiver')->paginate($nbAffiche);
        $deletedCultures = Culture::where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('cultures.index', [
            'title' => 'Cultures',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'culture.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'culture.create'),
            'addButtonText' => 'Ajouter une culture',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'culture.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $cultures,
                'paid' => $activeCultures,
                'pending' => $inactiveCultures,
                'cancelled' => $deletedCultures,
            ],
            'tableHeaders' => $this->tableHeaders,
            'tableActions' => $this->tableActions,
            'routeName' => $this->routeName,
        ]);
    }

    public function create()
    {
        $routeName = $this->routeName;
        $pageTitle = 'Cultures';
        $sectionTitle = 'Ajout';
        $sectionIntro = 'Enregistrer un nouveau Culture ici.';

        $tableName = 'cultures';
        $columns = $this->metadataService->getMigrationMetadata($tableName);
        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('cultures.create', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function edit(Culture $Culture)
    {
        $routeName = $this->routeName;
        $pageTitle = 'Cultures';
        $sectionTitle = 'Modification';
        $sectionIntro = 'Modifier le Culture ici.';

        $tableName = 'cultures';
        $columns = $this->metadataService->getMigrationMetadata($tableName);
        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('cultures.edit', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function store(SaveCultureRequest $request)
    {
        try {
            $culture = new Culture;
            $culture->create($request->all());
            $message = 'Culture enregistré avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'culture.index')->with('success', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function update(int $id, SaveCultureRequest $request)
    {
        try {
            $culture = Culture::findOrFail($id);
            $culture->update($request->all());
            $message = 'Culture mis à jour avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'culture.index')->with('succes', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function delete($id)
    {
        try {
            Culture::where('id', $id)->delete();
            $message = 'Culture supprimée';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'culture.index')->with('succes', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function search(Request $request, $nbAffiche = 10)
    {
        $query = $request->input('searchorders');
        $filter = $request->input('filter');

        $baseQuery = Culture::query();

        if ($query) {
            $baseQuery->where('nom_propre', 'LIKE', "%$query%");
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

        $cultures = $baseQuery->paginate($nbAffiche);
        $activeCultures = clone $baseQuery->where('status', 'activer')->paginate($nbAffiche);
        $inactiveCultures = clone $baseQuery->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedCultures = clone $baseQuery->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('cultures.index', [
            'title' => 'Cultures',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'culture.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'culture.create'),
            'addButtonText' => 'Ajouter une culture',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'culture.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $cultures,
                'paid' => $activeCultures,
                'pending' => $inactiveCultures,
                'cancelled' => $deletedCultures,
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

        $baseQuery = Culture::query();

        if ($query) {
            $baseQuery->where('nom_propre', 'LIKE', "%$query%");
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

        $cultures = $baseQuery->paginate($nbAffiche);
        $activeCultures = clone $baseQuery->where('status', 'activer')->paginate($nbAffiche);
        $inactiveCultures = clone $baseQuery->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedCultures = clone $baseQuery->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('cultures.index', [
            'title' => 'Cultures',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'culture.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'culture.create'),
            'addButtonText' => 'Ajouter une culture',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'culture.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $cultures,
                'paid' => $activeCultures,
                'pending' => $inactiveCultures,
                'cancelled' => $deletedCultures,
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
        $baseQuery = Culture::query();

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
        $CulturesQuery = clone $baseQuery;
        $activeCulturesQuery = clone $baseQuery;
        $inactiveCulturesQuery = clone $baseQuery;
        $deletedCulturesQuery = clone $baseQuery;

        // Apply status filters
        $cultures = match ($selectedView) {
            'orders-all-tab' => $CulturesQuery->paginate($nbAffiche),
            'orders-paid-tab' => $activeCulturesQuery->where('status', 'activer')->paginate($nbAffiche),
            'orders-pending-tab' => $inactiveCulturesQuery->where('status', 'desactiver')->paginate($nbAffiche),
            'orders-cancelled-tab' => $deletedCulturesQuery->where('status', 'supprimer')->paginate($nbAffiche),
            default => 'Unknown type', // Gérer les types inattendus
        };

        // share data to view
        $pdf = PDF::loadView('cultures.print', compact('cultures'));

        // download PDF file with download method
        return $pdf->download('cultures.pdf');
    }
}
