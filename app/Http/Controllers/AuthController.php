<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Http\RedirectResponse;
// use Illuminate\Support\Facades\Response;
use App\Http\Requests\AuthRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('welcome'); // Vous pouvez changer 'welcome' par la vue souhaitée pour la page d'accueil.
    }

    public function home(): \Illuminate\View\View
    {
        return view('welcome');
    }

    public function login(): \Illuminate\View\View
    {
        if (Auth::check()) {
            $roleName = strtolower(Auth::user()->getRoleNames()->first());
            $routeName = strtolower($roleName . '.index');
            return redirect()->route($routeName);
        } else {
            return view('login');
        }
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        return redirect('/login'); // Redirige vers la page de connexion après la déconnexion
    }

    public function dashboard()
    {
        if (Auth::check()) {
            $roleName = strtolower(Auth::user()->getRoleNames()->first());
            $routeName = strtolower($roleName . '.index');
            return redirect()->route($routeName);
        } else {
            return view('login');
        }
    }

    public function account()
    {
        if (Auth::check()) {
            $roleName = strtolower(Auth::user()->getRoleNames()->first());
            $routeName = strtolower($roleName . '.account');
            return redirect()->route($routeName);
        } else {
            return view('login');
        }
    }

    public function settings()
    {
        if (Auth::check()) {
            $roleName = strtolower(Auth::user()->getRoleNames()->first());
            $routeName = strtolower($roleName . '.settings');
            return redirect()->route($routeName);
        } else {
            return view('login');
        }
    }

    public function register(): \Illuminate\View\View
    {
        if (Auth::check()) {
            $role = strtolower(Auth::user()->getRoleNames()->first()->name);
            return view($role . '.login');
        }

        return view('register');
    }

    public function handleLogin(AuthRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $role = strtolower($user->getRoleNames()->first()); // Récupère le premier rôle de l'utilisateur

            // Construit la route de redirection dynamiquement
            $dashboardRoute = $role . '.dashboard';

            // Vérifie si la route existe avant de rediriger
            if (Route::has($dashboardRoute)) {
                return redirect()->route($dashboardRoute);
            } else {
                return redirect()->route('login')->with('error_msg', 'Route de tableau de bord non trouvée');
            }
        } else {
            return redirect()->back()->with('error_msg', 'Paramètres de connexion non reconnus');
        }
    }

    public function handleRegister(AuthRequest $request)
    {
        $validatedData = $request->validated();

        // Vérifiez si l'utilisateur existe déjà
        if (User::where('email', $validatedData['email'])->exists()) {
            return redirect()->back()->with('error_msg', 'Cet email est déjà utilisé');
        }

        // Créez le nouvel utilisateur
        $user = User::create([
            'nom' => $validatedData['last-name'],
            'prenom' => $validatedData['first-name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            // Ajoutez ici les autres champs nécessaires à l'enregistrement
        ]);

        // Attribuez un rôle à l'utilisateur après l'enregistrement, par exemple :
        $user->assignRole('user'); // ou 'admin' selon le contexte
        // Connectez l'utilisateur après l'enregistrement
        Auth::login($user);

        // Redirection basée sur le rôle de l'utilisateur
        $role = strtolower($user->getRoleNames()->first());

        return redirect()->route($role . '.dashboard');
    }
}
