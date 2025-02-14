<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperEmployee
 */
class Employee extends Model
{
    use HasFactory;

    protected $table = 'employers';

    protected $guarded = [];
    // protected $fillable = [
    //     'poste_id',
    //     'genre_id',
    //     'matricule',
    //     'nom',
    //     'prenoms',
    //     'fonction',
    //     'photo',
    //     'email',
    //     'mot_de_passe',
    //     'status'
    // ];

    public $timestamps = true;

    public function postes()
    {
        return $this->belongsTo(Poste::class);
    }
}
