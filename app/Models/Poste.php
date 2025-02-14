<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperPoste
 */
class Poste extends Model
{
    use HasFactory;

    protected $table = 'postes';

    protected $guarded = [];
    // protected $fillable = [
    //     'departement_id',
    //     'employer_id',
    //     'role_id',
    //     'nom_reseau',
    //     'numero_de_serie',
    //     'description',
    //     'proprietaire',
    //     'status',
    // ];

    public $timestamps = true;

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function employer()
    {
        return $this->belongsTo(Employee::class);
    }
}
