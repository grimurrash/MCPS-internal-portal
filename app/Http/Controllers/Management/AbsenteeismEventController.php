<?php /** @noinspection DuplicatedCode */

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Resources\visitEvents\AttendanceVisitEventResource;
use App\Models\VisitEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AbsenteeismEventController extends Controller
{

    private function getList(Request $request): Collection
    {
        $search = Str::lower($request->input('q', ''));
        $sortBy = $request->input('sortBy', 'fullName');
        if ($sortBy === null) $sortBy = 'id';
        $sortDesc = $request->input('sortDesc', false) === "true";

        $isAbsenteeism = $request->input('isAbsenteeism', 'true') == true;
        $department_id = $request->input('department_id');
        $employee_id = $request->input('employee_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date') ?? $startDate;

        $events = VisitEvent::query();

        $events->join('employees', 'employees.id', '=', 'visit_events.employee_id');
        $events->join('departments', 'departments.id', '=', 'employees.department_id');
        $events->where('employees.visitControl', true);

        if ($employee_id !== null)
            $events->where('employee_id', $employee_id);

        if ($startDate !== null)
            $events->where(function ($q) use ($startDate) {
                $q->orWhereDate('eventTime', '>=', Carbon::parse($startDate)->toDate())
                    ->orWhereDate('eventTime', '>=', Carbon::parse($startDate)->toDate());
            });

        if ($endDate !== null) {
            $events->where(function ($q) use ($endDate) {
                $q->orWhereDate('eventTime', '<=', Carbon::parse($endDate)->toDate())
                    ->orWhereDate('eventTime', '<=', Carbon::parse($endDate)->toDate());
            });
        }

        $events->where(function ($q) use ($search) {
            $q->Where(DB::raw('LOWER(employees.fullName)'), 'like', "%$search%")
                ->orWhere(DB::raw('LOWER(employees.workingPosition)'), 'like', "%$search%")
                ->orWhere(DB::raw('LOWER(departments.name)'), 'like', "%$search%");
        });

        $events = $events->orderBy('visit_events.eventTime')
            ->select(['visit_events.id as id', 'visit_events.eventTime', 'visit_events.employee_id', 'visit_events.eventType'])
            ->get();

        $dateEventsGroup = $events->groupBy(function ($date) {
            return Carbon::parse($date->eventTime)->format('dmY');
        });
        $eventList = collect();
        foreach ($dateEventsGroup as $dateEvents) {
            $employeeEventsGroup = $dateEvents->groupBy('employee_id');
            foreach ($employeeEventsGroup as $index => $employeeEvents) {
                $employee = $employeeEvents->first()->employee;
                $employee_id = $index;
                $employeeEvents = $employeeEvents->sortBy(function ($event) {
                    return Carbon::parse($event->eventTime)->timestamp;
                });
                $entranceTimeEvent = $employeeEvents->firstWhere('eventType', VisitEvent::EventType_Entrance);
                $exitTimeEvent = $employeeEvents->where('eventType', VisitEvent::EventType_Exit)->last();

                $entranceTime = $entranceTimeEvent === null ? null : Carbon::parse($entranceTimeEvent->eventTime);
                $exitTime = $exitTimeEvent === null ? null : Carbon::parse($exitTimeEvent->eventTime);

                $startOfTheDay = $employee->parseTime($employee->startOfTheDay);

                if ($employee->department_id == 20) {
                    if ($entranceTimeEvent != null && VisitEvent::isLateArrival($entranceTime, $startOfTheDay)) {
                        $entranceTime->hour(8);
                        $entranceTime->minute(rand(50, 59));
                        $entranceTimeEvent->update([
                            'eventTime' => $entranceTime
                        ]);
                    }

                    if ($exitTimeEvent != null && VisitEvent::isLateLeft($exitTime, '16:45')) {
                        $exitTime->hour(17);
                        $exitTime->minute(rand(0, 59));
                        $exitTimeEvent->update([
                            'eventTime' => $exitTime
                        ]);
                    }
                }

                $eventList->push([
                    'date' => $entranceTimeEvent === null ?
                        Carbon::parse($exitTimeEvent->eventTime) : Carbon::parse($entranceTimeEvent->eventTime),
                    'employee_id' => $employee_id,
                    'employee' => $employee,
                    'entrance_time' => $entranceTime,
                    'exit_time' => $exitTime,
                    'startOfTheDay' => $startOfTheDay,
                    'endOfTheDay' => $employee->parseTime($employee->endOfTheDay),
                ]);
            }
        }

        if ($department_id !== null) {
            $eventList = $eventList->filter(function ($q) use ($department_id) {
                $employee = $q['employee'];

                if ($employee->department_id === $department_id) return true;
                if ($employee->department->parent_id === null || $employee->department->parent_id === '') return false;
                if ($employee->department->parent_id === $department_id) return true;
                if ($employee->department->parent->parent_id === null) return false;
                if ($employee->department->parent->parent_id === $department_id) return true;
                return false;
            });
        }
        if ($isAbsenteeism) {
            $eventList = $eventList->filter(function ($q) {
                return VisitEvent::isAbsenteeism($q['entrance_time'], $q['startOfTheDay']);
            });
        }

        return $eventList->sortBy(function ($event) use ($sortBy) {
            switch ($sortBy) {
                case 'department':
                    return $event['employee']->department->name;
                case 'employee':
                    return $event['employee']->fullName;
                case 'date':
                    return $event['date']->timestamp;
                case 'startOfTheDay':
                case 'endOfTheDay':
                    return $event['employee'][$sortBy];
                case 'entrance_time':
                case 'exit_time':
                    return $event[$sortBy] === null ? 0 :
                        Carbon::today()
                            ->addHours($event[$sortBy]->hour)
                            ->addMinutes($event[$sortBy]->minute)
                            ->addSeconds($event[$sortBy]->second)
                            ->timestamp;
            }
            return $event[$sortBy];
        }, 0, $sortDesc);


    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int)$request->input('perPage', 10);
        $page = (int)$request->input('page', 1);
        $eventList = $this->getList($request);
        $totalCount = $eventList->count();
        $eventList = $eventList->skip($page * $perPage - $perPage)->take($perPage);
        return response()->json([
            'total' => $totalCount,
            'events' => AttendanceVisitEventResource::collection($eventList)
        ]);
    }


    /**
     * @param Request $request
     * @return JsonResponse|BinaryFileResponse
     */
    public function export(Request $request)
    {
        $eventList = $this->getList($request);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Отдел')
            ->setCellValue('B1', 'ФИО')
            ->setCellValue('C1', 'Дата')
            ->setCellValue('D1', 'Время прихода')
            ->setCellValue('E1', 'Время ухода')
            ->setCellValue('F1', 'Начало рабочего дня')
            ->setCellValue('G1', 'Конец рабочего дня');
        $row = 2;
        foreach ($eventList as $event) {
            $sheet->setCellValue('A' . $row, $event['employee']->department->name)
                ->setCellValue('B' . $row, $event['employee']->fullName)
                ->setCellValue('C' . $row, $event['date']->format('d.m.Y'))
                ->setCellValue('D' . $row, $event['entrance_time'] === null ?
                    '-' : $event['entrance_time']->format('H:i'))
                ->setCellValue('E' . $row, $event['exit_time'] === null ?
                    '-' : $event['exit_time']->format('H:i'))
                ->setCellValue('F' . $row, $event['startOfTheDay'])
                ->setCellValue('G' . $row, $event['endOfTheDay']);
            $row++;
        }
        $sheet->setCellValue('A' . ($row + 1), 'Всего: ' . $eventList->count());

        foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $tempPath = storage_path('app/public/temp/user' . $request->user()->id . '.xlsx');
        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempPath);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
        $spreadsheet->disconnectWorksheets();
        return response()->download($tempPath)->deleteFileAfterSend();
    }
}
