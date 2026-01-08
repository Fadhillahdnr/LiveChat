{{-- resources/views/livewire/groq-chat.blade.php --}}
<div class="max-w-2xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Chat dengan Groq (Livewire)</h2>

    @if ($error)
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            {{ $error }}
        </div>
    @endif

    <form wire:submit.prevent="submit">
        <textarea
            wire:model.defer="prompt"
            rows="4"
            class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
            placeholder="Tuliskan pertanyaan atau perintah Anda..."
            {{ $loading ? 'disabled' : '' }}
        ></textarea>

        <div class="mt-3 flex items-center">
            <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded disabled:opacity-50"
                {{ $loading ? 'disabled' : '' }}
            >
                {{ $loading ? 'Memproses...' : 'Kirim' }}
            </button>

            @if ($loading)
                <svg class="animate-spin h-5 w-5 text-blue-600 ml-3" xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
            @endif
        </div>
    </form>

    @if ($answer)
        <div class="mt-6 p-4 bg-gray-100 rounded">
            <h3 class="font-semibold mb-2">Jawaban:</h3>
            <p class="whitespace-pre-line">{{ $answer }}</p>
        </div>
    @endif
</div>  