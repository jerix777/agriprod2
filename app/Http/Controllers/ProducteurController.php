<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveProducteurRequest;
use App\Models\Producteur;
use App\Services\MigrationMetadataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProducteurController extends Controller
{
    protected $routeName = 'producteur';

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
        ['key' => 'nom', 'label' => 'Nom'],
        ['key' => 'prenoms', 'label' => 'Prénoms'],
        ['key' => 'date_de_naissance', 'label' => 'Âge', 'type' => 'age'],
        ['key' => 'contact', 'label' => 'Contact'],
        ['key' => 'lieu_de_residence', 'label' => 'Localité'],
        ['key' => 'producteur.parcelles', 'label' => 'Parcelles', 'type' => 'count'],
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
        ['id' => 'orders-all-tab', 'href' => '#orders-all', 'aria-controls' => 'orders-all', 'aria-selected' => 'true', 'label' => 'Tout le Producteur', 'active' => true],
        ['id' => 'orders-paid-tab', 'href' => '#orders-paid', 'aria-controls' => 'orders-paid', 'aria-selected' => 'false', 'label' => 'Activés', 'active' => false],
        ['id' => 'orders-pending-tab', 'href' => '#orders-pending', 'aria-controls' => 'orders-pending', 'aria-selected' => 'false', 'label' => 'Désactivés', 'active' => false],
        ['id' => 'orders-cancelled-tab', 'href' => '#orders-cancelled', 'aria-controls' => 'orders-cancelled', 'aria-selected' => 'false', 'label' => 'Supprimés', 'active' => false],
    ];

    public function index($nbAffiche = 10)
    {
        $producteurs = Producteur::with('parcelles')->paginate($nbAffiche);
        $activeProducteurs = Producteur::with('parcelles')->where('status', 'activer')->paginate($nbAffiche);
        $inactiveProducteurs = Producteur::with('parcelles')->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedProducteurs = Producteur::with('parcelles')->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('producteurs.index', [
            'title' => 'Producteurs',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'producteur.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'producteur.create'),
            'addButtonText' => 'Ajouter un Producteur',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'producteur.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $producteurs,
                'paid' => $activeProducteurs,
                'pending' => $inactiveProducteurs,
                'cancelled' => $deletedProducteurs,
            ],
            'tableHeaders' => $this->tableHeaders,
            'tableActions' => $this->tableActions,
            'routeName' => $this->routeName,
            // 'paginationLinks' => [
            //     'all' => $producteurs->links(),
            //     'paid' => $activeProducteurs->links(),
            //     'pending' => $inactiveProducteurs->links(),
            //     'cancelled' => $deletedProducteurs->links(),
            // ],
        ]);
    }

    public function create()
    {
        $routeName = $this->routeName;
        $pageTitle = 'Producteurs';
        $sectionTitle = 'Ajout';
        $sectionIntro = 'Enregistrer un nouveau Producteur ici.';

        $tableName = 'producteurs';
        $columns = $this->metadataService->getMigrationMetadata($tableName);

        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('producteurs.create', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function edit(Producteur $producteur)
    {
        $routeName = $this->routeName;
        $pageTitle = 'Producteurs';
        $sectionTitle = 'Ajout';
        $sectionIntro = 'Enregistrer un nouveau Producteur ici.';

        $tableName = 'producteurs';
        $columns = $this->metadataService->getMigrationMetadata($tableName);

        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('producteurs.edit', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function store(SaveProducteurRequest $request)
    {
        try {
            $producteur = new Producteur;
            $producteur->create($request->all());
            $message = 'Producteur enregistré avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'producteur.index')->with('success', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function update(int $id, SaveProducteurRequest $request)
    {
        try {
            $producteur = Producteur::findOrFail($id);
            $producteur->update($request->all());
            $message = 'Producteur mis à jour avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'producteur.index')->with('succes', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function delete($id)
    {
        try {
            Producteur::where('id', $id)->delete();
            $message = 'Producteur supprimé';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'producteur.index')->with('succes', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function search(Request $request, $nbAffiche = 10)
    {
        $query = $request->input('searchorders');
        $filter = $request->input('filter');

        $baseQuery = Producteur::query();

        if ($query) {
            $baseQuery->where('nom', 'LIKE', "%$query%");
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

        $producteurs = $baseQuery->paginate($nbAffiche);
        $activeProducteurs = clone $baseQuery->where('status', 'activer')->paginate($nbAffiche);
        $inactiveProducteurs = clone $baseQuery->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedProducteurs = clone $baseQuery->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('producteurs.index', [
            'title' => 'Producteurs',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'producteur.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'producteur.create'),
            'addButtonText' => 'Ajouter un Producteur',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'producteur.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $producteurs,
                'paid' => $activeProducteurs,
                'pending' => $inactiveProducteurs,
                'cancelled' => $deletedProducteurs,
            ],
            'tableHeaders' => $this->tableHeaders,
            'tableActions' => $this->tableActions,
            'routeName' => $this->routeName,
            // 'paginationLinks' => [
            //     'all' => $producteurs->links(),
            //     'paid' => $activeProducteurs->links(),
            //     'pending' => $inactiveProducteurs->links(),
            //     'cancelled' => $deletedProducteurs->links(),
            // ],
        ]);
    }

    public function print(Request $request, $nbAffiche = 10)
    {
        $query = $request->input('searchorders');
        $filter = $request->input('filter');

        $baseQuery = Producteur::query();

        if ($query) {
            $baseQuery->where('nom', 'LIKE', "%$query%");
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

        $producteurs = $baseQuery->paginate($nbAffiche);
        $activeProducteurs = clone $baseQuery->where('status', 'activer')->paginate($nbAffiche);
        $inactiveProducteurs = clone $baseQuery->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedProducteurs = clone $baseQuery->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('producteurs.index', [
            'title' => 'Producteurs',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'producteur.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'producteur.create'),
            'addButtonText' => 'Ajouter un Producteur',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'producteur.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $producteurs,
                'paid' => $activeProducteurs,
                'pending' => $inactiveProducteurs,
                'cancelled' => $deletedProducteurs,
            ],
            'tableHeaders' => $this->tableHeaders,
            'tableActions' => $this->tableActions,
            'routeName' => $this->routeName,
            // 'paginationLinks' => [
            //     'all' => $producteurs->links(),
            //     'paid' => $activeProducteurs->links(),
            //     'pending' => $inactiveProducteurs->links(),
            //     'cancelled' => $deletedProducteurs->links(),
            // ],
        ]);
    }

    public function createPDF(Request $request, $nbAffiche = 10)
    {
        $selectedView = $request->input('selectedView', 'orders-all-tab'); // Default to 'orders-all-tab' if not provided

        $query = $request->input('searchorders');
        $filter = $request->input('filter');

        // Base query with search filter
        $baseQuery = Producteur::query();

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
        $ProducteursQuery = clone $baseQuery;
        $activeProducteursQuery = clone $baseQuery;
        $inactiveProducteursQuery = clone $baseQuery;
        $deletedProducteursQuery = clone $baseQuery;

        // Apply status filters
        $producteurs = match ($selectedView) {
            'orders-all-tab' => $ProducteursQuery->paginate($nbAffiche),
            'orders-paid-tab' => $activeProducteursQuery->where('status', 'activer')->paginate($nbAffiche),
            'orders-pending-tab' => $inactiveProducteursQuery->where('status', 'desactiver')->paginate($nbAffiche),
            'orders-cancelled-tab' => $deletedProducteursQuery->where('status', 'supprimer')->paginate($nbAffiche),
        };

        // share data to view
        $pdf = PDF::loadView('producteurs.print', compact('producteurs'));

        // download PDF file with download method
        return $pdf->download('producteurs.pdf');
    }
}
