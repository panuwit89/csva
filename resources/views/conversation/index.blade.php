<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('รายการการสนทนา') }}
            </h2>
            <div class="flex items-center space-x-4">
                {{-- ใช้ onclick เพื่อเรียกฟังก์ชัน Global ที่เราสร้างใน app.blade.php --}}
                <a href="#" onclick="event.preventDefault(); openMyModal();" class="text-sm font-medium text-blue-600 hover:underline cursor-pointer">
                    {{ __('ดูทั้งหมด') }} ({{ $conversations->count() }})
                </a>
                <form action="{{ route('conversation.store') }}" method="POST">
                    @csrf
                    <x-button>
                        {{ __('สร้างการสนทนาใหม่') }}
                    </x-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div x-data="{ showAllConversations: false }" @open-the-modal.window="showAllConversations = true">
        <div class="py-8">
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
                                {{-- แสดงแค่ 5 รายการล่าสุด --}}
                                @foreach($conversations->take(5) as $conversation)
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

        {{-- โค้ด Modal --}}
        <div x-show="showAllConversations"
             x-transition
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75"
             style="display: none;">
            <div @click.away="showAllConversations = false" class="bg-white rounded-lg shadow-xl max-w-4xl w-full m-4">
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="text-lg font-semibold">รายการสนทนาทั้งหมด</h3>
                    <button @click="showAllConversations = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    @if($conversations->isEmpty())
                        <p class="text-gray-500">ไม่มีรายการสนทนา</p>
                    @else
                        {{-- แสดงทุกรายการใน Modal --}}
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
