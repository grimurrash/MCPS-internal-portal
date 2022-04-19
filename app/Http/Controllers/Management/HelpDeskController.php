<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Resources\HelpDeskResource;
use App\Models\Employee;
use App\Models\HelpDesk;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HelpDeskController extends Controller
{
    public function createOptionsList(): JsonResponse
    {
        $execution_addresses = HelpDesk::EXECUTION_ADDRESSES;
        $categories = HelpDesk::HELP_DESK_CATEGORIES;
        $employeesList = Employee::query()->get(['id', 'fullName']);
        $employees = [];
        foreach ($employeesList as $employee) {
            $employees[$employee->id] = $employee->fullName;
        }
        return response()->json([
            'addresses' => $execution_addresses,
            'categories' => $categories,
            'employees' => $employees,
        ]);
    }

    public function createHelpDeskRequest(Request $request): JsonResponse
    {
        $helpDesk = HelpDesk::create([
            'creation_time' => Carbon::now(),
            'employee_info' => $request->input('employee_info'),
            'employee_id' => $request->input('employee_id'),
            'execution_address' => (string)$request->input('execution_address', '0'),
            'category' => (string)$request->input('category', '0'),
            'description' => $request->input('description', ''),
        ]);
        return response()->json([
            'status' => true,
            'helpDeskRequestId' => $helpDesk->id
        ]);
    }

    public function tableOptionsList(): JsonResponse
    {
        $execution_addresses = [];
        $categories = [];
        $statuses = [];
        $executors = [];
        foreach (HelpDesk::EXECUTION_ADDRESSES as $id => $value) {
            $execution_addresses[] = [
                'id' => $id,
                'name' => $value
            ];
        }
        foreach (HelpDesk::HELP_DESK_CATEGORIES as $id => $value) {
            $categories[] = [
                'id' => $id,
                'name' => $value
            ];
        }
        foreach (HelpDesk::HELP_DESK_TASK_STATUSES as $id => $value) {
            $statuses[] = [
                'id' => $id,
                'name' => $value
            ];
        }
        foreach (HelpDesk::HELP_DESK_EXECUTORS as $id => $value) {
            $executors[] = [
                'id' => $id,
                'name' => $value
            ];
        }
        return response()->json([
            'addresses' => $execution_addresses,
            'categories' => $categories,
            'statuses' => $statuses,
            'executors' => $executors,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int)$request->input('perPage', 10);
        $page = (int)$request->input('page', 1);
        $sortBy = $request->input('sortBy', 'fullName');
        if ($sortBy === null) $sortBy = 'id';
        $sortDesc = $request->input('sortDesc', false);
        $sortDesc = $sortDesc === "true";
        $executionAddress = $request->input('executionAddressFilter');
        $category = $request->input('categoryFilter',);
        $status = $request->input('statusFilter');
        $executor = $request->input('executorFilter');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date', $startDate);

        $items = HelpDesk::query();

        if (isset($executionAddress)) $items->where('execution_address', $executionAddress);
        if (isset($category)) $items->where('category', $category);
        if (isset($status)) $items->where('status', $status);
        if (isset($executor)) $items->where('executor_id', $executor);

        if (isset($startDate)) {
            $items->where(function ($q) use ($startDate) {
                $q->orWhereDate('creation_time', '>=', Carbon::parse($startDate)->toDate());
            });
        }

        if (isset($endDate)) {
            $items->where(function ($q) use ($endDate) {
                $q->whereNull('date_of_execution')->orWhereDate('date_of_execution', '<=', Carbon::parse($endDate)->toDate());
            });
        }

        $items = $items->get();

        if ($sortBy === 'executor') {
            $items = $items->sortBy(function ($item) {
                return $item->executor->fullName;
            }, 0, $sortDesc);
        } elseif ($sortBy === 'employee') {
            $items = $items->sortBy(function ($item) {
                return $item->employee->fullName;
            }, 0, $sortDesc);
        } else {
            $items = $items->sortBy($sortBy, 0, $sortDesc);
        }
        $totalCount = $items->count();
        $items = $items->skip($page * $perPage - $perPage)->take($perPage);
        return response()->json([
            'total' => $totalCount,
            'list' => HelpDeskResource::collection($items)
        ]);
    }

    public function show(Request $request, int $helpDeskId): JsonResponse
    {
        $helpDesk = HelpDesk::find($helpDeskId);

        if (is_null($helpDesk)) {
            return response()->json()->setStatusCode(404);
        }

        return response()->json(HelpDeskResource::make($helpDesk));
    }

    public function update(Request $request, int $helpDeskId): JsonResponse
    {
        $helpDesk = HelpDesk::query()->find($helpDeskId);

        if (is_null($helpDesk)) {
            return response()->json()->setStatusCode(404);
        }
        $executorId = $request->input('executor_id');
        $statusId = $request->input('status_id');
        $dateOfExecution = null;

        if ($executorId == 0) $executorId = null;

        if ($executorId != null && $statusId == 0) {
            $statusId = 1;
        }

        if ($executorId == null && $statusId != 0) {
            $executorId = $request->user()->id;
        }

        if ($statusId == 2 && $statusId != $helpDesk->status) {
            $dateOfExecution = Carbon::now();
        }

        if (!isset(HelpDesk::HELP_DESK_TASK_STATUSES[$statusId])) {
            return response()->json()->setStatusCode(401);
        }
        $helpDesk->update([
            'date_of_execution' => $dateOfExecution,
            'executor_id' => $executorId,
            'status' => (string)$statusId,
            'executor_note' => $request->input('executor_note', null)
        ]);

        return response()->json(['status' => true]);
    }

    public function changeHelpDeskRequestStatus(Request $request, int $helpDeskId)
    {
        $helpDesk = HelpDesk::query()->find($helpDeskId);

        if (is_null($helpDesk)) {
            return response()->json()->setStatusCode(404);
        }
        $dateOfExecution = $helpDesk->date_of_execution;
        $statusId = $request->input('status_id');

        if (!isset(HelpDesk::HELP_DESK_TASK_STATUSES[$statusId])) {
            return response()->json()->setStatusCode(401);
        }

        if ($statusId == 2 && $statusId != $helpDesk->status) {
            $dateOfExecution = Carbon::now();
        }
        $executorId = $helpDesk->executor_id ?? $request->user()->id;

        $helpDesk->update([
            'date_of_execution' => $dateOfExecution,
            'executor_id' => $executorId,
            'status' => (string)$statusId,
        ]);

        return response()->json(['status' => true]);
    }

    public function acceptHelpDeskRequest(Request $request, $helpDeskId)
    {
        $helpDesk = HelpDesk::query()->find($helpDeskId);

        if (is_null($helpDesk)) {
            return response()->json()->setStatusCode(404);
        }
        $executorId = $request->user()->id;
        $helpDesk->update([
            'executor_id' => $executorId,
            'status' => "1"
        ]);
        return response()->json(['status' => true]);
    }
}
