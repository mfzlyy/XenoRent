<?php

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\UpdateUserData;
use App\Application\UseCases\Profile\UpdateProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $user->load('profile');

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request, UpdateProfile $updateProfile)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email,'.Auth::id(),
            'phone' => 'required|digits_between:10,15',
            'address' => 'required|string|max:500',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $userData = new UpdateUserData(
            name: $data['name'],
            email: $data['email'],
            phone: $data['phone'],
            address: $data['address'],
            password: $data['password'] ?? null,
        );

        $updateProfile->execute(Auth::id(), $userData);

        return back()->with('success', 'Profil berhasil diperbarui');
    }
}
