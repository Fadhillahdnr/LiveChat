<!-- ================= FLOATING CHAT SYSTEM ================= -->
<div
    x-data="{
        openList: false,
        openChat: false,
        selectedUser: null,

        messages: [],
        newMessage: '',

        poller: null,

        audio: new Audio('/sounds/chat.mp3'),
        soundEnabled: false,

        initialized: false,
        unreadCount: 0,
        lastMessageCount: 0,

        /* ================= INIT ================= */
        init() {
            this.poller = setInterval(() => {
                this.fetchMessages();
            }, 2000);
        },

        /* ================= ENABLE SOUND ================= */
        enableSound() {
            if (this.soundEnabled) return;

            this.audio.muted = true;
            this.audio.play().then(() => {
                this.audio.pause();
                this.audio.currentTime = 0;
                this.audio.muted = false;
                this.soundEnabled = true;
                console.log('🔔 Sound enabled');
            });
        },

        /* ================= FETCH ================= */
        fetchMessages() {
            if (!this.selectedUser) return;

            fetch(`/chat/fetch/${this.selectedUser.id}`)
                .then(res => res.json())
                .then(data => {

                    // ==== DETECT NEW MESSAGE ====
                    if (this.initialized && data.length > this.lastMessageCount) {
                        const lastMsg = data[data.length - 1];

                        if (lastMsg.from_id !== {{ auth()->id() }}) {

                            // 🔔 SOUND
                            if (this.soundEnabled) {
                                this.audio.play();
                            }

                            // 🔴 BADGE
                            if (!this.openChat) {
                                this.unreadCount++;
                            }
                        }
                    }

                    this.messages = data;
                    this.lastMessageCount = data.length;
                    this.initialized = true;

                    this.$nextTick(() => {
                        if (this.openChat && this.$refs.chatBody) {
                            this.$refs.chatBody.scrollTop =
                                this.$refs.chatBody.scrollHeight;
                        }
                    });
                });
        },

        /* ================= OPEN CHAT ================= */
        openChatWith(id, name) {
            this.selectedUser = { id, name };
            this.messages = [];
            this.lastMessageCount = 0;
            this.initialized = false;

            this.unreadCount = 0; // RESET BADGE

            this.openList = false;
            this.openChat = true;

            this.fetchMessages();
        },

        /* ================= SEND ================= */
        sendMessage() {
            if (!this.newMessage.trim() || !this.selectedUser) return;

            fetch('/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    to_id: this.selectedUser.id,
                    message: this.newMessage
                })
            }).then(() => {
                this.newMessage = '';
                this.fetchMessages();
            });
        }
    }"
    x-init="init()"
>
    <!-- ================= FLOATING BUTTON ================= -->
    <div class="fixed bottom-6 right-6 z-50">
        <button
            @click="
                openList = true;
                enableSound();
            "
            class="bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 flex items-center justify-center w-16 h-16 text-2xl relative"
        >
            💬

            <!-- 🔴 BADGE -->
            <span
                x-show="unreadCount > 0"
                x-text="unreadCount"
                class="absolute -top-1 -right-1 bg-red-600 text-white text-xs
                       w-5 h-5 flex items-center justify-center rounded-full"
            ></span>
        </button>
    </div>

    <!-- ================= USER LIST ================= -->
    <div
        x-show="openList"
        x-transition
        class="fixed inset-0 bg-black/40 flex justify-end items-end z-50"
        @click.self="openList=false"
    >
        <div class="bg-white w-80 h-[70vh] rounded-t-2xl p-4 overflow-y-auto">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-lg">Daftar User</h3>
                <button @click="openList=false">✖</button>
            </div>

            @foreach(\App\Models\User::where('id','!=',auth()->id())->get() as $user)
                <button
                    @click="openChatWith({{ $user->id }}, '{{ $user->name }}')"
                    class="w-full text-left p-3 hover:bg-gray-100 rounded mb-1"
                >
                    👤 {{ $user->name }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- ================= CHAT BOX ================= -->
    <div
        x-show="openChat"
        x-transition
        class="fixed bottom-4 right-4 w-96 h-[70vh] max-h-[600px]
               bg-[#111] text-white rounded-2xl shadow-xl z-50 flex flex-col"
    >
        <!-- HEADER -->
        <div class="h-14 flex items-center justify-between px-4 border-b border-gray-700">
            <div>
                <p class="font-bold" x-text="selectedUser?.name"></p>
                <p class="text-xs text-green-400">Online</p>
            </div>
            <button @click="openChat=false">✖</button>
        </div>

        <!-- BODY -->
        <div class="flex-1 overflow-y-auto p-4 space-y-2" x-ref="chatBody">
            <template x-for="msg in messages" :key="msg.id">
                <div
                    class="flex"
                    :class="msg.from_id == {{ auth()->id() }}
                        ? 'justify-end'
                        : 'justify-start'"
                >
                    <div
                        class="max-w-[75%] px-3 py-2 rounded-lg text-sm
                               break-words whitespace-pre-wrap"
                        :class="msg.from_id == {{ auth()->id() }}
                            ? 'bg-blue-600'
                            : 'bg-gray-700'"
                        x-text="msg.message"
                    ></div>
                </div>
            </template>
        </div>

        <!-- INPUT -->
        <div class="h-16 border-t border-gray-700 px-3 flex items-center gap-2">
            <input
                x-model="newMessage"
                @keydown.enter="sendMessage"
                type="text"
                class="flex-1 bg-gray-800 text-white rounded px-3 py-2 focus:outline-none"
                placeholder="Tulis pesan..."
            >
            <button @click="sendMessage" class="bg-blue-600 px-4 py-2 rounded">
                ➤
            </button>
        </div>
    </div>

</div>
<!-- ================= END CHAT SYSTEM ================= -->
