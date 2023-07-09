<?php

namespace App\Http\Controllers\Organizers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrganizationProjectRequest;
use App\Http\Requests\GetOrganizationProjectsRequest;
use App\Http\Requests\UpdateOrganizationProjectRequest;
use App\Http\Resources\OrganizationProject\OrganizationProjectInfoResource;
use App\Http\Resources\OrganizationProject\OrganizationProjectListItemResource;
use App\Models\OrganizationProject;
use App\Models\User;
use Auth;
use Illuminate\Http\JsonResponse;

class OrganizationProjectController extends Controller
{

    public function organizerList(): JsonResponse
    {
        $users = User::query()
            ->where('role_id', 9)
            ->orderBy('fullName')
            ->get(['id', 'fullName']);
        return response()->json($users->toArray());
    }

    public function curatorList(): JsonResponse
    {
        $users = User::query()
            ->where('role_id', 2)
            ->orderBy('fullName')
            ->get(['id', 'fullName']);
        return response()->json($users->toArray());
    }

    public function index(GetOrganizationProjectsRequest $request): JsonResponse
    {
        $userId = Auth::id();

        $sortBy = $request->get('sort_by', 'start_date');
        $sortDirection = $request->get('sort_dir', 'desc');

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $projects = OrganizationProject::query()
            ->where('organizer_id', $userId)
            ->with(['curator', 'organizer', 'responsibleEmployee'])
            ->orderBy($sortBy, $sortDirection);

        if (!is_null($startDate)) {
            $projects->where('start_date', $startDate);
        }

        if (!is_null($endDate)) {
            $projects->where('end_date', $endDate);
        }

        $projects = $projects->get();

        return response()->json(OrganizationProjectListItemResource::collection($projects));
    }
    public function indexAll(GetOrganizationProjectsRequest $request): JsonResponse
    {
        $sortBy = $request->get('sort_by', 'start_date');
        $sortDirection = $request->get('sort_dir', 'desc');

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $projects = OrganizationProject::query()
            ->with(['curator', 'organizer', 'responsibleEmployee'])
            ->orderBy($sortBy, $sortDirection);

        if (!is_null($startDate)) {
            $projects->where('start_date', $startDate);
        }

        if (!is_null($endDate)) {
            $projects->where('end_date', $endDate);
        }

        $projects = $projects->get();

        return response()->json(OrganizationProjectListItemResource::collection($projects));
    }

    public function show(int $id): JsonResponse
    {
        $project = OrganizationProject::where('id', $id)->first();
        if ($project === null) {
            return response()->json(['message' => 'Проекта с таким id не существует'], 401);
        }
        $userId = Auth::id();
        if (!in_array($userId, [$project->organizer_id, $project->curator_id, $project->responsible_employee_id], true)) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }

        return response()->json(OrganizationProjectInfoResource::make($project));
    }

    public function store(CreateOrganizationProjectRequest $request): JsonResponse
    {
        $data = [
            'name' => $request->get('name'),
            'number' => $request->get('number'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'responsible_employee_id' => $request->get('responsible_employee_id'),
            'description' => $request->get('description'),
            'metrics' => $request->get('metrics'),
            'planned_coverage' => $request->get('planned_coverage'),
            'actual_coverage' => $request->get('actual_coverage'),
            'curator_id' => $request->get('curator_id'),
            'organizer_id' => Auth::id(),
            'status' => $request->get('status')
        ];

        OrganizationProject::create($data);

        return response()->json(['status' => true]);
    }

    public function update(UpdateOrganizationProjectRequest $request, $projectId): JsonResponse
    {
        $project = OrganizationProject::where('id', $projectId)->first();
        if (Auth::id() !== $project->organizer_id) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }
        $data = $request->all();

        if (is_null($project)) {
            return response()->json(['message' => 'Проекта с таким id не существует'], 401);
        }

        $project->update($data);

        return response()->json([
            'message' => 'Данные пользователя обновлены!'
        ]);
    }
}
