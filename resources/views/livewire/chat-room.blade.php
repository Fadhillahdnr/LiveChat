<div class="mbpy-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="p-4">

                    {{-- Header --}}
                    <div class="flex items-center gap-3 mb-4">

                        {{-- Tombol kembali --}}
                        <a wire:navigate href="{{ route('dashboard') }}" class="btn btn-sm btn-circle btn-ghost">
                            ←
                        </a>

                        {{-- Avatar lawan chat --}}
                        <div class="avatar">
                            <div class="w-10 rounded-full">
                                <img
                                    src="{{ $user->avatar
                                        ? asset('storage/' . $user->avatar)
                                        : 'https://ui-avatars.com/api/?name=' . urlencode($user->name)
                                    }}"
                                    alt="{{ $user->name }}"
                                />
                            </div>
                        </div>

                        {{-- Nama --}}
                        <div class="flex flex-col">
                            <span class="font-bold text-lg">
                                {{ $user->name }}
                            </span>
                            <span class="text-xs text-gray-500">
                                Online
                            </span>
                        </div>

                    </div>


                    {{-- Chat box --}}
                    <div
                        wire:poll="loadMessages"
                        
                        class="h-[400px] overflow-y-auto p-4 rounded-lg bg-base-200 mb-4 space-y-2"
                        id="chat-box">
                        @foreach($messages as $msg)
                            @php
                                $isMe = $msg->from_id === auth()->id();
                                $avatar = $isMe
                                    ? (auth()->user()->avatar
                                        ? asset('storage/' . auth()->user()->avatar)
                                        : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name))
                                    : ($user->avatar
                                        ? asset('storage/' . $user->avatar)
                                        : 'https://ui-avatars.com/api/?name=' . urlencode($user->name));
                            @endphp

                            <div
                                wire:key="message-{{ $msg->id }}"
                                class="chat {{ $isMe ? 'chat-end' : 'chat-start' }}"
                            >

                                {{-- Avatar --}}
                                <div class="chat-image avatar">
                                    <div class="w-10 rounded-full">
                                        <img src="{{ $avatar }}" />
                                    </div>
                                </div>

                                {{-- Bubble --}}
                                <div class="chat-bubble {{ $isMe ? 'chat-bubble-primary' : '' }} max-w-xs">

                                    @if($msg->image)
                                        <img
                                            src="{{ asset('storage/' . $msg->image) }}"
                                            class="rounded-lg max-w-full h-auto mb-1"
                                        >
                                    @endif

                                    @if($msg->message)
                                        <p class="break-words">
                                            {{ $msg->message }}
                                        </p>
                                    @endif

                                </div>
                                {{-- Time --}}
                                <div class="chat-footer opacity-50 text-xs">
                                    {{ $msg->created_at->format('H:i') }}
                                </div>

                            </div>
                        @endforeach
                    </div>

                    {{-- Input --}}
                    <form wire:submit.prevent="sendMessage" class="flex gap-2">
                        <input
                            type="text"
                            wire:model.defer="message"
                            placeholder="Ketik pesan..."
                            class="input input-bordered w-full"
                            autocomplete="off"
                        >
                        <button type="submit" class="btn btn-primary">
                            Kirim
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Auto scroll --}}
<script>
    document.addEventListener('livewire:navigated', () => {
        const chatBox = document.getElementById('chat-box');

        function scrollBottom() {
            if (!chatBox) return;
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // saat pertama masuk halaman
        setTimeout(scrollBottom, 200);

        // saat kirim / terima pesan
        Livewire.on('scroll-chat', () => {
            setTimeout(scrollBottom, 100);
        });
    });
</script>



