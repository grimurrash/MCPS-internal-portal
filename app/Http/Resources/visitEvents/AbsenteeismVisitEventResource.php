<?php

namespace App\Http\Resources\visitEvents;

use App\Models\VisitEvent;
use Illuminate\Http\Resources\Json\JsonResource;

class AbsenteeismVisitEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $entrance_time = $this['entrance_time'] === null ? '-' : $this['entrance_time']->format('H:i');
        $exit_time = $this['exit_time'] === null ? '-' : $this['exit_time']->format('H:i');

        return [
            'employee' => $this['employee']->fullName,
            'employee_id' => $this['employee_id'],
            'department' => $this['employee']->department->name,
            'department_id' => $this['employee']->department_id,
            'date' => $this['date']->format('d.m.Y'),
            'entrance_time' => $entrance_time,
            'exit_time' => $exit_time,
            'startOfTheDay' => $this['startOfTheDay'],
            'endOfTheDay' => $this['endOfTheDay'],
            'isAbsenteeism' => VisitEvent::isAbsenteeism($this['entrance_time'], $this['startOfTheDay'] )
        ];
    }
}
