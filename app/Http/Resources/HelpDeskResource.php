<?php

namespace App\Http\Resources;

use App\Models\HelpDesk;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class HelpDeskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $creation_time = Carbon::parse($this->creation_time);
        $date_of_execution = isset($this->date_of_execution) ? Carbon::parse($this->date_of_execution) : null;
        $execution_time = null;
        if (isset($creation_time) && isset($date_of_execution)) {
            $execution_time = (int)(($date_of_execution->timestamp - $creation_time->timestamp) / 60) . ' минут.';
        }
        return [
            'id' => $this->id,
            'creation_time' => $creation_time->format('d.m.Y H:i'),
            'employee_id' => $this->employee_id ?? '',
            'employee_info' => $this->employee_info ?? '',
            'employee' => isset($this->employee_id) ? [
                'id' => $this->employee->id,
                'fullName' => $this->employee->fullName,
                'department' => $this->employee->department->name,
                'roomNumber' => $this->employee->roomNumber,
                'mobilePhone' => $this->employee->mobilePhone,
                'email' => $this->employee->email,
            ] : null,
            'execution_address_id' => (int)$this->execution_address,
            'execution_address' => HelpDesk::EXECUTION_ADDRESSES[$this->execution_address],
            'category_id' => (int)$this->category,
            'category' => HelpDesk::HELP_DESK_CATEGORIES[$this->category],
            'description' => $this->description,
            'executor_id' => $this->executor_id,
            'executor' => isset($this->executor_id) ? [
                'id' => $this->executor_id,
                'fullName' => $this->executor->fullName
            ] : null,
            'status_id' => (int)$this->status,
            'status' => HelpDesk::HELP_DESK_TASK_STATUSES[$this->status],
            'date_of_execution' => isset($date_of_execution) ? $date_of_execution->format('d.m.Y H:i') : null,
            'execution_time' =>  $execution_time,
            'is_send_feedback_email' =>  (bool)$this->is_send_feedback_email,
            'estimation' =>  $this->estimation,
            'employee_note' => $this->employee_note ?? '',
            'executor_note' => $this->executor_note ?? '',
        ];
    }
}
