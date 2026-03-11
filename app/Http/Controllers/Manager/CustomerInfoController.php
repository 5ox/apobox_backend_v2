<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerInfoController extends Controller
{
    /**
     * Show the customer info report.
     */
    public function report(Request $request): View
    {
        // TODO: Port from CakePHP
        return view('manager.customer-info.report');
    }
}
