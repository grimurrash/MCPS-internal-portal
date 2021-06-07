<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmployeeResource;
use App\Http\Resources\UserDataResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AccountSettingController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::query()->where('email', $user->email)->first();
        return response()->json([
            'general' => UserDataResource::make($user),
            'info' => $employee === null ? null : EmployeeResource::make($employee)
        ]);
    }

    public function updateGeneral(Request $request): JsonResponse
    {
        $valid = Validator::make($request->all(), [
            'fullName' => 'required'
        ]);

        if ($valid->fails()) return response()->json(['message' => 'ФИО не может быть пустым'], 400);

        $user = $request->user();
        $avatarPath = $user->avatar;
        $isUploadAvatar = $request->input('isUploadAvatar');
        if ($isUploadAvatar === "true") {
            if ($request->hasFile('avatar')) {
                $uploadedFile = $request->file('avatar');
                Storage::delete($user->avatar);
                $avatarPath = 'avatars/user-avatar-' . $user->id . '.' . $uploadedFile->getClientOriginalExtension();
                Storage::put($avatarPath, file_get_contents($uploadedFile->getRealPath()));
            } else {
                $avatarPath = "";
            }
        }

        $user->avatar = $avatarPath;
        $user->fullName = $request->input('fullName');
        $user->save();

        return response()->json(['message' => 'Общие данные обновлены!']);

    }

    public function updatePassword(Request $request): JsonResponse
    {
        $valid = Validator::make($request->all(), [
            'passwordValueOld' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed'],
        ]);

        if ($valid->fails()) return response()->json(['message' => 'НЕ все поля заполнены'], 400);

        $oldPassword = $request->input('passwordValueOld');
        $newPassword = $request->input('password');

        $user = $request->user();
        if (!Hash::check($oldPassword, $user->password))
            return response()->json(['error'=>['oldPassword' => 'Неверный старый пароль']], 400);

        $user->password = bcrypt($newPassword);
        $user->save();

        return response()->json([
            'message' => 'Вы успешно сменили пароль'
        ]);
    }
}
