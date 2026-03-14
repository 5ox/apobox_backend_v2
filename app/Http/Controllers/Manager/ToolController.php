<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class ToolController extends Controller
{
    /**
     * Available commands that can be run from the web UI.
     */
    protected array $commands = [
        'apply-storage-fees' => [
            'command' => 'app:apply-storage-fees',
            'label' => 'Apply Storage Fees',
            'description' => 'Accrue daily storage fees on warehouse orders past grace period and auto-charge customers.',
            'icon' => 'warehouse',
            'confirm' => 'This will charge customers with overdue storage. Run dry-run first?',
            'options' => ['dry-run'],
        ],
        'apply-storage-fees-dry' => [
            'command' => 'app:apply-storage-fees',
            'args' => ['--dry-run' => true],
            'label' => 'Storage Fees (Dry Run)',
            'description' => 'Preview storage fee calculations without making any changes.',
            'icon' => 'eye',
            'confirm' => null,
        ],
        'customer-reminders-awaiting' => [
            'command' => 'app:customer-reminders',
            'args' => ['--awaiting-payment' => true],
            'label' => 'Send Payment Reminders',
            'description' => 'Send reminder emails to customers with orders awaiting payment.',
            'icon' => 'mail',
            'confirm' => 'This will send emails to customers. Continue?',
        ],
    ];

    /**
     * Show the tools page with available commands.
     */
    public function index(): View
    {
        return view('manager.tools.index', [
            'commands' => $this->commands,
        ]);
    }

    /**
     * Run a registered artisan command and show the output.
     */
    public function run(Request $request, string $command): RedirectResponse
    {
        if (!isset($this->commands[$command])) {
            session()->flash('message', 'Unknown command.');
            return redirect()->route(auth('admin')->user()->role . '.tools.index');
        }

        $config = $this->commands[$command];
        $args = $config['args'] ?? [];

        Artisan::call($config['command'], $args);
        $output = Artisan::output();

        session()->flash('message', 'Command completed.');
        session()->flash('tool_output', $output);

        return redirect()->route(auth('admin')->user()->role . '.tools.index');
    }

}
