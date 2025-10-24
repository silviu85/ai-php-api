<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle a new user registration request.
     */
    public function register(Request $request)
    {
        // 1. Validate the incoming data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users', // 'unique:users' ensures no duplicate emails
            'password' => 'required|string|min:8|confirmed', // 'confirmed' requires a 'password_confirmation' field
        ]);

        // 2. Create the user in the database
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // NEVER store plain text passwords
        ]);

        // 3. Create a token for the new user
        $token = $user->createToken('api-token-on-register')->plainTextToken;

        // 4. Return the new user and their token
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201); // 201 Created status code
    }

    /**
     * login the user and create a token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credențialele furnizate sunt incorecte.'],
            ]);
        }

        // Generează un token pentru utilizator
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        // Revocă (șterge) token-ul folosit pentru autentificare
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Deconectare reușită']);
    }
}