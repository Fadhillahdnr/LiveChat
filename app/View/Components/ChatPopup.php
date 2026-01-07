<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\User;

class ChatPopup extends Component
{
    public $users;

    public function __construct()
    {
        $this->users = User::where('id', '!=', auth()->id())->get();
    }

    public function render()
    {
        return view('components.chat-popup');
    }
}
