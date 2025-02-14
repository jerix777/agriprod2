<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveGenreRequest;
use App\Models\Genre;
use App\Services\MigrationMetadataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GenreController extends Controller
{
    protected $routeName = 'genre';

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
        ['key' => 'nom_prenoms', 'label' => 'Nom & Prénoms'],
        ['key' => 'date_de_naissance', 'label' => 'Âge', 'type' => 'age'],
        ['key' => 'contact', 'label' => 'Contact'],
        ['key' => 'lieu_de_residence', 'label' => 'Localité'],
        ['key' => 'parcelles', 'label' => 'Parcelles'],
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
        ['id' => 'orders-all-tab', 'href' => '#orders-all', 'aria-controls' => 'orders-all', 'aria-selected' => 'true', 'label' => 'Tout le Campagne', 'active' => true],
        ['id' => 'orders-paid-tab', 'href' => '#orders-paid', 'aria-controls' => 'orders-paid', 'aria-selected' => 'false', 'label' => 'Activés', 'active' => false],
        ['id' => 'orders-pending-tab', 'href' => '#orders-pending', 'aria-controls' => 'orders-pending', 'aria-selected' => 'false', 'label' => 'Désactivés', 'active' => false],
        ['id' => 'orders-cancelled-tab', 'href' => '#orders-cancelled', 'aria-controls' => 'orders-cancelled', 'aria-selected' => 'false', 'label' => 'Supprimés', 'active' => false],
    ];

    public function index($nbAffiche = 10)
    {
        $genres = Genre::paginate($nbAffiche);
        $activeGenres = Genre::where('status', 'activer')->paginate($nbAffiche);
        $inactiveGenres = Genre::where('status', 'desactiver')->paginate($nbAffiche);
        $deletedGenres = Genre::where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('genres.index', [
            'title' => 'Genres',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'genre.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'genre.create'),
            'addButtonText' => 'Ajouter genre',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'genre.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $genres,
                'paid' => $activeGenres,
                'pending' => $inactiveGenres,
                'cancelled' => $deletedGenres,
            ],
            'tableHeaders' => $this->tableHeaders,
            'tableActions' => $this->tableActions,
            'routeName' => $this->routeName,
        ]);
    }

    public function create()
    {
        $routeName = $this->routeName;
        $pageTitle = 'Genres';
        $sectionTitle = 'Ajout';
        $sectionIntro = 'Enregistrer un nouveau Genre ici.';

        $tableName = 'genres';
        $columns = $this->metadataService->getMigrationMetadata($tableName);
        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('genres.create', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function edit(Genre $Genre)
    {
        $routeName = $this->routeName;
        $pageTitle = 'Genres';
        $sectionTitle = 'Modification';
        $sectionIntro = 'Modifier le Genre ici.';

        $tableName = 'genres';
        $columns = $this->metadataService->getMigrationMetadata($tableName);
        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('genres.edit', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function store(SaveGenreRequest $request)
    {
        try {
            $genre = new Genre;
            $genre->create($request->all());
            $message = 'Genre enregistré avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'genre.index')->with('success', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function update(int $id, SaveGenreRequest $request)
    {
        try {
            $genre = Genre::findOrFail($id);
            $genre->update($request->all());
            $message = 'Genre mis à jour avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'genre.index')->with('succes', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function delete($id)
    {
        try {
            Genre::where('id', $id)->delete();
            $message = 'Genre supprimé';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'genre.index')->with('succes', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function search(Request $request, $nbAffiche = 10)
    {
        $query = $request->input('searchorders');
        $filter = $request->input('filter');

        $baseQuery = Genre::query();

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

        $genres = $baseQuery->paginate($nbAffiche);
        $activeGenres = clone $baseQuery->where('status', 'activer')->paginate($nbAffiche);
        $inactiveGenres = clone $baseQuery->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedGenres = clone $baseQuery->where('status', 'supprimer')->paginate($nbAffiche);

        // Passer les données et les paramètres à la vue
        return view('genres.index', [
            'title' => 'Genres',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'genre.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'genre.create'),
            'addButtonText' => 'Ajouter genre',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'genre.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $genres,
                'paid' => $activeGenres,
                'pending' => $inactiveGenres,
                'cancelled' => $deletedGenres,
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
        $baseQuery = Genre::query();

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
        $GenresQuery = clone $baseQuery;
        $activeGenresQuery = clone $baseQuery;
        $inactiveGenresQuery = clone $baseQuery;
        $deletedGenresQuery = clone $baseQuery;

        // Apply status filters
        $genres = match ($selectedView) {
            'orders-all-tab' => $GenresQuery->paginate($nbAffiche),
            'orders-paid-tab' => $activeGenresQuery->where('status', 'activer')->paginate($nbAffiche),
            'orders-pending-tab' => $inactiveGenresQuery->where('status', 'desactiver')->paginate($nbAffiche),
            'orders-cancelled-tab' => $deletedGenresQuery->where('status', 'supprimer')->paginate($nbAffiche),
        };

        // share data to view
        $pdf = PDF::loadView('genres.print', compact('genres'));

        // download PDF file with download method
        return $pdf->download('genres.pdf');
    }
}
