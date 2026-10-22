<?php

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\CreateUserData;
use App\Application\UseCases\Auth\LoginUser;
use App\Application\UseCases\Auth\RegisterUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request, LoginUser $loginUser)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = $loginUser->execute($credentials['email'], $credentials['password']);

        if (! $user || ! Auth::loginUsingId($user->id, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request, RegisterUser $registerUser)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'phone' => 'required|digits_between:10,15',
            'address' => 'required|string|max:500',
        ]);

        $userData = new CreateUserData(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            phone: $data['phone'],
            address: $data['address'],
        );

        $user = $registerUser->execute($userData);
        Auth::loginUsingId($user->id);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
