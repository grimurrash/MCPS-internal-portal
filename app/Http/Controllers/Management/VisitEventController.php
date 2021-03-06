<?php /** @noinspection DuplicatedCode */

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Resources\BreakEventResource;
use App\Http\Resources\VisitEventResource;
use App\Models\Employee;
use App\Models\VisitEvent;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VisitEventController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $search = Str::lower($request->input('q', ''));
        $perPage = (int)$request->input('perPage', 10);
        $page = (int)$request->input('page', 1);
        $sortBy = $request->input('sortBy', 'fullName');
        if ($sortBy === null) $sortBy = 'id';
        $sortDesc = $request->input('sortDesc', false) === "true";

        $isLateArrival = $request->input('isLateArrival', false) === "true";
        $department_id = $request->input('department_id');
        $employee_id = $request->input('employee_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date') ?? $startDate;

        $events = VisitEvent::query();

        $events->join('employees', 'employees.id', '=', 'visit_events.employee_id');
        $events->join('departments', 'departments.id', '=', 'employees.department_id');

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
        if ($isLateArrival === true) {
            $eventList = $eventList->filter(function ($q) {
                return VisitEvent::isLateArrival($q['entrance_time'], $q['startOfTheDay']);
            });
        }

        $eventList = $eventList->sortBy(function ($event) use ($sortBy) {
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

        $totalCount = $eventList->count();
        $eventList = $eventList->skip($page * $perPage - $perPage)->take($perPage);
        return response()->json([
            'total' => $totalCount,
            'events' => VisitEventResource::collection($eventList)
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function breakEvent(Request $request): JsonResponse
    {
        $search = Str::lower($request->input('q', ''));
        $perPage = (int)$request->input('perPage', 10);
        $page = (int)$request->input('page', 1);
        $sortBy = $request->input('sortBy', 'fullName');
        if ($sortBy === null) $sortBy = 'id';
        $sortDesc = $request->input('sortDesc', false) === "true";

        $department_id = $request->input('department_id');
        $isLongBreak = $request->input('isLongBreak', false) === "true";
        $employee_id = $request->input('employee_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date') ?? $startDate;

        $events = VisitEvent::query();

        $events->join('employees', 'employees.id', '=', 'visit_events.employee_id');
        $events->join('departments', 'departments.id', '=', 'employees.department_id');

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
        $breakList = collect();
        foreach ($dateEventsGroup as $dateEvents) {
            $employeeEventsGroup = $dateEvents->groupBy('employee_id');
            foreach ($employeeEventsGroup as $index => $employeeEvents) {
                $employee = $employeeEvents->first()->employee;
                $employee_id = $index;
                $newBreak = null;
                $employeeEvents = $employeeEvents->sortBy(function ($event) {
                    return Carbon::parse($event->eventTime)->timestamp;
                });
                foreach ($employeeEvents as $event) {
                    if ($newBreak === null && $event->eventType === '????????') continue;

                    $eventTime = Carbon::parse($event->eventTime);
                    if ($newBreak === null && $event->eventType === '??????????') {
                        $newBreak = [
                            'date' => $eventTime,
                            'employee_id' => $employee_id,
                            'employee' => $employee,
                            'exit_time' => $eventTime,
                            'entrance_time' => null,
                        ];
                    }
                    if ($newBreak !== null && $event->eventType === '????????') {
                        $newBreak['entrance_time'] = $eventTime;
                        $breakList->push($newBreak);
                        $newBreak = null;
                    }
                }
            }
        }

        if ($department_id !== null) {
            $breakList = $breakList->filter(function ($q) use ($department_id) {
                $employee = $q['employee'];

                if ($employee->department_id === $department_id) return true;
                if ($employee->department->parent_id === null || $employee->department->parent_id === '') return false;
                if ($employee->department->parent_id === $department_id) return true;
                if ($employee->department->parent->parent_id === null) return false;
                if ($employee->department->parent->parent_id === $department_id) return true;
                return false;
            });
        }
        if ($isLongBreak === true) {
            $breakList = $breakList->filter(function ($q) {
                return VisitEvent::isLongBreak($q['exit_time'], $q['entrance_time']);
            });
        }
        $breakList = $breakList->sortBy(function ($event) {
            return $event['employee']->fullName;
        })->sortBy(function ($event) use ($sortBy) {
            switch ($sortBy) {
                case 'department':
                    return $event['employee']->department->name;
                case 'employee':
                    return $event['employee']->fullName;
                case 'date':
                    return $event['date']->timestamp;
                case 'entrance_time':
                case 'exit_time':
                    return $event[$sortBy] === null ? 0 :
                        Carbon::today()
                            ->addHours(Carbon::parse($event[$sortBy])->hour)
                            ->addMinutes(Carbon::parse($event[$sortBy])->minute)
                            ->addSeconds(Carbon::parse($event[$sortBy])->second)
                            ->timestamp;
            }
            return $event[$sortBy];
        }, 0, $sortDesc);

        $totalCount = $breakList->count();
        $breakList = $breakList->skip($page * $perPage - $perPage)->take($perPage);
        return response()->json([
            'total' => $totalCount,
            'events' => BreakEventResource::collection($breakList)
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => ['required', 'max:50000', 'mimes:xlsx,xls,xlsm'],
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        $file = $request->file('file');
        $fileName = "temp/importEmployees." . $file->getClientOriginalExtension();
        Storage::put($fileName, file_get_contents($file->getRealPath()));
        $filePath = storage_path('app/public/' . $fileName);

        $list = [];
        $excel = IOFactory::load($filePath);
        $worksheet = $excel->getActiveSheet();
        foreach ($worksheet->getRowIterator() as $row) {
            $newRow = [];
            foreach ($row->getCellIterator() as $cel) {
                $newRow[] = $cel->getValue();
            }
            $list[] = $newRow;
        }
        $excel->disconnectWorksheets();
        unset($excel);
        unlink($filePath);

        $isTodayTemplate = !($list[2][3] === '????????' || $list[2][3] === '??????????' || $list[2][3] === '');
        $minDate = "2080-12-30 21:21:21";
        foreach ($list as $item) {
            if ($item[1] === null) continue;
            $minDate = min($minDate, $item[1]);
        }
        $events = VisitEvent::query()->whereDate('eventTime', '>=', Carbon::parse($minDate)->format('Y-m-d'))->get();
        $employees = Employee::all();
        $employeesArr = [];
        foreach ($employees as $employee)
            $employeesArr[$employee->id] = $employee->id;
        $createCount = 0;
        $errorCount = 0;
        $createEvents = [];
        foreach ($list as $index => $row) {
            if ($index === 0 || $index === 1) continue;

            if ($isTodayTemplate) {
                $employeeId = trim((string)$row[3]);
                $eventType = $row[8];
            } else {
                $employeeId = trim((string)$row[5]);
                $eventType = $row[3];
            }
            if (empty($employeeId) || $eventType === '') continue;
            $eventId = (int)$row[0];

            $searchEvent = $events->contains(function ($val) use ($eventId, $eventType) {
                return $val->eventId === $eventId && $val->eventType === $eventType;
            });

            if (!$searchEvent) {
                if (!isset($employeesArr[$employeeId])) {
                    $errorCount++;
                    continue;
                }
                try {
                    $createEvents[] = [
                        'employee_id' => $employeeId,
                        'eventId' => $eventId,
                        'eventTime' => Carbon::parse($row[1]),
                        'eventType' => $eventType
                    ];
                    $createCount++;
                } catch (Exception $exception) {
                    $errorCount++;
                }
            }
        }
        VisitEvent::query()->insert($createEvents);
        return response()->json([
            'createCount' => $createCount,
            'errorCount' => $errorCount
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $event = VisitEvent::query()->find($id);
        if ($event === null) return response()->json(['error' => ['fullName' => '?????????????? ???? ??????????????']], 404);

        $event->update([
            'note' => $request->input('note')
        ]);

        return response()->json(['message' => '???????????????????? ??????????????????']);
    }

    /**
     * @param Request $request
     * @return JsonResponse|BinaryFileResponse
     */
    public function exportAttendance(Request $request)
    {
        $search = Str::lower($request->input('q', ''));
        $sortBy = $request->input('sortBy', 'employee');
        if ($sortBy === null) $sortBy = 'employee';
        $sortDesc = $request->input('sortDesc', false) === "true";

        $isLateArrival = $request->input('isLateArrival', false);
        $department_id = $request->input('department_id');
        $employee_id = $request->input('employee_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date') ?? $startDate;

        $events = VisitEvent::query();

        $events->join('employees', 'employees.id', '=', 'visit_events.employee_id');
        $events->join('departments', 'departments.id', '=', 'employees.department_id');

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
                $eventList->push([
                    'date' => $entranceTimeEvent === null ?
                        Carbon::parse($exitTimeEvent->eventTime) : Carbon::parse($entranceTimeEvent->eventTime),
                    'employee_id' => $employee_id,
                    'employee' => $employee,
                    'entrance_time' => $entranceTimeEvent === null ? null : Carbon::parse($entranceTimeEvent->eventTime),
                    'exit_time' => $exitTimeEvent === null ? null : Carbon::parse($exitTimeEvent->eventTime),
                    'startOfTheDay' => $employee->parseTime($employee->startOfTheDay),
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
        if ($isLateArrival === true) {
            $eventList = $eventList->filter(function ($q) {
                return VisitEvent::isLateArrival($q['entrance_time'], $q['startOfTheDay']);
            });
        }

        $eventList = $eventList->sortBy(function ($event) {
            return $event['employee']->fullName;
        })->sortBy(function ($event) use ($sortBy) {
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
                            ->addHours(Carbon::parse($event[$sortBy])->hour)
                            ->addMinutes(Carbon::parse($event[$sortBy])->minute)
                            ->addSeconds(Carbon::parse($event[$sortBy])->second)
                            ->timestamp;
            }
            return $event[$sortBy];
        }, 0, $sortDesc);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', '??????????')
            ->setCellValue('B1', '??????')
            ->setCellValue('C1', '????????')
            ->setCellValue('D1', '?????????? ??????????????')
            ->setCellValue('E1', '?????????? ??????????');
        $row = 2;
        foreach ($eventList as $event) {
            $sheet->setCellValue('A' . $row, $event['employee']->department->name)
                ->setCellValue('B' . $row, $event['employee']->fullName)
                ->setCellValue('C' . $row, $event['date']->format('d.m.Y'))
                ->setCellValue('D' . $row, $event['entrance_time'] === null ?
                    '-' : $event['entrance_time']->format('H:i'))
                ->setCellValue('E' . $row, $event['exit_time'] === null ?
                    '-' : $event['exit_time']->format('H:i'));
            $row++;
        }
        $sheet->setCellValue('A' . ($row + 1), '??????????: ' . $eventList->count());

        foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $tempPath = storage_path('app/public/temp/user' . $request->user()->id . '.xlsx');
        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempPath);
        } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
        $spreadsheet->disconnectWorksheets();
        return response()->download($tempPath)->deleteFileAfterSend();
    }

    public function exportBreak(Request $request)
    {
        $search = Str::lower($request->input('q', ''));
        $sortBy = $request->input('sortBy', 'fullName');
        if ($sortBy === null) $sortBy = 'id';
        $sortDesc = $request->input('sortDesc', false) === "true";

        $department_id = $request->input('department_id');
        $isLongBreak = $request->input('isLongBreak', false);
        $employee_id = $request->input('employee_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date') ?? $startDate;

        $events = VisitEvent::query();

        $events->join('employees', 'employees.id', '=', 'visit_events.employee_id');
        $events->join('departments', 'departments.id', '=', 'employees.department_id');

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
        $breakList = collect();
        foreach ($dateEventsGroup as $dateEvents) {
            $employeeEventsGroup = $dateEvents->groupBy('employee_id');
            foreach ($employeeEventsGroup as $index => $employeeEvents) {
                $employee = $employeeEvents->first()->employee;
                $employee_id = $index;
                $newBreak = null;
                $employeeEvents = $employeeEvents->sortBy(function ($event) {
                    return Carbon::parse($event->eventTime)->timestamp;
                });
                foreach ($employeeEvents as $event) {
                    if ($newBreak === null && $event->eventType === '????????') continue;

                    $eventTime = Carbon::parse($event->eventTime);
                    if ($newBreak === null && $event->eventType === '??????????') {
                        $newBreak = [
                            'date' => $eventTime,
                            'employee_id' => $employee_id,
                            'employee' => $employee,
                            'exit_time' => $eventTime,
                            'entrance_time' => null,
                        ];
                    }
                    if ($newBreak !== null && $event->eventType === '????????') {
                        $newBreak['entrance_time'] = $eventTime;
                        $breakList->push($newBreak);
                        $newBreak = null;
                    }
                }
            }
        }

        if ($department_id !== null) {
            $breakList = $breakList->filter(function ($q) use ($department_id) {
                $employee = $q['employee'];

                if ($employee->department_id === $department_id) return true;
                if ($employee->department->parent_id === null || $employee->department->parent_id === '') return false;
                if ($employee->department->parent_id === $department_id) return true;
                if ($employee->department->parent->parent_id === null) return false;
                if ($employee->department->parent->parent_id === $department_id) return true;
                return false;
            });
        }
        if ($isLongBreak === true) {
            $breakList = $breakList->filter(function ($q) {
                return VisitEvent::isLongBreak($q['exit_time'], $q['entrance_time']);
            });
        }
        $breakList = $breakList->sortBy(function ($event) {
            return $event['employee']->fullName;
        })->sortBy(function ($event) use ($sortBy) {
            switch ($sortBy) {
                case 'department':
                    return $event['employee']->department->name;
                case 'employee':
                    return $event['employee']->fullName;
                case 'date':
                    return $event['date']->timestamp;
                case 'entrance_time':
                case 'exit_time':
                    return $event[$sortBy] === null ? 0 :
                        Carbon::today()
                            ->addHours(Carbon::parse($event[$sortBy])->hour)
                            ->addMinutes(Carbon::parse($event[$sortBy])->minute)
                            ->addSeconds(Carbon::parse($event[$sortBy])->second)
                            ->timestamp;
            }
            return $event[$sortBy];
        }, 0, $sortDesc);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', '??????????')
            ->setCellValue('B1', '??????')
            ->setCellValue('C1', '????????')
            ->setCellValue('D1', '?????????? ??????????')
            ->setCellValue('E1', '?????????? ??????????????')
            ->setCellValue('F1', '???????????????????????? ????????????????');
        $row = 2;
        foreach ($breakList as $event) {
            $sheet->setCellValue('A' . $row, $event['employee']->department->name)
                ->setCellValue('B' . $row, $event['employee']->fullName)
                ->setCellValue('C' . $row, $event['date']->format('d.m.Y'))
                ->setCellValue('D' . $row, $event['exit_time'] === null ?
                    '-' : $event['exit_time']->format('H:i'))
                ->setCellValue('E' . $row, $event['entrance_time'] === null ?
                    '-' : $event['entrance_time']->format('H:i'))
                ->setCellValue('F' . $row,
                    $event['entrance_time'] !== null || $event['exit_time'] !== null ?
                        (int)(($event['entrance_time']->timestamp - $event['exit_time']->timestamp) / 60) . ' ??????????.'
                        : '-');

            $row++;
        }
        $sheet->setCellValue('A' . ($row + 1), '??????????: ' . $breakList->count());

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $tempPath = storage_path('app/public/temp/user' . $request->user()->id . '.xlsx');
        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempPath);
        } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
        $spreadsheet->disconnectWorksheets();
        return response()->download($tempPath)->deleteFileAfterSend();
    }
}
