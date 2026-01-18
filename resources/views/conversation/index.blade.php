<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('รายการการสนทนา') }}
            </h2>
            <div class="flex items-center space-x-4">
                {{-- ใช้ onclick เพื่อเรียกฟังก์ชัน Global ที่เราสร้างใน app.blade.php --}}
                <a href="#" onclick="event.preventDefault(); openViewModal();" class="text-sm font-medium text-blue-600 hover:underline cursor-pointer">
                    {{ __('ดูทั้งหมด') }} ({{ $conversations->count() }})
                </a>
                {{-- เพิ่มปุ่ม "ลบการสนทนา" ใหม่ --}}
                <a href="#" onclick="event.preventDefault(); openDeleteModal();" class="text-sm font-medium text-red-600 hover:underline cursor-pointer">
                    {{ __('ลบการสนทนา') }}
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

    <div x-data="{ showModal: false, isDeleteMode: false }"
         @open-the-modal.window="showModal = true; isDeleteMode = false;"
         @open-the-delete-modal.window="showModal = true; isDeleteMode = true;">
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

        <div x-show="showModal"
             x-transition
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75"
             style="display: none;">
            <div @click.away="showModal = false; isDeleteMode = false;" class="bg-white rounded-lg shadow-xl max-w-4xl w-full m-4">
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="text-lg font-semibold" x-text="isDeleteMode ? 'เลือกลบการสนทนา' : 'รายการสนทนาทั้งหมด'"></h3>
                    <button @click="showModal = false; isDeleteMode = false;" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div x-data="{
                        selectedConversations: [],
                        conversationIds: {{ $conversations->pluck('id')->toJson() }},
                        get allSelected() {
                            return this.conversationIds.length > 0 && this.selectedConversations.length === this.conversationIds.length;
                        },
                        toggleSelectAll() {
                            if (this.allSelected) {
                                this.selectedConversations = [];
                            } else {
                                this.selectedConversations = Array.from(this.conversationIds);
                            }
                        }
                    }" x-init="$watch('showModal', value => { if (!value) { selectedConversations = [] } })"
                >
                    <form action="{{ route('conversation.multipleDelete') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                            @if($conversations->isEmpty())
                                <p class="text-gray-500">ไม่มีรายการสนทนา</p>
                            @else
                                @foreach($conversations as $conversation)
                                    <div class="flex items-center p-4 border rounded-lg transition">
                                        <input type="checkbox"
                                               name="conversations_to_delete[]"
                                               value="{{ $conversation->id }}"
                                               x-show="isDeleteMode"
                                               x-model="selectedConversations"
                                               class="mt-0.5 mr-4 rounded text-red-600 focus:ring-red-500">
                                        <a href="{{ route('conversation.show', $conversation) }}"
                                           x-bind:class="{ 'pointer-events-none': isDeleteMode }"
                                           class="flex-1 block hover:bg-gray-50 rounded-lg"
                                        >
                                            <div class="flex justify-between items-center">
                                                <h3 class="font-medium">{{ $conversation->title }}</h3>
                                                <span class="text-sm text-gray-500">{{ $conversation->updated_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-gray-600 mt-2 truncate">
                                                @if($conversation->messages->isNotEmpty())
                                                    {{ Str::limit($conversation->messages->last()->content, 100) }}
                                                @else
                                                    No messages yet
                                                @endif
                                            </p>
                                        </a>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div x-show="isDeleteMode" class="p-4 border-t flex justify-between items-center">
                            <x-button type="button"
                                      @click="toggleSelectAll()"
                                      x-text="allSelected ? 'ยกเลิกการเลือกทั้งหมด' : 'เลือกทั้งหมด'"
                                      class="text-sm font-medium">
                            </x-button>

                            <div class="text-sm text-gray-600">
                                <span x-text="selectedConversations.length"></span> / {{ $conversations->count() }} รายการ
                            </div>

                            <x-button type="submit"
                                      class="bg-red-600 hover:bg-red-700 disabled:opacity-50"
                                      x-bind:disabled="selectedConversations.length === 0">
                                {{ __('ยืนยันการลบ') }} (<span x-text="selectedConversations.length"></span>)
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
