<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'common_name',
        'watering_general_benchmark',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'watering_general_benchmark' => 'json',
            'common_name' => 'string'
        ];
    }

    public function users(){
        return $this->belongsToMany(
            User::class,
            "user_plant",
            "plant_id",
            "user_id"
        );
    }
}
