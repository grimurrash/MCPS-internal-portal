<?php

namespace App\Http\Resources\OrganizationProject;

use Auth;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OrganizationProject */
class OrganizationProjectListItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'number' => $this->number,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'responsible_employee_id' => $this->responsible_employee_id,
            'responsible_employee_name' => $this->responsibleEmployee->fullName,
            'description' => $this->description,
            'metrics' => $this->metrics,
            'planned_coverage' => $this->planned_coverage,
            'actual_coverage' => $this->actual_coverage,
            'curator_id' => $this->curator_id,
            'curator_name' => $this->curator->fullName,
            'organizer_id' => $this->organizer_id,
            'j' => $this->organizer->fullName,
            'status' => $this->status,
            'is_edit' => $this->organizer_id === Auth::id()
        ];
    }
}
