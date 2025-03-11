<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    // Inscription utilisateur
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Création du token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Utilisateur inscrit avec succès',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    // Connexion utilisateur
    public function login(Request $request)
    {
        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Utilisateur connecté avec succès',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }
}
