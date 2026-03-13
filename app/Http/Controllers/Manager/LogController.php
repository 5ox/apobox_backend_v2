<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class LogController extends Controller
{
    /**
     * Available log channels shown in the UI.
     */
    private const LOG_CHANNELS = [
        'laravel'  => ['icon' => 'file-text',    'label' => 'Laravel'],
        'email'    => ['icon' => 'mail',          'label' => 'Email'],
        'payment'  => ['icon' => 'credit-card',   'label' => 'Payment'],
        'shipping' => ['icon' => 'truck',         'label' => 'Shipping'],
        'auth'     => ['icon' => 'shield',        'label' => 'Auth'],
        'zendesk'  => ['icon' => 'message-circle', 'label' => 'Zendesk'],
    ];

    /**
     * View application log files.
     */
    public function view(?string $log = 'laravel'): View
    {
        // Sanitize filename to prevent path traversal
        $log = preg_replace('/[^a-zA-Z0-9_\-]/', '', $log);

        $logFile = $this->readLatestLog($log);

        $channels = self::LOG_CHANNELS;

        return view('manager.logs.view', compact('log', 'logFile', 'channels'));
    }

    /**
     * Find and read the latest log file for a channel.
     *
     * The daily driver creates files like "email-2026-03-12.log".
     * We glob for all matching files and read the most recent one.
     */
    private function readLatestLog(string $name): string
    {
        $logDir = storage_path('logs/');

        // Try daily-rotated files first (e.g. email-2026-03-12.log)
        $pattern = $logDir . $name . '-*.log';
        $dailyFiles = glob($pattern);

        if (!empty($dailyFiles)) {
            sort($dailyFiles); // Alphabetical = chronological for date-suffixed files
            $path = end($dailyFiles); // Latest file
        } elseif (File::exists($logDir . $name . '.log')) {
            // Fall back to single-driver file (e.g. email.log)
            $path = $logDir . $name . '.log';
        } else {
            return 'There is currently no log data to display.';
        }

        $lines = collect(file($path));

        return $lines->slice(-500)->implode('');
    }
}
