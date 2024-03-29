<?php /** @noinspection DuplicatedCode */

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeOptionsResource;
use App\Http\Resources\EmployeeResource;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Revolution\Google\Sheets\Facades\Sheets;

class EmployeeController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function optionsList(): JsonResponse
    {
        $employees = Employee::query()->select('id', 'fullName')->orderBy('fullName')->get();
        return response()->json(['employees' => $employees]);
    }

    /**
     * @param $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function optionsListByUser($userId): JsonResponse
    {
        $department = Department::where('head_id', $userId)->first();
        if ($department === null) return response()->json(['error' => ['fullName' => 'Отдел не найден']], 404);

        $department_id = $department->id;
        $employees = Employee::all()->filter(function ($q) use ($department_id) {
            if ($q->department_id === $department_id) return true;

            if ($q->department->parent_id === null || $q->department->parent_id === '') return false;

            if ($q->department->parent_id === $department_id) return true;

            if ($q->department->parent->parent_id === null) return false;

            if ($q->department->parent->parent_id === $department_id) return true;

            return false;
        })->sortBy('fullName');

        return response()->json([
            'departmentId' => $department->id,
            'employees' => EmployeeOptionsResource::collection($employees)
        ]);
    }

    public function importPhoneBook(): JsonResponse
    {
        $sheets = Sheets::spreadsheet(env('PHONEBOOK_SPREADSHEET_ID'))->sheet('Лист1')->get();
        $updateCount = 0;
        $employees = Employee::all();
        foreach ($sheets as $index => $row) {
            if (count($row) <= 7) continue;
            [$workingPosition, $fullName, $phone, $internalCode, $mobilePhone, $roomNumber, $objectName, $employeeId] = $row;
            if ($employeeId === null || $employeeId === "") continue;
            $employee = $employees->find($employeeId);
            if ($employee === null) continue;

            $employee->update([
                'internalCode' => $internalCode ?? $employee->internalCode,
                'mobilePhone' => $mobilePhone ?? $employee->mobilePhone,
                'roomNumber' => $roomNumber ?? $employee->roomNumber,
                'workingPosition' => $workingPosition ?? $employee->workingPosition
            ]);
            $updateCount++;
        }

        return response()->json([
            'updateCount' => $updateCount,
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function phoneBook(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        $search = Str::lower($search);
        $perPage = (int)$request->input('perPage', 10);
        $page = (int)$request->input('page', 1);
        $sortBy = $request->input('sortBy', 'fullName');
        if ($sortBy === null) $sortBy = 'id';
        $sortDesc = $request->input('sortDesc', false);
        $sortDesc = $sortDesc === "true";

        $employees = Employee::query()->where(function ($q) {
            $q->Where('internalCode', '!=', '')
                ->orWhere('mobilePhone', '!=', '');
        });
        $employees->where(function ($q) use ($search) {
            $q->Where(DB::raw('LOWER(fullName)'), 'like', "%$search%")
                ->orWhere(DB::raw('LOWER(workingPosition)'), 'like', "%$search%");
        });

        $employees = $employees->get();

        if ($sortBy === 'department') {
            $employees = $employees->sortBy(function ($employee) {
                return $employee->department->name;
            }, 0, $sortDesc);
        } else {
            $employees = $employees->sortBy($sortBy, 0, $sortDesc);
        }

        $totalCount = $employees->count();
        $employees = $employees->skip($page * $perPage - $perPage)->take($perPage);
        return response()->json([
            'total' => $totalCount,
            'employees' => EmployeeResource::collection($employees)
        ]);
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
        $sortBy = $request->input('sortBy', 'fullName');
        if ($sortBy === null) $sortBy = 'id';
        $sortDesc = $request->input('sortDesc', false);
        $sortDesc = $sortDesc === "true";
        $search = Str::lower($search);
        $department_id = $request->input('department_id', null);
        $isHaveInternalCode = $request->input('isHaveInternalCode', '2') === '1';
        $gender = $request->get('gender');
        $education = $request->get('education');
        $ageMin = $request->get('age_from');
        $ageMax = $request->get('age_to');
        $isFoundersRepresentative = $request->get('founders_representative') ;

        $employees = Employee::query();
        $employees->where(function ($q) use ($search) {
            $q->Where(DB::raw('LOWER(fullName)'), 'like', "%$search%")
                ->orWhere(DB::raw('LOWER(workingPosition)'), 'like', "%$search%");
        });

        if ($isHaveInternalCode) $employees->where('internalCode', "!=", '');

        if (!is_null($isFoundersRepresentative)) {
            $employees->whereNull('founders_representative_date', 'and', $isFoundersRepresentative === "true");
        }

        if (!is_null($gender)) {
            $employees->whereNull('gender', $gender);
        }

        if (!is_null($education)) {
            $employees->where('education', 'like',  "%$education%");
        }

        if (!is_null($ageMin)) {
            $employees->where('date_of_birth','<=', Carbon::now()->subYears($ageMin));
        }

        if (!is_null($ageMax)) {
            $employees->where('date_of_birth', '>=', Carbon::now()->subYears($ageMax));
        }

        $employees = $employees->get();

        if ($department_id !== null) {
            $employees = $employees->filter(function ($q) use ($department_id) {
                if ($q->department_id === $department_id) return true;

                if ($q->department->parent_id === null || $q->department->parent_id === '') return false;

                if ($q->department->parent_id === $department_id) return true;

                if ($q->department->parent->parent_id === null) return false;

                if ($q->department->parent->parent_id === $department_id) return true;

                return false;
            });
        }

        if ($sortBy === 'department') {
            $employees = $employees->sortBy(function ($employee) {
                return $employee->department->name;
            }, 0, $sortDesc);
        } else {
            $employees = $employees->sortBy($sortBy, 0, $sortDesc);
        }

        $totalCount = $employees->count();
        $employees = $employees->skip($page * $perPage - $perPage)->take($perPage);
        return response()->json([
            'total' => $totalCount,
            'employees' => EmployeeResource::collection($employees)
        ]);
    }

    public function oldImport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => ['required', 'max:50000', 'mimes:xlsx,xls,xlsm']
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

        $updateCount = 0;
        $createCount = 0;
        $employeeIdsList = [];
        $employees = Employee::all();
        foreach ($list as $index => $row) {
            if ($index === 0 || $index === 1) continue;

            $employeeId = (string)$row[0];
            $employeeIdsList[] = $employeeId;
            $searchEmployee = $employees->find($employeeId);
            $fullName = trim($row[2]) . ' ' . trim($row[1]);
            if ($searchEmployee === null) {
                Employee::query()->create([
                    'id' => $row[0],
                    'fullName' => $fullName,
                    'department_id' => $row[3]
                ]);
                $createCount++;
                continue;
            }

            if ($searchEmployee->fullName !== $fullName || $searchEmployee->department_id !== $row[3]) {
                $searchEmployee->fullName = $fullName;
                $searchEmployee->department_id = $row[3];
                $searchEmployee->save();
                $updateCount++;
            }
        }

        $deleteEmployee = $employees->whereNotIn('id', $employeeIdsList);
        foreach ($deleteEmployee as $item) $item->delete();

        return response()->json([
            'updateCount' => $updateCount,
            'createCount' => $createCount,
            'deleteCount' => $deleteEmployee->count()
        ]);
    }


    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => ['required', 'max:50000', 'mimes:xlsx,xls,xlsm']
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

        $updateCount = 0;
        $createCount = 0;
        $employees = Employee::all();
        foreach ($list as $index => $row) {
            if ($index === 0 || $index === 1) continue;
            if (empty($row[7])) {
                continue;
            }
            $fullName = trim($row[7]);
            $searchEmployee = $employees->where('fullName', $fullName)->first();

            if ($searchEmployee === null) {
                $createCount++;
                continue;
            }
            if (!empty($row[8])) {
                $timestamp = ($row[8] - 25569) * 86400;
                $searchEmployee->date_of_birth = Carbon::createFromTimestamp($timestamp);
            }
            $searchEmployee->workingPosition = $row[6] ?: null;
            $searchEmployee->gender = $row[9] ?: null;
            $searchEmployee->education = $row[10] ?: null;
            if (!empty($row[14])) {
                $timestamp = ($row[14] - 25569) * 86400;
                $searchEmployee->date_of_employment = Carbon::createFromTimestamp($timestamp);
            }
            if (!empty($row[21])) {
                $timestamp = ($row[21] - 25569) * 86400;
                $searchEmployee->founders_representative_date = Carbon::createFromTimestamp($timestamp);
            }
            $searchEmployee->save();
            $updateCount++;
        }

        return response()->json([
            'updateCount' => $updateCount,
            'createCount' => $createCount,
            'deleteCount' => 0
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $employee = Employee::find($id);
        if ($employee === null)
            return response()->json(['error' => ['fullName' => 'Сотрудникк не найден']], 404);

        $validator = Validator::make($request->all(), [
            'internalCode' => 'max:4',
            'mobilePhone' => 'max:20',
            'roomNumber' => 'max:5',
        ]);

        if ($validator->fails())
            return response()->json(['error' => $validator->errors()], 400);

        $employee->update([
            'internalCode' => $request->input('internalCode') ?? '',
            'mobilePhone' => $request->input('mobilePhone') ?? '',
            'roomNumber' => $request->input('roomNumber') ?? '',
            'workingPosition' => $request->input('workingPosition') ?? '',
            'gender' => $request->input('gender'),
            'date_of_birth' => $request->input('dateOfBirth'),
            'education' => $request->input('education'),
            'founders_representative_date' => $request->input('foundersRepresentativeDate') ,
            'date_of_employment' => $request->input('dateOfEmployment'),
        ]);

        return response()->json(['message' => 'Информация о сотруднике обновлена']);
    }
}
