<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperGenre
 */
class Genre extends Model
{
    use HasFactory;

    protected $table = 'genres';

    protected $guarded = [];

    protected $fillable = [
        'libelle',
        'status',
    ];

    public $timestamps = true;

    public function employers()
    {
        return $this->belongsTo(Employee::class);
    }

    public function producteurs()
    {
        return $this->belongsTo(Producteur::class);
    }
}
