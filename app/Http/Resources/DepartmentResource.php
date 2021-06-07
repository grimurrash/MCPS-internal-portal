<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'parent' => $this->parent_id !== '' ? $this->parent->name : '-',
            'head_id' => $this->head_id,
            'head' => $this->head_id !== null ? $this->headUser->fullName : 'Не указан',
            'employee_count' => $this->employees->count()
        ];
    }
}
