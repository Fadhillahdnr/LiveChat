<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\GroqClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class GroqChat extends Component
{
    // Properti yang di‑bind ke UI
    public $prompt = '';
    public $answer = '';
    public $loading = false;
    public $error   = '';

    protected $rules = [
        'prompt' => 'required|string|min:1|max:2000',
    ];

    // Groq client instance (resolve in mount to support Livewire)
    protected $groq;

    public function mount()
    {
        $this->groq = app(GroqClient::class);
    }

    public function submit()
    {
        $this->reset(['answer', 'error']);
        $this->validate();

        $this->loading = true;

        try {
            if (!$this->groq) {
                $this->groq = app(GroqClient::class);
            }
            // Siapkan pesan sesuai format OpenAI
            $messages = [
                ['role' => 'system', 'content' => 'Kamu adalah asisten AI yang ramah dan membantu.'],
                ['role' => 'user',   'content' => $this->prompt],
            ];

            // Opsional: Anda dapat menambah parameter lain di array kedua
            $response = $this->groq->chat($messages, [
                'temperature' => 0.6,
                'max_tokens'  => 500,
            ]);

            // Ambil jawaban
            $this->answer = $response['choices'][0]['message']['content'] ?? 'Tidak ada respons';
        } catch (RequestException $e) {
            // Menangkap error dari Http client (status >=400)
            $status = $e->response->status();
            $body   = $e->response->body();

            Log::error('Groq API error', [
                'status' => $status,
                'body'   => $body,
                'prompt' => $this->prompt,
            ]);

            $this->error = "Terjadi kesalahan (HTTP {$status}). Silakan coba lagi.";
        } catch (\Throwable $e) {
            // Kesalahan tak terduga lainnya
            Log::error('Groq unexpected error', ['msg' => $e->getMessage()]);
            $this->error = 'Kesalahan tak terduga. Lihat log server untuk detail.';
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.groq-chat');
    }
}
