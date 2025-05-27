<div class="flex flex-col h-full">
    <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages">
        @foreach($messages as $msg)
            <div class="flex {{ $msg->type === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w p-4 rounded-lg {{ $msg->type === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800' }}">
                    <div class="whitespace-normal">
                        {!! nl2br(e($msg->content)) !!}
                    </div>

                    @if($msg->hasAttachments())
                        <div class="mt-2 pt-2 border-t {{ $msg->type === 'user' ? 'border-blue-400' : 'border-gray-300' }}">
                            <p class="text-sm {{ $msg->type === 'user' ? 'text-blue-100' : 'text-gray-600' }}">
                                Attachments:
                            </p>
                            <div class="flex flex-wrap gap-2 mt-1">
                                @foreach($msg->attachments as $attachment)
                                    <a href="{{ $attachment->getUrl() }}"
                                       target="_blank"
                                       class="flex items-center gap-1 px-2 py-1 rounded-md {{ $msg->type === 'user' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 hover:bg-gray-400' }} text-sm">
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

{{--        @if($loading)--}}
{{--            <div class="flex justify-start">--}}
{{--                <div class="max-w-3/4 p-3 rounded-lg bg-gray-200 text-gray-800">--}}
{{--                    <span class="inline-block animate-pulse">Thinking...</span>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        @endif--}}
        <div class="flex justify-start" wire:loading wire:target="sendMessage">
            <div class="max-w-3/4 p-3 rounded-lg bg-gray-200 text-gray-800">
                <div class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-600"></div>
                    <span>Thinking...</span>
                </div>
            </div>
        </div>
    </div>

    <div class="border-t p-4">
        <form wire:submit.prevent="sendMessage" class="space-y-3">
            <div class="flex gap-2">
                <input type="text" wire:model.live="message"
                       class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Type your message..."
                       id="sendMessageComplete"
                    {{ $loading ? 'disabled' : '' }}>
                <button type="button"
                        wire:click="toggleFileUpload"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300"
                    {{ $loading ? 'disabled' : '' }}>
                    {{ $showFileUpload ? 'Hide Files' : 'Add Files' }}
                </button>
                <button type="submit"
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    {{ $loading ? 'disabled' : '' }}>
                    Send
                </button>
            </div>

            @if($showFileUpload)
                <div class="p-3 border rounded-lg border-dashed border-gray-300 bg-gray-50">
                    <div class="mb-2">
                        <input type="file" wire:model="files" multiple class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100"
                        />
                        <div wire:loading wire:target="files">Uploading...</div>
                        @error('files.*') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    @if(count($files) > 0)
                        <div class="mt-2">
                            <p class="text-sm text-gray-600 mb-1">Selected files:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($files as $index => $file)
                                    <div class="flex items-center bg-white px-2 py-1 rounded-md border border-gray-300 text-sm">
                                        <span class="truncate max-w-32">{{ $file->getClientOriginalName() }}</span>
                                        <button type="button" wire:click="removeFile({{ $index }})" class="ml-1 text-red-500">×</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-2 text-xs text-gray-500">
                        Supported formats: PDF, images (PNG, JPG, JPEG), TXT files
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>

<script>
    // Auto-scroll to bottom when new messages arrive
    document.addEventListener('livewire:initialized', () => {
        const container = document.getElementById('chat-messages');
        const scrollToBottom = () => {
            container.scrollTop = container.scrollHeight;
        };

        scrollToBottom();

        Livewire.hook('message.processed', (message, component) => {
            scrollToBottom();
        });
    });
</script>
