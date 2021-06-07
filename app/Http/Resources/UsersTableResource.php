<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UsersTableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'fullName' => $this->fullName,
            'avatar' => $this->avatar ? Storage::url($this->avatar) :  '',
            'email' => $this->email,
            'role' => $this->role->name,
            'created_at' => $this->created_at
        ];
    }
}
