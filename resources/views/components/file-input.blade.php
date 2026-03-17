@props([
    'name',
    'label',
    'accept',
    'document' => null,
])

<div x-data="{ isDropping: false, file: null, fileName: '' }" class="w-full flex flex-col h-full">

    {{-- The label's height can now vary without affecting alignment --}}
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $label }} <span class="text-red-500">*</span> </label>

    {{-- The Dropzone Container --}}
    <div
        class="relative flex flex-col items-center justify-center w-full p-4 border-2 border-gray-300 border-dashed rounded-lg transition-colors duration-200 ease-in-out flex-grow"
        :class="{'border-indigo-500 bg-indigo-50': isDropping, 'bg-gray-50': !isDropping}"
        @dragover.prevent="isDropping = true"
        @dragleave.prevent="isDropping = false"
        @drop.prevent="isDropping = false; file = $event.dataTransfer.files[0]; fileName = file.name; $refs.input.files = $event.dataTransfer.files;"
    >
        {{-- This invisible input covers the whole area --}}
        <input
            type="file"
            id="{{ $name }}"
            name="{{ $name }}"
            accept="{{ $accept }}"
            x-ref="input"
            @change="file = $refs.input.files[0]; fileName = file ? file.name : ''"
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-0"
        >

        {{-- State when a file is ALREADY UPLOADED --}}
        @if ($document)
            <div class="text-center w-full flex flex-col justify-center items-center" x-show="!file">
                {{-- Icon --}}
                <svg class="mx-auto h-10 w-10 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                {{-- Filename (with truncation) --}}
                <div class="w-full px-2 min-w-0">
                    <p class="mt-2 text-sm font-semibold text-gray-700 truncate" title="{{ $document->original_filename }}">
                        {{ $document->original_filename }}
                    </p>
                </div>
                <p class="text-xs text-gray-500">Uploaded: {{ $document->updated_at->format('d M Y') }}</p>

                <div class="relative z-10 mt-3 flex justify-center items-center space-x-3">
                    {{-- View Button --}}
                    <a href="{{ Storage::url($document->stored_path) }}" target="_blank" class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded hover:bg-blue-200 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        ดู
                    </a>

                    {{-- Delete Button (inside a form) --}}
                    <button
                        type="button"
                        onclick="handleDocumentDelete(this, '{{ route('profile.document.destroy', $document) }}')"
                        class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded hover:bg-red-200 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        ลบ
                    </button>
                </div>
                <p class="mt-2 text-xs text-gray-400">ลากไฟล์ใหม่หรือกดเพื่ออัปโหลด</p>
            </div>
        @endif

        {{-- State when a NEW file is selected/dropped --}}
        <div class="text-center w-full flex-col justify-center items-center" :class="file ? 'flex' : 'hidden'">
            <svg class="mx-auto h-12 w-12 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
            <div class="w-full px-2 min-w-0">
                <p class="mt-2 text-sm font-semibold text-gray-700 truncate" x-text="fileName" :title="fileName"></p>
            </div>
            <p class="text-xs text-gray-500">พร้อมที่จะอัปโหลด</p>
        </div>

        {{-- Default state (no file uploaded and no new file selected) --}}
        <div class="text-center" x-show="!file && !{{ $document ? 'true' : 'false' }}">
            <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l-3 3m3-3l3-3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" /></svg>
            <p class="mt-2 text-sm text-gray-600"><span class="font-semibold text-indigo-600">กดเพื่ออัปโหลด</span>
                <p class="text-sm text-gray-600">หรือ ลากและวาง
            </p>
            <p class="mt-2 text-xs text-gray-500">{{ strtoupper(str_replace('.', '', $accept)) }} สูงสุด 5 MB</p>
        </div>
    </div>
</div>
