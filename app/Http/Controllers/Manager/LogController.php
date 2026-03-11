<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class LogController extends Controller
{
    /**
     * View application log files.
     */
    public function view(?string $log = 'email'): View
    {
        $logDir = storage_path('logs/');

        // Sanitize filename to prevent path traversal
        $log = preg_replace('/[^a-zA-Z0-9_\-]/', '', $log);

        $logPath = $logDir . $log . '.log';
        $logFile = 'There is currently no log data to display.';

        if (File::exists($logPath)) {
            // Read last 500 lines to avoid memory issues with large logs
            $lines = collect(file($logPath));
            $logFile = $lines->slice(-500)->implode('');
        }

        return view('manager.logs.view', compact('log', 'logFile'));
    }
}
