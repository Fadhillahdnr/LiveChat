<?php

namespace App\Livewire;

/**
 * Shim class to avoid legacy DI behavior; delegate to canonical Livewire component
 * under App\Http\Livewire which is PSR-4 correct and initializes GroqClient
 * reliably.
 */
class GroqChat extends \App\Http\Livewire\GroqChat {}