<?php

namespace App\Http\Resources\visitEvents;

use App\Models\VisitEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbsenceWorkVisitEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'employee' => $this['employee']->fullName,
            'department' => $this['employee']->departmentsName,
            'department_id' => $this['employee']->department_id,
            'date' => $this['date']->format('d.m.Y'),
        ];
    }
}
