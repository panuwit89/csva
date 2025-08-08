<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $conversation->title }}
            </h2>
            <div class="flex justify-content-end space-x-4">
                <form method="POST" action="{{ route('conversation.destroy', $conversation) }}" onsubmit="return confirm('Are you sure you want to delete this conversation? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" color="red">
                        ลบการสนทนา
                    </x-button>
                </form>
                <x-button :href="route('conversation.index')">
                    กลับไปยังรายการการสนทนา
                </x-button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 h-[70vh]">
                    <livewire:chat-box :conversation="$conversation" />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
