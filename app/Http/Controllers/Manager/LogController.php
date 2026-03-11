<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LogController extends Controller
{
    /**
     * View application log files.
     */
    public function view(?string $file = null): View
    {
        // TODO: Port from CakePHP
        return view('manager.logs.view', compact('file'));
    }
}
