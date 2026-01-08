<?php

namespace App\Livewire;

/**
 * Legacy compatibility shim — delegate to the canonical Livewire component
 * under App\Http\Livewire to avoid duplicated Groq HTTP calls.
 */
class ChatRoom extends \App\Http\Livewire\ChatRoom {}
