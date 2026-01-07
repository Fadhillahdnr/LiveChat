<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

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
        if (trim($this->message) === '') {
            return;
        }

        Message::create([
            'from_id' => Auth::id(),
            'to_id'   => $this->user->id,
            'message' => $this->message,
        ]);

        $this->message = '';

        // refresh pesan setelah kirim
        $this->loadMessages();
        $this->dispatch('chat-sent');
        $this->dispatch('scroll-chat');
    }

    public function render()
    {
        return view('livewire.chat-room');
    }
}
