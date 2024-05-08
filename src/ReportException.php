<?php

namespace JTelesforoAntonio\LaravelMsTeamsReportExceptions;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ReportException
{
    /**
     * Register the listener.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function register($app)
    {
        $app['events']->listen(MessageLogged::class, [$this, 'handleException']);
    }

    /**
     * Handle exception.
     *
     * @param  \Illuminate\Log\Events\MessageLogged  $event
     * @return void
     */
    public function handleException($event)
    {
        if (isset($event->context['exception'])) {
            $exception = $event->context['exception'];
            $location = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
            if (Str::contains($location['file'], 'vendor')) {
                $location = collect($exception->getTrace())
                    ->first(fn ($frame) => !Str::contains($frame['file'], base_path('vendor')));
            }
            $payload = [
                'message' => $exception->getMessage(),
                'location' => $location['file'] . ':' . $location['line'],
                'context' => transform(Arr::except($event->context, ['exception']), function ($context) {
                    return !empty($context) ? $context : null;
                }),
                'line_preview' => $this->getFileContext($location['file'], $location['line']),
            ];
            $this->reportException($payload);
        }
    }

    /**
     * Get the exception code from a file.
     *
     * @param  string  $file
     * @param  int  $line
     * @return mixed
     */
    public function getFileContext($file, $line)
    {
        return collect(explode("\n", file_get_contents($file)))
            ->slice($line - 10, 20)
            ->mapWithKeys(fn ($value, $key) => [$key + 1 => $value])
            ->all();
    }

    /**
     * Send the exception to MS Teams.
     *
     * @param  array  $payload
     * @return void
     */
    public function reportException($payload)
    {
        $fields = Arr::except($payload, ['line_preview']);
        $facts = [];
        foreach ($fields as $key => $value) {
            $facts[] = [
                'name' => "$key:",
                'value' => is_array($value) ? json_encode($value) : $value,
            ];
        }
        $lines = '';
        foreach ($payload['line_preview'] as $key => $line) {
            $lines .= "$key $line\r";
        }
        $messageCard = [
            '@context' => 'https://schema.org/extensions',
            '@type' => 'MessageCard',
            'themeColor' => '#dc3545',
            'title' => config('ms-teams-report-exceptions.card_title'),
            'summary' => config('app.name'),
            'sections' => [
                [
                    'facts' => $facts,
                ],
                [
                    'activityTitle' => 'line preview:',
                    'text' => "```php\r" . $lines . "\r```",
                ],
            ],
        ];

        Http::post(config('ms-teams-report-exceptions.webhook_url'), $messageCard);
    }
}
