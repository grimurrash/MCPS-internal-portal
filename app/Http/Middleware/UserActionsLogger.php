<?php

namespace App\Http\Middleware;

use App\Models\UserActionsLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class UserActionsLogger
{
    private $startTime;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @param $logType
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $logType)
    {
        $this->startTime = microtime(true);
        $request->logType = $logType;
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        if (env('USER_ACTIONS_LOGGER', true)) {
            $endTime = microtime(true);
            if (env('USER_ACTIONS_LOGGER_USE_DB', true)) {
                $log = new UserActionsLog();
                $log->user_id = $request->user()->id;
                $log->logType = $request->logType;
                $log->time = gmdate('Y-m-d H:i:s');
                $log->duration = number_format($endTime - LARAVEL_START, 3);
                $log->ipAddress = $request->ip();
                $log->url = $request->fullUrl();
                $log->method = $request->method();
                $log->input = json_encode($request->input());
                $log->save();
            } else {
                $filename = 'user_actions_logger_' . date('d-m-y') . '.log';
                $dataToLog = 'User id: ' . $request->user()->id . '; fullName: ' . $request->user()->fullName . "\n";
                $dataToLog .= 'logType: ' . $this->logLevel;
                $dataToLog .= 'Time: ' . gmdate('Y-m-d H:i:s') . "\n";
                $dataToLog .= 'Duration: ' . number_format($endTime - LARAVEL_START, 3) . "\n";
                $dataToLog .= 'IP Address: ' . $request->ip() . "\n";
                $dataToLog .= 'URL: ' . $request->fullUrl() . "\n";
                $dataToLog .= 'Method: ' . $request->method() . "\n";
                $dataToLog .= 'Input: ' . json_encode($request->input()) . "\n";

                File::append(storage_path('logs' . DIRECTORY_SEPARATOR . $filename),
                    $dataToLog . "\n" . str_repeat("=", 20) . "\n\n");
            }
        }
    }
}
