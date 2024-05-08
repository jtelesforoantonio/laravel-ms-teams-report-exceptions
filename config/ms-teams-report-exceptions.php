<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook Url
    |--------------------------------------------------------------------------
    |
    | Create Incoming Webhooks:
    | https://learn.microsoft.com/en-us/microsoftteams/platform/webhooks-and-connectors/how-to/add-incoming-webhook
    |
    */
    'webhook_url' => env('MS_TEAMS_REPORT_EXCEPTIONS_WEBHOOK_URL'),
    'card_title' => env('MS_TEAMS_REPORT_EXCEPTIONS_CARD_TITLE', sprintf('🚨 %s Exception', ucfirst(env('APP_NAME')))),
    'enabled' => env('MS_TEAMS_REPORT_EXCEPTIONS_ENABLED', false),
];