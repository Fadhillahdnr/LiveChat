<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Services\GroqClient;

#[Layout('layouts.app')]
class ChatRoom extends Component
{
    public User $user;
    public $messages = [];
    public string $message = '';

    public function mount(User $user)
    {
        $this->user = $user;
        $this->loadMessages();
    }

    /**
     * Ambil semua pesan antara user login & lawan chat
     */
    public function loadMessages()
    {
        $this->messages = Message::where(function ($q) {
            $q->where('from_id', Auth::id())
                ->where('to_id', $this->user->id);
        })->orWhere(function ($q) {
            $q->where('from_id', $this->user->id)
                ->where('to_id', Auth::id());
        })
        ->orderBy('created_at')
        ->get();
    }

    /**
     * Kirim pesan baru
     */
    public function sendMessage()
    {
        if (trim($this->message) === '') return;

        // Simpan pesan user
        Message::create([
            'from_id' => Auth::id(),
            'to_id'   => $this->user->id,
            'message' => $this->message,
        ]);

        $userMsg = $this->message;
        $this->message = '';

        $this->loadMessages();
        $this->dispatch('scroll-chat');

        /**
         * Jika lawan chat bukan BOT → selesai
         * Juga anggap user bernama 'admin bot' (case-insensitive) sebagai bot.
         */
        $isBot = $this->user->is_bot || strtolower($this->user->name) === 'admin bot';
        if (!$isBot) {
            return;
        }

        /**
         * BOT GROQ Membalas
         */
        try {
            $reply = $this->callChatGPT($userMsg);

            Message::create([
                'from_id' => $this->user->id,   // bot sebagai pengirim
                'to_id'   => Auth::id(),
                'message' => $reply,
            ]);

            $this->loadMessages();
            $this->dispatch('scroll-chat');

        } catch (\Exception $e) {
            Message::create([
                'from_id' => $this->user->id,
                'to_id'   => Auth::id(),
                'message' => "⚠️ Bot sedang bermasalah, coba lagi nanti.",
            ]);

            $this->loadMessages();
            $this->dispatch('scroll-chat');
        }
    }

    /**
     * Call GROQ AI
     */
    private function callChatGPT($text)
    {
        $client = app(GroqClient::class);

        $messages = [
            [
                'role' => 'system',
                'content' => 'Kamu adalah Admin Bot yang ramah, sopan, dan selalu membantu user dengan jelas.'
            ],
            [
                'role' => 'user',
                'content' => $text,
            ],
        ];

        try {
            $resp = $client->chat($messages, [
                'temperature' => 0.6,
                'max_tokens' => 500,
            ]);

            return $resp['choices'][0]['message']['content'] ?? "⚠️ Bot error: respons kosong.";
        } catch (\Throwable $e) {
            logger('GROQ ERROR: ' . $e->getMessage());
            return "⚠️ Bot error: API gagal.";
        }
    }

    public function render()
    {
        return view('livewire.chat-room');
    }
}
