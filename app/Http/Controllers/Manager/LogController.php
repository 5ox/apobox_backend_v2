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
            sort($dailyFiles);
            $path = end($dailyFiles);
        } elseif (File::exists($logDir . $name . '.log')) {
            $path = $logDir . $name . '.log';
        } else {
            // Diagnostics: show what files exist so we can debug
            $allFiles = glob($logDir . '*.log');
            if (empty($allFiles)) {
                return "No log files found in {$logDir}\n\n"
                    . "Directory exists: " . (is_dir($logDir) ? 'yes' : 'NO') . "\n"
                    . "Writable: " . (is_writable($logDir) ? 'yes' : 'NO') . "\n"
                    . "Log channel: " . config('logging.default') . "\n"
                    . "Stack channels: " . implode(', ', config('logging.channels.stack.channels', []));
            }

            $fileList = array_map(
                fn ($f) => basename($f) . ' (' . number_format(filesize($f)) . ' bytes)',
                $allFiles
            );
            return "No '{$name}' log files found.\n\nAvailable log files:\n- " . implode("\n- ", $fileList);
        }

        $lines = collect(file($path));

        if ($lines->isEmpty()) {
            return "Log file exists but is empty: " . basename($path);
        }

        return $lines->slice(-500)->implode('');
    }
}
