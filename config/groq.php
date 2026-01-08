<?php
// config/groq.php
return [
    'api_key'   => env('GROQ_API_KEY'),
    'base_url'  => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
    'model'     => env('GROQ_MODEL', 'llama3-70b-8192'),
    'timeout'   => env('GROQ_TIMEOUT', 30),   // detik
    // Daftar model cadangan (comma-separated) yang akan dicoba jika model utama
    // sudah tidak didukung. Contoh: GROQ_FALLBACK_MODELS="model-a,model-b"
    'fallback_models' => array_filter(array_map('trim', explode(',', env('GROQ_FALLBACK_MODELS', '')))),
    // Jika true, GroqClient tidak akan memanggil API eksternal dan akan mengembalikan
    // jawaban tiruan berguna untuk pengembangan lokal.
    'simulate' => env('GROQ_SIMULATE', false),
];