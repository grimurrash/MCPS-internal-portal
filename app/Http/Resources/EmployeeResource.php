<?php

namespace App\Http\Resources;

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

        return [
            'id' => $this->id,
            'fullName' => $this->fullName,
            'department_id' => $this->deparment_id,
            'department' => $this->department->name,
            'workingPosition' => $this->workingPosition,
            'roomNumber' => $this->roomNumber,
            'internalCode' => $this->internalCode,
            'mobilePhone' => $this->mobilePhone,
            'startOfTheDay' => $this->parseTime($this->startOfTheDay),
            'endOfTheDay' => $this->parseTime($this->endOfTheDay),
            'visitControl'=> $this->visitControl === 1,
            'email' => $this->email,
            'birthday' => $this->birthday
        ];
    }
}
