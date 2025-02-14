<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperDepartement
 */
class Departement extends Model
{
    use HasFactory;

    protected $table = 'departements';

    protected $guarded = [];

    protected $fillable = [
        'libelle',
        'status',
    ];

    public $timestamps = true;

    public function postes()
    {
        return $this->hasMany(Poste::class);
    }
}
