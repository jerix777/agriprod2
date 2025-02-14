<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCulture
 */
class Culture extends Model
{
    use HasFactory;

    protected $table = 'cultures';

    protected $guarded = [];
    // protected $fillable = [
    //     'nom_commun',
    //     'nom_propre',
    //     'nom_scientifique',
    //     'status',
    // ];

    public $timestamps = true;

    public function productions()
    {
        return $this->hasMany(Production::class);
    }
}
