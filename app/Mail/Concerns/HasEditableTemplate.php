<?php

namespace App\Mail\Concerns;

use App\Models\EmailTemplate;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Support\Facades\Blade;

trait HasEditableTemplate
{
    protected function editableContent(string $templateKey, string $defaultView): Content
    {
        $dbBody = EmailTemplate::getBody($templateKey);

        if ($dbBody !== null) {
            $html = Blade::render(
                '@extends("layouts.email") @section("content")' . $dbBody . '@endsection',
                $this->templateData()
            );
            return new Content(htmlString: $html);
        }

        return new Content(view: $defaultView);
    }

    protected function editableSubject(string $templateKey, string $default): string
    {
        $dbSubject = EmailTemplate::getSubject($templateKey);

        if ($dbSubject !== null) {
            // Replace simple {{variable}} placeholders in subject
            $data = $this->templateData();
            return preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($data) {
                return $data[$matches[1]] ?? $matches[0];
            }, $dbSubject);
        }

        return $default;
    }

    abstract protected function templateData(): array;
}
