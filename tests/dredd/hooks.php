<?php

/**
 * @file
 *
 * Hooks for running Dredd tests.
 */

use Dredd\Hooks;
use Guzzle\Client;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

$client = new GuzzleHttp\Client([
    'base_uri' => 'http://0.0.0.0:8080',
    'headers' => [
        'Authorization' => 'Bearer test-user',
    ],
]);

/**
 * Replace elements in requested path.
 */
$pathReplace = function ($transaction, $from, $to) {
    $replacements = [$from => $to];
    $transaction->request->uri = strtr($transaction->request->uri, $replacements);
    $transaction->fullPath = strtr($transaction->fullPath, $replacements);
    // Also fix the ID so the user can see the change.
    $transaction->id = strtr($transaction->id, $replacements);
};

/* @var \Laravel\Lumen\Application $app */
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->boot();
$artisan = $app->make(ConsoleKernel::class);

Hooks::beforeAll(function (&$transaction) use ($artisan) {
    $artisan->call('migrate:fresh');
    // Print the resulting output so it is picked out by Dredd for debugging.
    echo $artisan->output();
});

Hooks::beforeEach(function ($transaction) {
    $transaction->request->headers->Authorization = 'Bearer test-user';

    // Skip internal error responses, we can't trigger those and HEAD requests
    // which dredd doesn't support.
    if (preg_match('/(500|HEAD > \d{3})$/', $transaction->name)) {
        $transaction->skip = true;
    }
});

Hooks::before('/list/{listName}/{searchId} > DELETE > 204', function ($transaction) {
    // Use an ID that exists.
    $replacements = ['42' => '1'];
    $transaction->request->uri = strtr($transaction->request->uri, $replacements);
    $transaction->fullPath = strtr($transaction->fullPath, $replacements);
});

Hooks::before('/list/{listName}/{searchId} > GET > 200 > application/json', function ($transaction) {
    // Use an ID that exists.
    $replacements = ['42' => '1'];
    $transaction->request->uri = strtr($transaction->request->uri, $replacements);
    $transaction->fullPath = strtr($transaction->fullPath, $replacements);

    // Fix the expection to what the SearchTesting search provider will return.
    $transaction->expected->body = '{"materials":[{"pid":"pid 1"},{"pid":"pid 0"}]}';
});
