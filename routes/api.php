<?php

use App\Http\Controllers\Api\ItineraireController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// 1. Authentification et Gestion des Utilisateurs :
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// 3. Rechercher par durée ou categorie
Route::middleware('auth:sanctum')->group(function () {
    Route::get('itineraires/search', [ItineraireController::class, 'search']);
});

// 2. Gestion des Itinéraires :
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('itineraires', ItineraireController::class);
    Route::post('itineraire/{id}/ma_liste', [ItineraireController::class, 'ajouterAlaListe']);
});



