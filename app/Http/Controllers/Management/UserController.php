<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Resources\AbilityResource;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserDataResource;
use App\Http\Resources\UsersTableResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
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
        $role = $request->input('role_id');
        $permission = $request->input('permission_id');

        $search = Str::lower($search);
        $users = User::query();

        $users->where(function ($q) use ($search) {
            $q->Where(DB::raw('LOWER(fullName)'), 'like', "%$search%")
                ->orWhere(DB::raw('LOWER(email)'), 'like', "%$search%");
        });

        if ($role !== null) {
            $users->where('role_id', '=', $role);
        }
        if ($permission !== null) {
            $users->whereHas('permissions', function ($q) use ($permission) {
                $q->where('permission_id', '=', $permission);
            });
        }

        $users->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
        $users = $users->get();
        $totalCount = $users->count();
        $users = $users->skip($page * $perPage - $perPage)->take($perPage);
        return response()->json([
            'users' => UsersTableResource::collection($users),
            'total' => $totalCount
        ])->setStatusCode(200);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $user = User::find($id);
        if ($user === null) {
            return response()->json([
                'message' => 'Мы не можем найти пользователя с таким id.'
            ], 401);
        }
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'avatar' => $user->avatar ? Storage::url($user->avatar) : '',
            'fullName' => $user->fullName,
            'role_id' => $user->role_id,
            'role_name' => $user->role->name,
            'ability' => AbilityResource::collection($user->permissions)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse|object
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'unique:users', 'max:255'],
            'fullName' => ['required'],
            'role_id' => ['required']
        ], [
            'email.required' => 'Введите электронную почту',
            'email.unique' => 'Пользователь с такой электронной почтой уже существует',
            'email.max' => 'Длина электронной почты не должна быть более 255 символов',
            'fullName.required' => 'Введите ФИО',
            'role_id.required' => 'Выберите роль',
        ]);

        $email = $request->input('email');
        $role_id = $request->input('role_id');

        $role = Role::find($role_id);

        $user = User::create([
            'fullName' => $request->input('fullName'),
            'email' => $email,
            'password' => bcrypt(explode('@', $email)[0] . Carbon::today()->format('dmyHi')),
            'role_id' => $role_id
        ]);
        $user->permissions()->attach($role->permissions);

        return response()->json([
            'status' => true,
            'user_id' => $user->id
        ])->setStatusCode(200);
    }

    public function listStore(Request $request)
    {
        $loadFile = $request->file('file');
        $loadFileName = 'temp/userListStore.' . $loadFile->getClientOriginalExtension();
        Storage::put($loadFileName, file_get_contents($loadFile->getRealPath()));

        $loadFilePath = storage_path('app/public') . '/' . $loadFileName;

        $excel = IOFactory::load($loadFilePath);
        $worksheet = $excel->getActiveSheet();
        $loadDataList = $worksheet->toArray();
        $excel->disconnectWorksheets();
        unset($excel);
        unlink($loadFilePath);

        $permission_id = $request->input('permission_id', null);
        $role_id = $request->input('role_id', 1);

        $role = Role::find($role_id);
        $permission = null;
        if ($permission_id !== null) {
            $permission = Permission::find($permission_id);
        }

        $createUsers = [];
        foreach ($loadDataList as $index => $userData) {
            if ($index < 1) continue;
            $fullName = trim($userData[0]);
            $email = trim($userData[1]);
            $user = User::where('email', $email)->first();

            if ($user !== null) {
                if ($permission != null && !$user->permissions->contains($permission)) {
                    $user->permissions()->attach($permission);
                }
                $createUsers[] = [
                    'status' => 'Существует',
                    'email' => $email,
                    'fullName' => $fullName,
                    'password' => ''
                ];
            } else {
                $password = explode('@', $email)[0] . Carbon::today()->format('dmyHi');
                $user = User::create([
                    'fullName' => $fullName,
                    'email' => $email,
                    'password' => bcrypt($password),
                    'role_id' => $role_id
                ]);
                $user->permissions()->attach($role->permissions);
                if ($permission != null) {
                    $user->permissions()->attach($permission);
                }
                $createUsers[] = [
                    'status' => 'Зарегистрирован',
                    'email' => $email,
                    'fullName' => $fullName,
                    'password' => $password
                ];
            }
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'ФИО')
            ->setCellValue('B1', 'Почта')
            ->setCellValue('C1', 'Пароль')
            ->setCellValue('D1', 'Стьатус');
        $row = 2;
        foreach ($createUsers as $createInfo) {
            $sheet->setCellValue('A' . $row, $createInfo['fullName']);
            $sheet->setCellValue('B' . $row, $createInfo['email']);
            $sheet->setCellValue('C' . $row, $createInfo['password']);
            $sheet->setCellValue('D' . $row, $createInfo['status']);
            $row++;
        }
        foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $tempPath = storage_path('app/public/temp/importUserList.xlsx');
        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempPath);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
        $spreadsheet->disconnectWorksheets();
        return response()->download($tempPath)->deleteFileAfterSend();
    }


    /**
     * @param Request $request
     * @param $userId
     * @return JsonResponse
     */
    public function update(Request $request, $userId): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'unique:users,email,' . $userId, 'max:255'],
            'fullName' => ['required'],
            'role_id' => ['required']
        ], [
            'email.required' => 'Введите электронную почту',
            'email.unique' => 'Пользователь с такой электронной почтой уже существует',
            'email.max' => 'Длина электронной почты не должна быть более 255 символов',
            'fullName.required' => 'Введите ФИО',
            'role_id.required' => 'Выберите роль',
        ]);
        $user = User::find($userId);

        if ($user === null) {
            return response()->json([
                'message' => 'Мы не можем найти пользователя с таким id.'
            ], 401);
        }
        $avatarPath = $user->avatar;
        if ($request->hasFile('avatar') && $request->input('isUploadAvatar') === 'true') {
            $uploadedFile = $request->file('avatar');
            Storage::delete($user->avatar);
            $avatarPath = 'avatars/user-avatar-' . $userId . '.' . $uploadedFile->getClientOriginalExtension();
            Storage::put($avatarPath, file_get_contents($uploadedFile->getRealPath()));
        }
        $user->email = $request->input('email');
        $user->fullName = $request->input('fullName');
        $user->role_id = $request->input('role_id');
        $user->avatar = $avatarPath ?? null;
        $user->save();
        $permissions = json_decode($request->input('permissions'), true);
        $permissionSlugs = array_map(static function ($perm) {
            return $perm['id'];
        }, $permissions);
        $user->refreshPermissions(...$permissionSlugs);
        return response()->json([
            'message' => 'Данные пользователя обновлены!'
        ]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse|object
     */
    public function destroy(int $id)
    {
        $user = User::find($id);
        if ($user === null)
            return response()->json(['status' => false], 404);

        $user->delete();
        return response()->json(['status' => true]);
    }

    public function usersOptions(): JsonResponse
    {
        return response()->json(['users' => UserDataResource::collection(User::all())]);
    }

    /**
     * @return JsonResponse
     */
    public function selectOptions(): JsonResponse
    {
        $roles = Role::all();
        $permissions = Permission::all();
        return response()->json([
            'roles' => RoleResource::collection($roles),
            'permissions' => PermissionResource::collection($permissions)
        ]);
    }
}