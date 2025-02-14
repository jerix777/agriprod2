<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperParcelle
 */
class Parcelle extends Model
{
    use HasFactory;

    protected $table = 'parcelles';

    protected $guarded = [];
    // protected $fillable = [
    //     'producteur_id',
    //     'matricule',
    //     'localisation',
    //     'superficie',
    // ];

    public $timestamps = true;

    public function producteur()
    {
        return $this->belongsTo(Producteur::class);
    }

    public function productions()
    {
        return $this->hasMany(Production::class);
    }
}
