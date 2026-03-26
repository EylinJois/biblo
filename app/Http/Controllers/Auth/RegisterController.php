<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8|confirmed'
            ],
            [
                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal 8 karakter.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.unique' => 'Alamat Email tidak valid.',
                'name.required' => 'Nama wajib diisi.',
            ]
        );


        try {

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password'])
            ]);

            $user->sendEmailVerificationNotification();

            return redirect()->route('verification.notice1');

        } catch (\Exception $e) {

            return back()
                ->withInput()
                ->withErrors([
                    'general' => 'Registrasi gagal. Silakan coba lagi.'
                ]);
        }
    }


}
