<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveEmployeeRequest;
use App\Models\Employee;
use App\Services\MigrationMetadataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    protected $routeName = 'employer';

    protected $metadataService;

    protected $nbLines = 2;

    public function __construct(MigrationMetadataService $metadataService)
    {
        $this->metadataService = $metadataService;
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
        $employers = Employee::with('postes.departement', 'postes.role')->paginate($nbAffiche);
        $activeEmployees = Employee::with('postes.departement', 'postes.role')->where('status', 'activer')->paginate($nbAffiche);
        $inactiveEmployees = Employee::with('postes.departement', 'postes.role')->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedEmployees = Employee::with('postes.departement', 'postes.role')->where('status', 'supprimer')->paginate($nbAffiche);

        return view('employers.index', [
            'title' => 'Personnels',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'employer.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'employer.create'),
            'addButtonText' => 'Ajouter un Employé',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'employer.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $employers,
                'paid' => $activeEmployees,
                'pending' => $inactiveEmployees,
                'cancelled' => $deletedEmployees,
            ],
            'tableHeaders' => $this->tableHeaders,
            'tableActions' => $this->tableActions,
            'routeName' => $this->routeName,
        ]);
    }

    public function create()
    {
        $routeName = $this->routeName;
        $pageTitle = 'Employees';
        $sectionTitle = 'Ajout';
        $sectionIntro = 'Enregistrer un nouveau Employé ici.';

        $tableName = 'employers';
        $columns = $this->metadataService->getMigrationMetadata($tableName);
        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('employers.create', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function edit(Employee $Employee)
    {
        $routeName = $this->routeName;
        $pageTitle = 'Employees';
        $sectionTitle = 'Modification';
        $sectionIntro = 'Modifier l\'Employé ici.';

        $tableName = 'employers';
        $columns = $this->metadataService->getMigrationMetadata($tableName);
        $relatedData = $this->metadataService->getRelatedData($columns);

        return view('employers.edit', compact('columns', 'routeName', 'relatedData', 'pageTitle', 'sectionTitle', 'sectionIntro', 'columns', 'relatedData'));
    }

    public function store(SaveEmployeeRequest $request)
    {
        try {
            $employer = new Employee;
            $employer->create($request->all());
            $message = 'Employé enregistré avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'employer.index')->with('success', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function update(int $id, SaveEmployeeRequest $request)
    {
        try {
            $employer = Employee::findOrFail($id);
            $employer->update($request->all());
            $message = 'Employé mis à jour avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'employer.index')->with('succes', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function delete($id)
    {
        try {
            Employee::where('id', $id)->delete();
            $message = 'Employé supprimé avec succès';

            return redirect()->route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'employer.index')->with('succes', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();

            return redirect()->back()->with('error_message', $message);
        }
    }

    public function search(Request $request, $nbAffiche = 10)
    {
        $query = $request->input('searchorders');
        $filter = $request->input('filter');

        $baseQuery = Employee::with('poste.departement', 'poste.role');

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

        $employers = (clone $baseQuery)->paginate($nbAffiche);
        $activeEmployees = (clone $baseQuery)->where('status', 'activer')->paginate($nbAffiche);
        $inactiveEmployees = (clone $baseQuery)->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedEmployees = (clone $baseQuery)->where('status', 'supprimer')->paginate($nbAffiche);

        return view('employers.index', [
            'title' => 'Personnels',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'employer.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'employer.create'),
            'addButtonText' => 'Ajouter un Employé',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'employer.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $employers,
                'paid' => $activeEmployees,
                'pending' => $inactiveEmployees,
                'cancelled' => $deletedEmployees,
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

        $baseQuery = Employee::with('poste.departement', 'poste.role');

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

        $employers = $baseQuery->paginate($nbAffiche);
        $activeEmployees = (clone $baseQuery)->where('status', 'activer')->paginate($nbAffiche);
        $inactiveEmployees = (clone $baseQuery)->where('status', 'desactiver')->paginate($nbAffiche);
        $deletedEmployees = (clone $baseQuery)->where('status', 'supprimer')->paginate($nbAffiche);

        return view('employers.index', [
            'title' => 'Personnels',
            'searchAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'employer.search'),
            'addAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'employer.create'),
            'addButtonText' => 'Ajouter un Employé',
            'printAction' => route(strtolower(Auth::user()->getRoleNames()->first()).'.'.'employer.print'),
            'addButtonPrint' => 'Imprimer',
            'tabs' => $this->tabs,
            'data' => [
                'all' => $employers,
                'paid' => $activeEmployees,
                'pending' => $inactiveEmployees,
                'cancelled' => $deletedEmployees,
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
        $baseQuery = Employee::query();

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
        $EmployeesQuery = clone $baseQuery;
        $activeEmployeesQuery = clone $baseQuery;
        $inactiveEmployeesQuery = clone $baseQuery;
        $deletedEmployeesQuery = clone $baseQuery;

        // Apply status filters
        $employers = match ($selectedView) {
            'orders-all-tab' => $EmployeesQuery->paginate($nbAffiche),
            'orders-paid-tab' => $activeEmployeesQuery->where('status', 'activer')->paginate($nbAffiche),
            'orders-pending-tab' => $inactiveEmployeesQuery->where('status', 'desactiver')->paginate($nbAffiche),
            'orders-cancelled-tab' => $deletedEmployeesQuery->where('status', 'supprimer')->paginate($nbAffiche),
        };

        // share data to view
        $pdf = PDF::loadView('employers.print', compact('employers'));

        // download PDF file with download method
        return $pdf->download('employers.pdf');
    }
}
