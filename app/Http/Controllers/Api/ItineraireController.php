<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Http\Request;
use App\Models\Itineraire;
use Illuminate\Support\Facades\DB;

class ItineraireController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // 1. Requête pour récupérer tous les itinéraires avec leurs destinations
    public function index()
    {
        $itineraires = DB::table('itineraires')
            ->join('destinations', 'itineraires.id', '=', 'destinations.itineraire_id')
            ->select('itineraires.*', 'destinations.*')
            ->get();

        return response()->json($itineraires);
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
            'destinations' => 'required|array|min:2',
            'destinations.*.nom' => 'required|string',
            'destinations.*.lieu' => 'required|string',
            'destinations.*.details' => 'nullable|array',
            'destinations.*.details.*.type' => 'required|string|in:endroit,activité,plat',
            'destinations.*.details.*.nom' => 'required|string'
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
                $destination = Destination::create([
                    'itineraire_id' => $itineraire->id,
                    'nom' => $dest['nom'],
                    'lieu' => $dest['lieu']
                ]);

                if (!empty($dest['details'])) {
                    foreach ($dest['details'] as $detail) {
                        $destination->details()->create([
                            'type' => $detail['type'],
                            'nom' => $detail['nom']
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json($itineraire->load('destinations.details'), 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Erreur lors de la création : ' . $e->getMessage()], 500);
        }
    }

    // 2. Filtrer les itinéraires par catégorie et durée
    public function search(Request $request)
    {
        $query = DB::table('itineraires');

        if ($request->has('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        if ($request->has('duree')) {
            $query->where('duree', $request->duree);
        }

        $itineraires = $query
            ->join('destinations', 'itineraires.id', '=', 'destinations.itineraire_id')
            ->select('itineraires.*', 'destinations.*')
            ->get();

        return response()->json($itineraires);
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
    // 3. Ajouter un itinéraire à la liste personnelle "À visiter"
    public function ajouterAlaListe($id)
    {
        $user = auth()->user();

        $exists = DB::table('listeVisite')
            ->where('user_id', $user->id)
            ->where('itineraire_id', $id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Déjà ajouté'], 200);
        }

        // Ajouter l'itinéraire à la liste
        DB::table('listeVisite')->insert([
            'user_id' => $user->id,
            'itineraire_id' => $id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Ajouté à la liste à visiter'], 201);
    }

    // 4. Recherche d'itinéraires contenant un mot-clé dans le titre
    public function searchByKeyword(Request $request)
    {
        $query = DB::table('itineraires');

        if ($request->has('mot_cle')) {
            $query->where('titre', 'like', '%' . $request->mot_cle . '%');
        }

        $itineraires = $query
            ->join('destinations', 'itineraires.id', '=', 'destinations.itineraire_id')
            ->select('itineraires.*', 'destinations.*')
            ->get();

        return response()->json($itineraires);
    }

    // 6. Statistiques : Nombre total d'itinéraires par catégorie
    public function statistiquesParCategorie()
    {
        $stats = DB::table('itineraires')
            ->select('categorie', DB::raw('count(*) as total'))
            ->groupBy('categorie')
            ->get();

        return response()->json($stats);
    }

    //5. Récupérer les itinéraires les plus populaires (avec le plus de favoris)
    public function itinerairePopulaires()
    {
        $itineraires = DB::table('listeVisite')
            ->select('itineraire_id', DB::raw('count(*) as total_favoris'))
            ->groupBy('itineraire_id')
            ->orderByDesc('total_favoris')
            ->join('itineraires', 'itineraires.id', '=', 'listeVisite.itineraire_id')
            ->take(5)
            ->get();

        return response()->json($itineraires);
    }


}
