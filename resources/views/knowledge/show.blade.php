<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $knowledge->title }}
            </h2>
            <div class="flex space-x-2">
                <x-button :href="route('knowledge.download', $knowledge)" color="green">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    ดาวน์โหลด
                </x-button>
                <x-button :href="route('knowledge.edit', $knowledge)" color="indigo">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    แก้ไข
                </x-button>
                <x-button :href="route('knowledge.index')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    ย้อนกลับ
                </x-button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Document Information -->
                        <div class="lg:col-span-1">
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">ข้อมูลเอกสาร</h3>

                                <div class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">ชื่อเรื่อง</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $knowledge->title }}</dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">ชื่อไฟล์ดั้งเดิม</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $knowledge->original_filename }}</dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">ขนาดไฟล์</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $knowledge->file_size_formatted }}</dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">อัปโหลดโดย</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $knowledge->uploader->name }}</dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">อัปโหลดวันที่</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $knowledge->created_at->format('F j, Y \a\t g:i A') }}</dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">สถานะ</dt>
                                        <dd class="mt-1">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $knowledge->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $knowledge->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </dd>
                                    </div>

                                    @if($knowledge->description)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">คำอธิบาย</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $knowledge->description }}</dd>
                                        </div>
                                    @endif
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-6 space-y-2">
                                    <form action="{{ route('knowledge.toggle', $knowledge) }}" method="POST" class="w-full">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="w-full flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white {{ $knowledge->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            @if($knowledge->is_active)
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                                                </svg>
                                                ปิดการใช้งาน
                                            @else
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                เปิดการใช้งาน
                                            @endif
                                        </button>
                                    </form>

                                    <form action="{{ route('knowledge.destroy', $knowledge) }}" method="POST" class="w-full" onsubmit="return confirm('Are you sure you want to delete this document? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            ลบเอกสาร
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- PDF Preview -->
                        <div class="lg:col-span-2">
                            <div class="bg-gray-50 rounded-lg p-6">
                                {{-- Depend on mime_type  --}}
                                <h3 class="text-lg font-medium text-gray-900 mb-4">
                                    @if($knowledge->mime_type === 'application/pdf')
                                        ตัวอย่างเอกสาร
                                    @elseif($knowledge->mime_type === 'application/json')
                                        เนื้อหาเอกสารไฟล์ JSON
                                    @elseif($knowledge->mime_type === 'text/plain')
                                        เนื้อหาเอกสาร
                                    @else
                                        ข้อมูลไฟล์
                                    @endif
                                </h3>

                                {{-- File preview depend on mime_type --}}
                                @if($knowledge->mime_type === 'application/pdf')
                                    {{-- For PDF --}}
                                    <div class="border rounded-lg bg-white" style="height: 600px;">
                                        <iframe
                                            src="{{ $knowledge->file_url }}#toolbar=1"
                                            class="w-full h-full rounded-lg"
                                            title="PDF Preview">
                                        </iframe>
                                    </div>

                                @elseif(in_array($knowledge->mime_type, ['application/json', 'text/plain']) && $content !== null)
                                    {{-- For TXT and JSON --}}
                                    <div class="border rounded-lg bg-white p-4" style="height: 600px; overflow-y: auto;">
                                        <pre class="text-sm whitespace-pre-wrap">{{ $content }}</pre>
                                    </div>

                                @else
                                    {{-- For other type, cannot display --}}
                                    <div class="border-2 border-dashed rounded-lg bg-white flex items-center justify-center" style="height: 600px;">
                                        <div class="text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="mt-2 text-gray-500">การดูตัวอย่างไฟล์ ไม่รองรับกับนามสกุลไฟล์นี้</p>
                                        </div>
                                    </div>
                                @endif

                                <div class="mt-4 text-center">
                                    <p class="text-sm text-gray-500">
                                        มีปัญหาเกี่ยวกับการดูเอกสาร
                                        <a href="{{ route('knowledge.download', $knowledge) }}" class="text-blue-600 hover:text-blue-800 underline">
                                            ดาวน์โหลด
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
