<?php

namespace App\Http\Controllers\Organizers;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizerMenuResource;
use App\Models\OrganizerItem;
use App\Models\OrganizerMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizerController extends Controller
{
    public function show(Request $request, int $id): JsonResponse
    {
       $menus = OrganizerMenu::where('organizer_id', $id)->get();
       return response()->json(OrganizerMenuResource::collection($menus));
    }

    public function storeOrganizerMenu(Request $request): JsonResponse
    {
        $name = $request->input('name');
        $organizer_id = $request->input('organizer_id');
        $menus = OrganizerMenu::query()->where('organizer_id', $organizer_id)->where('name', $name)->get();
        if (count($menus) > 0) {
            return response()->json(['status' => false, 'error'=> 'Меню с таким названием уже существует'], 401);
        }
        OrganizerMenu::query()->create([
            'name' => $name,
            'organizer_id'=> $organizer_id
        ]);
        return response()->json(['status' => true]);
    }

    public function updateOrganizerMenu(Request $request, int $organizerMenuId): JsonResponse {
        $name = $request->input('name');
        $organizer_id = $request->input('organizer_id');
        $menu = OrganizerMenu::find($organizerMenuId);
        $menu->update([
            'name' => $name,
            'organizer_id'=> $organizer_id
        ]);
        return response()->json(['status'=> true]);
    }

    public function storeOrganizerItem(Request $request): JsonResponse {
        $name = $request->input('name');
        $menu_id = $request->input('menu_id');
        $link = $request->input('link');
        $organizerItems = OrganizerItem::query()->where('menu_id', $menu_id)->where('name', $name)->get();
        if (count($organizerItems) > 0) {
            return response()->json(['status'=> false, 'error'=> 'Ссылка с таким наименованием уже существует'], 401);
        }
        OrganizerItem::query()->create([
            'name' => $name,
            'link'=> $link,
            'menu_id' => $menu_id
        ]);
        return response()->json(['status'=> true]);
    }

    public function updateOrganizerItem(Request $request, $organizerItemId): JsonResponse {
        $name = $request->input('name');
        $menu_id = $request->input('menu_id');
        $link = $request->input('link');
        $organizerItem = OrganizerItem::find($organizerItemId);
        $organizerItem->update([
            'name' => $name,
            'link'=> $link,
            'menu_id' => $menu_id
        ]);
        return response()->json(['status'=> true]);
    }

    public function deleteOrganizerMenu(Request $request, int $id): JsonResponse
    {
        $organizerMenu = OrganizerMenu::query()->find($id);
        try {
            $organizerMenu->delete();
            return response()->json(['status'=> true]);
        } catch (\Exception $e) {
            return response()->json(['status'=> false]);
        }
    }

    public function deleteOrganizerItem(Request $request, int $id): JsonResponse
    {
        $organizerItem = OrganizerItem::query()->find($id);
        try {
            $organizerItem->delete();
            return response()->json(['status'=> true]);
        } catch (\Exception $e) {
            return response()->json(['status'=> false]);
        }
    }
}
