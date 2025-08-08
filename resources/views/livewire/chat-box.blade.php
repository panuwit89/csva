<div class="flex flex-col h-full" wire:poll.2s>
    <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages">
        @foreach($messages as $msg)
            <div class="flex {{ $msg->role === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w p-4 rounded-lg {{ $msg->role === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800' }}">
                    <div class="prose prose-sm max-w-none {{ $msg->role === 'user' ? 'prose-invert' : '' }}">
                        {!! Str::markdownWithTables($msg->content, [
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false,
                        ]) !!}
                    </div>

                    @if($msg->hasAttachments())
                        <div class="mt-2 pt-2 border-t {{ $msg->role === 'user' ? 'border-blue-400' : 'border-gray-300' }}">
                            <p class="text-sm {{ $msg->role === 'user' ? 'text-blue-100' : 'text-gray-600' }}">
                                รายการไฟล์แนบ:
                            </p>
                            <div class="flex flex-wrap gap-2 mt-1">
                                @foreach($msg->attachments as $attachment)
                                    <a href="{{ $attachment->getUrl() }}"
                                       target="_blank"
                                       class="flex items-center gap-1 px-2 py-1 rounded-md {{ $msg->role === 'user' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 hover:bg-gray-400' }} text-sm">
                                        <span>
                                            @if($attachment->isImage())
                                                📷
                                            @elseif($attachment->isPdf())
                                                📄
                                            @elseif($attachment->isText())
                                                📝
                                            @else
                                                📎
                                            @endif
                                        </span>
                                        <span class="truncate max-w-32">{{ $attachment->original_name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        @if($isWaitingForResponse)
            <div class="flex justify-start">
                <div class="max-w-3/4 p-3 rounded-lg bg-gray-200 text-gray-800">
                    <div class="flex items-center space-x-2">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-600"></div>
                        <span>กำลังประมวลผล...</span>
                    </div>
                </div>
            </div>
        @endif

        @if($showSuggestedPrompts)
            <div class="flex justify-center mt-8">
                <div class="max-w-4xl w-full">
                    <div class="text-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">สามารถช่วยคุณได้ในเรื่องใดบ้าง?</h3>
                        <p class="text-sm text-gray-500">เลือกหัวข้อที่ต้องการถามเพื่อดูคำถามที่เกี่ยวข้อง</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($this->suggestedPrompts as $category)
                            <div class="relative group">
                                <button class="w-full flex items-start gap-3 p-4 text-left bg-white border border-gray-200 rounded-lg hover:border-blue-300 hover:shadow-md transition-all duration-200 {{ $loading ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ $loading ? 'disabled' : '' }}>
                                    <div class="flex-shrink-0 w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center text-lg group-hover:bg-blue-50 transition-colors">
                                        {{ $category['icon'] }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium text-gray-900 text-sm mb-1">{{ $category['title'] }}</h4>
                                        <p class="text-xs text-gray-600">{{ count($category['prompts']) }} คำถาม</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </button>

                                <!-- Dropdown Menu -->
                                <div class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                    <div class="py-2">
                                        @foreach($category['prompts'] as $prompt)
                                            <button wire:click="sendMessage('{{ addslashes($prompt) }}')"
                                                    class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ $loading ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                {{ $loading ? 'disabled' : '' }}>
                                                {{ $prompt }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="border-t p-4">
        <form wire:submit.prevent="sendMessage" class="space-y-3" id="chat-form">
            <div class="flex gap-2">
                <button type="button"
                        wire:click="toggleSuggestedPrompts"
                        class="bg-green-200 text-green-700 px-4 py-2 rounded-lg hover:bg-green-300"
                    {{ $loading ? 'disabled' : '' }}>
                    ตัวอย่างคำถาม
                </button>
                <input type="text" wire:model.live="message"
                       class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="พิมพ์ข้อความของคุณ"
                       id="sendMessageComplete"
                    {{ $loading ? 'disabled' : '' }}>
                <button type="button"
                        wire:click="toggleFileUpload"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 relative"
                    {{ $loading ? 'disabled' : '' }}>
                    {{ $showFileUpload ? 'ซ่อนไฟล์แนบ' : 'เพิ่มไฟล์แนบ' }}
                    @if(count($files) > 0)
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                            {{ count($files) }}
                        </span>
                    @endif
                </button>
                <button type="submit"
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    {{ $loading ? 'disabled' : '' }}>
                    ส่งข้อความ
                </button>
            </div>

            @if($showFileUpload)
                <div class="p-3 border rounded-lg border-dashed border-gray-300 bg-gray-50">
                    <div class="mb-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            เลือกไฟล์ (ได้ไม่เกิน 3 ไฟล์)
                        </label>
                        <input type="file"
                               wire:model="newFiles"
                               multiple
                               class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100"
                            {{ count($files) >= 3 ? 'disabled' : '' }}
                        />
                        <div wire:loading wire:target="newFiles" class="text-sm text-blue-600 mt-1">
                            <div class="flex items-center">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>
                                กำลังอัปโหลด...
                            </div>
                        </div>
                        @error('files') <span class="text-sm text-red-500 block mt-1">{{ $message }}</span> @enderror
                        @error('files.*') <span class="text-sm text-red-500 block mt-1">{{ $message }}</span> @enderror
                        @error('newFiles.*') <span class="text-sm text-red-500 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    @if(count($files) > 0)
                        <div class="mt-3">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm text-gray-600">ไฟล์ที่เลือก ({{ count($files) }}/3):</p>
                                <button type="button"
                                        wire:click="clearAllFiles"
                                        class="text-xs text-red-600 hover:text-red-800 px-2 py-1 bg-red-50 rounded"
                                    {{ $loading ? 'disabled' : '' }}>
                                    ลบทั้งหมด
                                </button>
                            </div>
                            <div class="grid grid-cols-1 gap-2">
                                @foreach($files as $index => $file)
                                    <div class="flex items-center justify-between bg-white px-3 py-2 rounded-md border border-gray-300">
                                        <div class="flex items-center space-x-2 flex-1 min-w-0">
                                            <span class="text-gray-500">
                                                @php
                                                    $extension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
                                                @endphp
                                                @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                                                    📷
                                                @elseif($extension === 'pdf')
                                                    📄
                                                @elseif(in_array($extension, ['txt', 'doc', 'docx']))
                                                    📝
                                                @else
                                                    📎
                                                @endif
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ $file->getClientOriginalName() }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ number_format($file->getSize() / 1024, 1) }} KB
                                                </p>
                                            </div>
                                        </div>
                                        <button type="button"
                                                wire:click="removeFile({{ $index }})"
                                                class="ml-2 text-red-500 hover:text-red-700 p-1"
                                            {{ $loading ? 'disabled' : '' }}>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-3 text-xs text-gray-500">
                        <p>รองรับไฟล์: PDF</p>
                        <p>ขนาดไฟล์ไม่เกิน 10MB ต่อไฟล์</p>
                    </div>
                </div>
            @endif
        </form>
    </div>

    <style>
        .prose table {
            width: 100%;
            margin: 1rem 0;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .prose th,
        .prose td {
            border: none;
            border-right: 1px solid #d1d5db;
            border-bottom: 1px solid #d1d5db;
            padding: 0.5rem 1rem;
            text-align: left;
            background-color: #ffffff;
        }

        .prose th:last-child,
        .prose td:last-child {
            border-right: none;
        }

        .prose tr:last-child td {
            border-bottom: none;
        }

        .prose th {
            background-color: #f3f4f6;
            font-weight: 600;
        }

        .prose p {
            line-height: 1.6;
        }

        .prose:not(.prose-invert) p {
            margin-bottom: 0.2rem;
        }

        .prose ul {
            margin: 1rem 0;
            padding-left: 1.5rem;
            list-style-type: disc;
        }

        .prose ol {
            margin: 1rem 0;
            padding-left: 1.5rem;
            list-style-type: decimal;
        }

        .prose li {
            margin: 0.25rem 0;
        }

        .prose code {
            background-color: #f3f4f6;
            padding: 0.125rem 0.25rem;
            border-radius: 0.25rem;
            font-size: 0.875em;
        }

        .prose pre {
            background-color: #1f2937;
            color: #f9fafb;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin: 1rem 0;
        }

        .prose pre code {
            background-color: transparent;
            padding: 0;
            color: inherit;
        }
    </style>
</div>

<script>
    // Auto-scroll to bottom when new messages arrive
    document.addEventListener('livewire:initialized', () => {
        const container = document.getElementById('chat-messages');
        const input = document.getElementById('sendMessageComplete');

        const scrollToBottom = () => {
            container.scrollTop = container.scrollHeight;
        };

        scrollToBottom();

        // Handle form submission to clear input immediately
        const form = document.getElementById('chat-form');
        form.addEventListener('submit', function(e) {
            // Clear input immediately when form is submitted
            setTimeout(() => {
                input.value = '';
                // Also clear the Livewire model
            @this.set('message', '');
            }, 50);
        });

        // Handle suggested prompt clicks
        document.addEventListener('click', function(e) {
            if (e.target.closest('[wire\\:click*="sendMessage"]')) {
                setTimeout(() => {
                    input.value = '';
                @this.set('message', '');
                }, 50);
            }
        });

        // Auto-scroll when messages update
        Livewire.hook('message.processed', (message, component) => {
            setTimeout(scrollToBottom, 100);
        });

        // Clear input after successful message send
        Livewire.on('messageSent', () => {
            input.value = '';
        @this.set('message', '');
            setTimeout(scrollToBottom, 100);
        });
    });
</script>
