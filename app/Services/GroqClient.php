<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GroqClient
{
    protected $apiKey;
    protected $baseUrl;
    protected $model;
    protected $timeout;

    public function __construct()
    {
        $this->apiKey  = config('groq.api_key');
        $this->baseUrl = rtrim(config('groq.base_url'), '/');
        $this->model   = config('groq.model');
        $this->timeout = config('groq.timeout', 30);
    }

    /**
     * Mengirim prompt ke Groq (chat completion)
     *
     * @param  string|array $messages  (array of ['role'=>..., 'content'=>...])
     * @param  array $options        (opsional: temperature, max_tokens, dll)
     * @return array                 (response decoded as associative array)
     *
     * @throws RequestException
     */
    public function chat(array $messages, array $options = []): array
    {
        // Jika mode simulasi aktif (mis. pengembangan) atau API key tidak diset,
        // kembalikan respons tiruan agar fitur chat tetap berfungsi secara lokal.
        if (config('groq.simulate', env('GROQ_SIMULATE', false)) || empty($this->apiKey)) {
            $userContent = '';
            foreach ($messages as $m) {
                if (($m['role'] ?? '') === 'user') {
                    $userContent = $m['content'];
                }
            }

            $sim = [
                'id' => 'sim-' . uniqid(),
                'object' => 'chat.completion',
                'choices' => [[
                    'message' => ['role' => 'assistant', 'content' => "(Simulated) Saya menerima: " . trim($userContent)],
                ]],
            ];

            Log::info('Groq simulated response used', ['simulate' => true, 'prompt' => $userContent]);
            return $sim;
        }
        $payload = array_merge([
            'model'    => $this->model,
            'messages' => $messages,
            // nilai default, dapat di‑override lewat $options
            'temperature' => 0.7,
            'max_tokens'  => 1024,
        ], $options);

        $attempt = 0;
        $maxAttempts = 3;
        $delaySeconds = 2;

        do {
            $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->timeout($this->timeout)
                ->post($this->baseUrl . '/chat/completions', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            // Jika model sudah decommissioned, coba model fallback dari config
            $errorCode = $response->json('error.code');
            if ($response->status() === 400 && $errorCode === 'model_decommissioned') {
                $decomMsg = $response->json('error.message');
                Log::warning('Groq model decommissioned', ['msg' => $decomMsg, 'model' => $payload['model']]);

                $fallbacks = config('groq.fallback_models', []);
                foreach ($fallbacks as $fb) {
                    // ubah payload model dan coba segera
                    $payload['model'] = $fb;
                    $resp2 = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $this->apiKey,
                            'Content-Type'  => 'application/json',
                        ])
                        ->timeout($this->timeout)
                        ->post($this->baseUrl . '/chat/completions', $payload);

                    if ($resp2->successful()) {
                        // simpan model baru sebagai default untuk durasi request ini
                        $this->model = $fb;
                        return $resp2->json();
                    }
                    // jika gagal karena decommissioned lagi, lanjutkan ke fallback berikutnya
                    if ($resp2->status() === 400 && $resp2->json('error.code') === 'model_decommissioned') {
                        Log::warning('Fallback model also decommissioned', ['model' => $fb]);
                        continue;
                    }
                    // Jika gagal karena rate limit, coba sesuai mekanisme retry di bawah
                    if ($resp2->status() === 429) {
                        // biarkan mekanisme retry handle dengan menaikkan attempt
                        $response = $resp2;
                        break;
                    }
                    // untuk error lain, lempar
                    $resp2->throw();
                }
                // jika tidak ada fallback atau semua gagal, lempar exception dari response awal
                $response->throw();
            }

            // Jika limit, tunggu dan coba lagi
            if ($response->status() === 429 && $attempt < $maxAttempts) {
                $retryAfter = $response->header('Retry-After') ?: $delaySeconds;
                sleep((int) $retryAfter);
                $attempt++;
            } else {
                // Lempar exception untuk Livewire tangani
                $response->throw();
            }
        } while ($attempt < $maxAttempts);
        }
}