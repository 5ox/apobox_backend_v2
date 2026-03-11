<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Display a static page by slug.
     */
    public function display(?string $page = null): View
    {
        // TODO: Port from CakePHP
        // Look up page by slug, render appropriate view
        return view('pages.display', compact('page'));
    }

    /**
     * Display the Terms of Service page.
     */
    public function tos(): View
    {
        // TODO: Port from CakePHP
        return view('pages.tos');
    }

    /**
     * Display the developers widget page.
     */
    public function developersWidget(): View
    {
        // TODO: Port from CakePHP
        return view('pages.developers-widget');
    }
}
