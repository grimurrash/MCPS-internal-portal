<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(Role::class,'users_permissions');
    }
}
