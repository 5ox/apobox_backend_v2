<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends Controller
{
    /**
     * Display a static page by slug.
     *
     * Routes like /pages/faq, /pages/about render the corresponding Blade
     * view under resources/views/pages/. Blocks any request where the slug
     * starts with an admin routing prefix (e.g., manager_, employee_) to
     * prevent unauthorized access to admin-only pages.
     */
    public function display(?string $page = null): View
    {
        if (empty($page)) {
            abort(404);
        }

        // Block prefixed page names (e.g., manager_dashboard)
        $adminPrefixes = ['manager_', 'employee_', 'api_'];
        foreach ($adminPrefixes as $prefix) {
            if (str_starts_with($page, $prefix)) {
                abort(404, 'Invalid page');
            }
        }

        $title = str_replace(['-', '_'], ' ', ucfirst($page));

        // Check if a view exists for this page
        $viewPath = 'pages.' . str_replace('/', '.', $page);
        if (! view()->exists($viewPath)) {
            abort(404);
        }

        return view($viewPath, [
            'page' => $page,
            'title' => $title,
        ]);
    }

    /**
     * Display the Terms of Service page.
     *
     * Fetches the TOS content from apobox.com, extracts the <pre> content,
     * and caches it for 24 hours.
     */
    public function tos(): View
    {
        $content = Cache::remember('tos_content', 86400, function () {
            try {
                $response = Http::timeout(10)->get('https://www.apobox.com', ['page_id' => 3140]);

                if (! $response->ok()) {
                    return '';
                }

                $html = $response->body();

                // Extract <pre> content
                $dom = new \DOMDocument();
                libxml_use_internal_errors(true);
                $dom->loadHTML($html);
                libxml_clear_errors();

                $extracted = '';
                foreach ($dom->getElementsByTagName('pre') as $node) {
                    $extracted .= $dom->saveHtml($node);
                }

                return $extracted;
            } catch (\Exception $e) {
                return '';
            }
        });

        return view('pages.tos', compact('content'));
    }

    /**
     * Display the developers widget documentation page.
     *
     * Reads the signup widget HTML file and displays it in a formatted view.
     */
    public function developersWidget(): View
    {
        $title = 'Developer Documentation';

        $widgetPath = public_path('widgets/signup.html');
        $content = '';

        if (file_exists($widgetPath)) {
            $content = file_get_contents($widgetPath);
        }

        return view('pages.developers-widget', compact('content', 'title'));
    }
}
