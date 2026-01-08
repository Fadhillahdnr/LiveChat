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

<!-- Groq chat popup + floating button (outside main wrapper) -->
<div id="groq-popup-backdrop" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.4);z-index:9998;">
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%, -50%);width:420px;max-width:90%;background:white;border-radius:8px;box-shadow:0 10px 30px rgba(0,0,0,0.2);overflow:hidden;z-index:9999;">
        <div style="padding:12px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;">
            <strong>Admin Bot (Groq)</strong>
            <button id="groq-close" style="background:none;border:none;cursor:pointer;font-size:20px;padding:0;">✕</button>
        </div>
        <div style="padding:12px;max-height:500px;overflow-y:auto;">
            @livewire('groq-chat')
        </div>
    </div>
</div>

<button id="groq-open" style="position:fixed;bottom:24px;right:24px;z-index:9999;background:#0ea5a4;color:white;border-radius:50%;width:56px;height:56px;display:flex;align-items:center;justify-content:center;font-size:24px;border:none;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,0.15);">🤖</button>

<script>
    (function(){
        const open = document.getElementById('groq-open');
        const close = document.getElementById('groq-close');
        const backdrop = document.getElementById('groq-popup-backdrop');

        function show() { if (backdrop) { backdrop.style.display = 'flex'; } }
        function hide() { if (backdrop) { backdrop.style.display = 'none'; } }
        
        if (open) open.addEventListener('click', show);
        if (close) close.addEventListener('click', hide);

        // close when clicking outside the popup
        if (backdrop) backdrop.addEventListener('click', function(e){
            if (e.target === backdrop) hide();
        });
    })();
</script>



