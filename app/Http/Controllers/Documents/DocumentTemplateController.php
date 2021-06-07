<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Resources\TemplateResource;
use App\Models\DocumentTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentTemplateController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function optionsList(Request $request): JsonResponse
    {
        $templates = DocumentTemplate::query()->select('id','name')->get();
        return response()->json(['templates' => $templates]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        $perPage = (int)$request->input('perPage', 10);
        $page = (int)$request->input('page', 1);
        $sortBy = $request->input('sortBy', 'id');
        $sortDesc = $request->input('sortDesc', false);
        $type = $request->input('type_id', null);
        $isDouble = $request->input('isDouble', null);

        $search = Str::lower($search);
        $templates = DocumentTemplate::query();

        $templates->where(function ($q) use ($search) {
            $q->Where(DB::raw('LOWER(name)'), 'like', "%$search%")
                ->orWhere(DB::raw('LOWER(description)'), 'like', "%$search%");
        });

        if ($type !== null) {
            $templates->where('type_id', '=', $type);
        }

        if ($isDouble !== null) {
            $templates->where('isDouble', '=', $isDouble);
        }

        $templates->orderBy($sortBy, $sortDesc === "true" ? 'desc' : 'asc');

        $templates = $templates->get();
        $totalCount = $templates->count();
        $templates = $templates->skip($page * $perPage - $perPage)->take($perPage);

        return response()->json([
            'total' => $totalCount,
            'templates' => TemplateResource::collection($templates)
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'max:255'],
            'description' => ['required'],
            'documentType_id' => ['required'],
            'file' => ['required', 'max:20000', 'mimes:doc,docx']
        ]);
        $name = $request->input('name');
        $description = $request->input('description');
        $documentTypeId = $request->input('documentType_id');
        $file = $request->file('file');
        $isDouble = $request->input('isDouble', false);

        $template = DocumentTemplate::create([
            'name' => $name,
            'description' => $description,
            'type_id' => $documentTypeId,
            'filePath' => '',
            'isDouble' => $isDouble == "true" || $isDouble === 1
        ]);

        $filePath = '/documents/templates/template-' . $template->id . '.' . $file->getClientOriginalExtension();
        Storage::put($filePath, file_get_contents($file->getRealPath()));
        $template->filePath = $filePath;
        $template->save();
        return response()->json([
            'message' => 'Шаблон документа добавлен'
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'max:255'],
            'documentType_id' => ['required'],
            'description' => ['required'],
        ]);
        $name = $request->input('name');
        $description = $request->input('description');
        $documentTypeId = $request->input('documentType_id');

        $template = DocumentTemplate::find($id);
        $filePath = $template->filePath;
        if ($request->hasFile('file')) {
            $request->validate([
                'file' => ['required', 'max:20000', 'mimes:doc,docx']
            ]);
            $file = $request->file('file');
            Storage::delete($filePath);
            $filePath = 'documents/templates/template-' . $template->id . '.' . $file->getClientOriginalExtension();
            Storage::put($filePath, file_get_contents($file->getRealPath()));
        }

        $isDouble = $request->input('isDouble', false);
        $template->update([
            'name' => $name,
            'description' => $description,
            'type_id' => $documentTypeId,
            'filePath' => $filePath,
            'isDouble' => $isDouble == "true" || $isDouble === 1
        ]);

        return response()->json([
            'message' => 'Шаблон документа изменен'
        ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $template = DocumentTemplate::find($id);
        Storage::delete($template->filePath);
        $template->delete();
        return response()->json([
            'message' => 'Шаблон документа удален'
        ]);
    }
}
