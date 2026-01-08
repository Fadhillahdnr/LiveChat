<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- CONTENT DASHBOARD -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="py-12">
                    <livewire:groq-chat />
                </div>
            </div>
        </div>
    </div>

    <!-- CHAT POPUP COMPONENT -->
    <x-chat-popup />
</x-app-layout>
