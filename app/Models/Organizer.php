<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organizer extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function menus(): HasMany
    {
        return $this->hasMany(OrganizerMenu::class, 'organizer_id');
    }
}
