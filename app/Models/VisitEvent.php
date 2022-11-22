<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitEvent extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;

    public const EventType_Entrance = "Вход";
    public const EventType_Exit = "Выход";

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * @param $entranceTime
     * @param $startOfTheDay
     * @return bool
     */
    public static function isLateArrival($entranceTime, $startOfTheDay): bool
    {
        $startOfTheDay = explode(':', $startOfTheDay);
        if ($entranceTime === null) return true;
        $entranceTimeHour = (int)$entranceTime->format('H');
        $entranceTimeMinutes = (int)$entranceTime->format('i');
        if ($entranceTimeHour > (int)$startOfTheDay[0] ||
            ($entranceTimeHour === (int)$startOfTheDay[0] && $entranceTimeMinutes > $startOfTheDay[1]))
            return true;

        return false;
    }

    public static function isLateLeft($exitTime, $endOfTheDay): bool
    {
        $endOfTheDay = explode(':', $endOfTheDay);
        if ($exitTime == null) return true;
        $exitTimeHour = (int)$exitTime->format('H');
        $exitTimeMinutes = (int)$exitTime->format('i');
        if ($exitTimeHour < (int)$endOfTheDay[0] ||
            ($exitTimeHour === (int)$endOfTheDay[0] && $exitTimeMinutes < $endOfTheDay[1]))
            return true;

        return false;
    }


    /**
     * @param $exitTime
     * @param $entranceTime
     * @return bool
     */
    public static function isLongBreak($exitTime, $entranceTime): bool
    {
        $exitHour = (int)$exitTime->format('H');
        $exitMinutes = (int)$exitTime->format('i');
        $entranceHour = (int)$entranceTime->format('H');
        $entranceMinutes = (int)$entranceTime->format('i');
        $delay = (int)(($entranceTime->timestamp - $exitTime->timestamp) / 60);
        if ((($exitHour === 13 || ($exitHour === 12 && $exitMinutes > 50))
                && ($entranceHour === 13 || ($entranceHour === 14 && $entranceMinutes < 10)))
            || (($exitHour === 12 || $exitHour === 13) && $delay < 70)
        ) return false;

        return (int)(($entranceTime->timestamp - $exitTime->timestamp) / 60) > 15;
    }

    public static function isAbsenteeism($entranceTime, $startOfTheDay): bool
    {
        $startOfTheDay = explode(':', $startOfTheDay);
        if ($entranceTime === null) return true;
        $entranceTimeHour = (int)$entranceTime->format('H');
        if ($entranceTimeHour >= (int)$startOfTheDay[0] + 4)
            return true;

        return false;
    }
}
