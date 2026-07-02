<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    protected $table = 'wilayah';

    protected $fillable = [
        'nama_wilayah', 'latitude', 'longitude', 'geojson_polygon',
    ];

    public function petaniProfiles()
    {
        return $this->hasMany(PetaniProfile::class, 'wilayah_id');
    }
}
