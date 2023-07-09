<?php

namespace App\Http\Resources\OrganizationProject;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OrganizationProject */
class OrganizationProjectInfoResource extends JsonResource
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
            'description' => $this->description,
            'metrics' => $this->metrics,
            'planned_coverage' => $this->planned_coverage,
            'actual_coverage' => $this->actual_coverage,
            'curator_id' => $this->curator_id,
            'organizer_id' => $this->organizer_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
