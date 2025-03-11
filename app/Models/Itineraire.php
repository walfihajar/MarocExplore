<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Itineraire extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'titre', 'categorie', 'duree', 'image'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function destinations()
    {
        return $this->hasMany(Destination::class);
    }
}
