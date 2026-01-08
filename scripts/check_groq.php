<?php
declare(strict_types=1);

// Bootstraps Laravel and inspects App\Http\Livewire\GroqChat->groq without calling external APIs.
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Livewire\GroqChat;

try {
    $c = $app->make(GroqChat::class);
    // Ensure Livewire lifecycle mount() runs (sets up the Groq client)
    if (method_exists($c, 'mount')) {
        $c->mount();
    }
    $r = new ReflectionClass($c);
    if ($r->hasProperty('groq')) {
        $p = $r->getProperty('groq');
        $p->setAccessible(true);
        $v = $p->getValue($c);
        if ($v === null) {
            echo "groq property is null\n";
            exit(2);
        }
        echo "groq property is instance of " . get_class($v) . "\n";
        exit(0);
    }

    echo "GroqChat has no property 'groq'\n";
    exit(3);
} catch (Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e;
    exit(1);
}
