<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Parcelle;
use App\Models\Producteur;
use App\Models\Production;
use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class AppController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $totalAgents = Employee::where('status', 'activer')->count();
        $totalProducteurs = Producteur::where('status', 'activer')->count();
        $totalParcelles = Parcelle::where('status', 'activer')->count();
        $totalProductions = Production::where('status', 'activer')->count();
        $roleName=strtolower(Auth::user()->getRoleNames()->first());
        return view(strtolower(Auth::user()->getRoleNames()->first()) . '.dashboard', compact('roleName','totalAgents', 'totalProducteurs', 'totalParcelles', 'totalProductions'));
    }

    public function profil(): \Illuminate\View\View
    {
        return view(strtolower(Auth::user()->getRoleNames()->first()) . '.profil');
    }

    public function settings(): \Illuminate\View\View
    {
        return view(strtolower(Auth::user()->getRoleNames()->first()) . '.settings');
    }
}
