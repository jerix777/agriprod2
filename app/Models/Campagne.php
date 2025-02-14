<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCampagne
 */
class Campagne extends Model
{
    use HasFactory;

    protected $table = 'campagnes';

    // protected $fillable = [
    //     'annee',
    //     'theme',
    //     'date_debut',
    //     'date_fin',
    //     'status',
    // ];
    protected $guarded = [];

    public $timestamps = true;

    /**
     * productions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Production, $this>
     */
    public function productions()
    {
        return $this->hasMany(Production::class);
    }
}
