<?php

namespace App\Http\Resources;

use App\Models\Employee;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Employee $this */
        return [
            'id' => $this->id,
            'fullName' => $this->fullName,
            'department_id' => $this->deparment_id,
            'department' => $this->department->name,
            'workingPosition' => $this->workingPosition,
            'roomNumber' => $this->roomNumber,
            'internalCode' => $this->internalCode,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'education' => $this->education,
            'founders_representative_date' => $this->founders_representative_date,
            'date_of_employment' => $this->date_of_employment,
            'mobilePhone' => $this->mobilePhone,
            'email' => $this->email,
        ];
    }
}
