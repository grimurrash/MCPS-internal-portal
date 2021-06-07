<?php

namespace App\Http\Resources;

use App\Models\VisitEvent;
use Illuminate\Http\Resources\Json\JsonResource;

class BreakEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'employee' => $this['employee']->fullName,
            'employee_id' => $this['employee_id'],
            'department' => $this['employee']->department->name,
            'department_id' => $this['employee']->department_id,
            'date' => $this['date']->format('d.m.Y'),
            'exit_time' => $this['exit_time'] === null ? '-' : $this['exit_time']->format('H:i'),
            'entrance_time' => $this['entrance_time'] === null ? '-' : $this['entrance_time']->format('H:i'),
            'break_time' => $this['entrance_time'] !== null || $this['exit_time'] !== null ?
                (int)(($this['entrance_time']->timestamp - $this['exit_time']->timestamp) / 60) . ' минут.'
                : '-',
            'isLongBreak' => VisitEvent::isLongBreak($this['exit_time'], $this['entrance_time'])
        ];
    }
}
