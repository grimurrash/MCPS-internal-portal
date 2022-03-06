<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DepartmentController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function optionsList(): JsonResponse
    {
        $departments = Department::query()->select('id', 'name')->orderBy('name')->get();
        return response()->json(['departments' => $departments]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        $perPage = (int)$request->input('perPage', 10);
        $page = (int)$request->input('page', 1);
        $sortBy = $request->input('sortBy', 'id');
        if ($sortBy === null) $sortBy = 'id';
        $sortDesc = $request->input('sortDesc', false);
        $sortDesc = $sortDesc === "true";
        $search = Str::lower($search);
        $departments = Department::query();

        $departments->where(function ($q) use ($search) {
            $q->Where(DB::raw('LOWER(name)'), 'like', "%$search%");
        });

        $departments = $departments->get();
        if ($sortBy === 'parent') {
            $departments = $departments->sortBy(function ($department) {
                if ($department->parent_id === null || $department->parent_id === '') return '';
                return $department->parent->name;
            }, 0, $sortDesc);
        } else if ($sortBy === 'head') {
            $departments = $departments->sortBy(function ($department) {
                if ($department->head_id === null || $department->head_id === '') return '';
                return $department->headUser->fullName;
            }, 0, $sortDesc);
        } else if ($sortBy === 'employee_count') {
            $departments = $departments->sortBy(function ($department) {
                return $department->employees->count();
            }, 0, $sortDesc);
        } else {
            $departments = $departments->sortBy($sortBy, 0, $sortDesc);

        }
        $totalCount = $departments->count();
        $departments = $departments->skip($page * $perPage - $perPage)->take($perPage);
        return response()->json([
            'total' => $totalCount,
            'departments' => DepartmentResource::collection($departments)
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => ['required', 'max:50000', 'mimes:xlsx,xls,xlsm']
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $file = $request->file('file');
        $fileName = "temp/importDepartments." . $file->getClientOriginalExtension();
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

        $updateCount = 0;
        $createCount = 0;

        $departmentIdsList = [];
        $departments = Department::all();
        foreach ($list as $index => $row) {
            if ($index === 0 || $index === 1) continue;

            $departmentId = (string)$row[0];
            $departmentIdsList[] = $departmentId;
            $searchDepartment = $departments->whereStrict('id', $departmentId)->first();
            if ($searchDepartment === null) {
                Department::query()->create([
                    'id' => $row[0],
                    'name' => $row[1],
                    'parent_id' => $row[2]
                ]);
                $createCount++;
                continue;
            }
            if ($searchDepartment->name !== $row[1] || $searchDepartment->parent_id !== $row[2]) {
                $searchDepartment->name = $row[1];
                $searchDepartment->parent_id = $row[2];
                $searchDepartment->save();
                $updateCount++;
            }
        }

        $deleteDepartment = $departments->whereNotIn('id', $departmentIdsList);
        foreach ($deleteDepartment as $item) $item->delete();

        unlink($filePath);

        return response()->json([
            'updateCount' => $updateCount,
            'createCount' => $createCount,
            'deleteCount' => $deleteDepartment->count()
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $department = Department::find($id);
        if ($department === null) return response()->json(['error' => ['head_id' => 'Отдел не найден']], 404);

        $head_id = $request->input('head_id');
        $user = User::find($head_id);
        if ($user === null) return response()->json(['error' => ['head_id' => 'Пользователь не найден']], 404);

        $valid = Validator::make($request->all(), [
            'head_id' => ['required']
        ]);
        if ($valid->fails()) return response()->json(['error' => $valid->errors()], 400);
        if (!$user->hasPermission('head_of_department')) {
            $permission = Permission::query()->where('slug', 'head_of_department')->first();
            $user->permissions()->attach($permission);
        }
        $department->update([
            'head_id' => $head_id
        ]);

        return response()->json(['message' => 'Руководитель добавлен']);
    }
}
