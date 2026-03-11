<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Show the reports index/dashboard.
     */
    public function index(Request $request): View
    {
        // TODO: Port from CakePHP
        return view('manager.reports.index');
    }
}
