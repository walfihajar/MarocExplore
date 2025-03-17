<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory;

    protected $fillable = ['itineraire_id', 'nom', 'lieu'];

    public function itineraire()
    {
        return $this->belongsTo(Itineraire::class);
    }
    public function details(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DestinationDetail::class);
    }
}
