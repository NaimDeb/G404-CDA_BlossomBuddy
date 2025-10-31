<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPlant extends Model
{
    // Précise a Laravel que la table s'appelle user_plant dans la db
    protected $table = 'user_plant';

    protected $fillable = [
        'user_id',
        'plant_id',
        'city',
        'next_watering_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }
}
