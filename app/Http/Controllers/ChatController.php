<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function fetch($userId)
    {
        return Message::where(function ($q) use ($userId) {
            $q->where('from_id', auth()->id())
              ->where('to_id', $userId);
        })->orWhere(function ($q) use ($userId) {
            $q->where('from_id', $userId)
              ->where('to_id', auth()->id());
        })
        ->orderBy('created_at')
        ->get();
    }

    public function send(Request $request)
    {
        return Message::create([
            'from_id' => auth()->id(),
            'to_id'   => $request->to_id,
            'message' => $request->message,
        ]);
    }
}
