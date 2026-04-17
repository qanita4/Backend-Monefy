<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Cari user berdasarkan email, kalau tidak ada buat baru
            $user = User::updateOrCreate([
                'email' => $googleUser->email,
            ], [
                'name' => $googleUser->name,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'password' => null, // Karena login Google, password dikosongkan
            ]);

            Auth::login($user);

            $token = $user->createToken('MonefyToken')->plainTextToken;

            return redirect("http://localhost:3000/login-success?token=" . $token);

        } catch (Exception $e) {
            return redirect("http://localhost:3000/login-failed?error=" . $e->getMessage());
        }
    }
}
