<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperProduction
 */
class Production extends Model
{
    use HasFactory;

    protected $table = 'productions';

    protected $guarded = [];
    // protected $fillable = [
    //     'campagne_id',
    //     'producteur_id',
    //     'parcelle_id',
    //     'quantite',
    //     'qualite',
    //     'status',
    // ];

    public $timestamps = true;

    public function campagne()
    {
        return $this->belongsTo(Campagne::class);
    }

    public function parcelle()
    {
        return $this->belongsTo(Parcelle::class);
    }

    public function culture()
    {
        return $this->belongsTo(Culture::class);
    }
}
