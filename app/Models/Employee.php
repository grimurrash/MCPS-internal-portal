<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use phpseclib3\Math\PrimeField\Integer;

class Employee extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visitEvents(): HasMany
    {
        return $this->hasMany(VisitEvent::class,'employee_id');
    }

    public function parseTime($time): string
    {
        if ($time / 60 > 10) $hous = $time / 60;
        else $hous = '0' . ($time / 60);

        if ($time % 60 > 10) $minutes = $time % 60;
        else $minutes = '0' . ($time % 60);

        return "$hous:$minutes";
    }

    public function stringifyTime($string) {
        $arr = explode(':', $string);
        return $arr[0] * 60 + $arr[1];
    }
}
