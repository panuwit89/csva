<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('รายการการสนทนา') }}
            </h2>
            <form action="{{ route('conversation.store') }}" method="POST">
                @csrf
                <x-button>
                    {{ __('สร้างการสนทนาใหม่') }}
                </x-button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if($conversations->isEmpty())
                        <div class="text-center py-8">
                            <p class="text-gray-500">คุณยังไม่มีประวัติการสนทนา</p>
                            <p class="mt-4">
                                โปรดกดปุ่ม "สร้างการสนทนาใหม่" เพื่อเริ่มต้นการสนทนา
                            </p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($conversations as $conversation)
                                <a href="{{ route('conversation.show', $conversation) }}"
                                   class="block p-4 border rounded-lg hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-center">
                                        <h3 class="font-medium">{{ $conversation->title }}</h3>
                                        <span class="text-sm text-gray-500">
                                            {{ $conversation->updated_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    <p class="text-gray-600 mt-2 truncate">
                                        @if($conversation->messages->isNotEmpty())
                                            {{ Str::limit($conversation->messages->last()->content, 100) }}
                                        @else
                                            No messages yet
                                        @endif
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
