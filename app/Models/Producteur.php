<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperProducteur
 */
class Producteur extends Model
{
    use HasFactory;

    protected $table = 'producteurs';

    // protected $fillable = [
    //     'genre_id',
    //     'matricule',
    //     'nom',
    //     'prenoms',
    //     'date_de_naissance',
    //     'lieu_de_residence',
    //     'contact',
    //     'status',
    // ];
    protected $guarded = [];

    public $timestamps = true;

    public function genre()
    {
        return $this->hasOne(Genre::class);
    }

    public function parcelles()
    {
        return $this->hasMany(Parcelle::class);
    }

    public function productions()
    {
        return $this->hasMany(Production::class);
    }
}
