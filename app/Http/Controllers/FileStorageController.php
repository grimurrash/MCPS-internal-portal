<?php

namespace App\Http\Controllers;

use App\Http\Resources\FileStorageResource;
use App\Models\FileStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FileStorageController extends Controller
{
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
        $sortDesc = $request->input('sortDesc', false);

        $search = Str::lower($search);
        $fileStorages = FileStorage::query()->Where(DB::raw('LOWER(name)'), 'like', "%$search%")
            ->orderBy($sortBy, $sortDesc === "true" ? 'desc' : 'asc')->get();

        $totalCount = $fileStorages->count();
        $fileStorages = $fileStorages->skip($page * $perPage - $perPage)->take($perPage);

        return response()->json([
            'total' => $totalCount,
            'fileStorage' => FileStorageResource::collection($fileStorages)
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $valid = Validator::make($request->all(), [
            'name' => ['required', 'max:255'],
            'description' => ['required'],
            'file' => ['required']
        ]);

        if($valid->fails()) return response()->json(['error' => $valid->errors()], 400);
        $name = $request->input('name');
        $description = $request->input('description');
        $file = $request->file('file');
        $fileStorage = FileStorage::create([
            'name' => $name,
            'description' => $description,
            'filePath' => '',
            'extension' => $file->getClientOriginalExtension(),
        ]);

        $filePath = '/file-storage/file-' . $fileStorage->id . '.' . $file->getClientOriginalExtension();
        Storage::put($filePath, file_get_contents($file->getRealPath()));
        $fileStorage->filePath = $filePath;
        $fileStorage->save();
        return response()->json([
            'message' => 'Файл добавлен!'
        ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $fileStorage = FileStorage::find($id);
        Storage::delete($fileStorage->filePath);
        $fileStorage->delete();
        return response()->json(['message' => 'Файл удален!']);
    }
}
