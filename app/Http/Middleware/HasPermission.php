<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @param $permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permissions)
    {
        $permissions_array = explode('|', $permissions);
        foreach ($permissions_array as $permission) {
            if ($request->user()->hasPermission($permission)) {
                return $next($request);
            }
        }
        return response()->json(['message' => 'Недостаточно прав.'], 403);

    }
}
