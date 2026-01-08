<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\GroqClient;
use Illuminate\Http\Client\RequestException;

echo "Using model: " . config('groq.model') . PHP_EOL;

try {
    $client = $app->make(GroqClient::class);
    $messages = [
        ['role' => 'system', 'content' => 'System test'],
        ['role' => 'user', 'content' => 'Halo'],
    ];

    $resp = $client->chat($messages, ['temperature' => 0.1, 'max_tokens' => 20]);

    echo "HTTP OK, response keys: " . implode(', ', array_keys($resp)) . PHP_EOL;
    echo json_encode($resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(0);
} catch (RequestException $e) {
    $status = $e->response ? $e->response->status() : 'n/a';
    $body   = $e->response ? $e->response->body() : $e->getMessage();
    echo "RequestException: status={$status}\n";
    echo $body . PHP_EOL;
    exit(2);
} catch (Throwable $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
    exit(3);
}
