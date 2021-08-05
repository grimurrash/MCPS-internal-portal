<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;

    public static function othersBuildings($id)
    {
        return Building::query()->where('id','!=', $id)->get();
    }
}
