<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Http\Request;
use App\models\Itineraire;
use Illuminate\Support\Facades\DB;

class ItineraireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Itineraire::with('destinations')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'categorie' => 'required|string',
            'duree' => 'required|integer|min:1',
            'image' => 'nullable|string',
            'destination' => 'required|array|min:2',
            'destinations.*.nom' => 'required|string',
            'destinations.*.lieu' => 'required|string'
        ]);

        DB::beginTransaction();

        try {
            $itineraire = Itineraire::create([
                'user_id' => auth()->id(),
                'titre' => $request->titre,
                'categorie' => $request->categorie,
                'duree' => $request->duree,
                'image' => $request->image
            ]);

            foreach ($request->destinations as $dest) {
                Destination::create([
                    'itineraire_id' => $itineraire->id,
                    'nom' => $dest['nom'],
                    'lieu' => $dest['lieu']
                ]);
            }

            DB::commit();

                return response()->json($itineraire->load('destinations'), 201);

            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['error' => 'Erreur lors de la création'], 500);
            }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Itineraire $itineraire)
    {
        if ($itineraire->user_id !== auth()->id()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $request->validate([
            'titre' => 'sometimes|string|max:255',
            'categorie' => 'sometimes|string',
            'duree' => 'sometimes|integer|min:1',
            'image' => 'nullable|string'
        ]);

        $itineraire->update($request->all());

        return response()->json($itineraire);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
