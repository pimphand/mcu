<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.login.index');
    }

    public function login(Request $request)
    {
        $credentials = $this->validate($request, [
            'username' => 'required|min:1,max:30',
            'password' => 'required|min:1,max:30'
        ]);

        if (filter_var($credentials['username'], FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $credentials['username'];
            unset($credentials['username']);
        }

        $login = Auth::attempt($credentials);
        if (!$login) {
            return redirect()->back()->with('error', 'Username or Password Incorect');
        }

        return redirect()->intended(route('dashboard.index'));
    }

    public function logout()
    {
        \Session::flush();
        return redirect()->route('login.index');
    }

    public function pesertaToken($token)
    {
        return $token;
    }
}
