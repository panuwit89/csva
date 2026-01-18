<section>
    <header>
        <h2 class="text-xl font-medium text-gray-900">
            {{ __('ข้อมูลโปรไฟล์') }}
        </h2>

        <p class="mt-1 text-m text-gray-600">
            {{ __("อัปเดตข้อมูลโปรไฟล์ของคุณ") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('ชื่อ-นามสกุล')"/>
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" autofocus autocomplete="name" disabled/>
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('อีเมล')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" autocomplete="username" disabled/>
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <!-- Interested Tags Section -->
        <div id="interested-tags-section">
            <x-input-label :value="__('หัวข้อที่สนใจ')" />
            <p class="mt-1 text-sm text-gray-500">{{ __('กด Enter เพื่อแยกแต่ละหัวข้อ') }}</p>

            <!-- Tag Input Container -->
            <div class="mt-3 border rounded-lg p-3 bg-gray-50 min-h-[100px] focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500">
                <!-- Selected Tags Display -->
                <div id="selected-tags" class="flex flex-wrap gap-2 mb-2">
                    @foreach($user->interestedTags as $tag)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800 border border-green-200">
                            {{ $tag->name }}
                            <button type="button" class="ml-2 text-green-600 hover:text-green-800 focus:outline-none" onclick="removeTag(this, {{ $tag->id }})">
                                ×
                            </button>
                            <input type="hidden" name="tag_names[]" value="{{ $tag->name }}">
                        </span>
                    @endforeach
                </div>

                <!-- Tag Input -->
                <input
                    type="text"
                    id="tag-input"
                    placeholder="{{ __('เพิ่มหัวข้อ') }}"
                    class="w-full border-0 bg-transparent focus:ring-0 focus:outline-none text-sm placeholder-gray-400"
                    autocomplete="off"
                >
            </div>

            <!-- Tag Counter & Delete All Tags Button -->
            <div class="mt-2 text-right">
                <button
                    type="button"
                    id="delete-all-tags"
                    onclick="deleteAllTags()"
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm text-white-800
                    hover:bg-red-500 bg-red-600 text-red-100 border border-red-200"
                    style="display: {{ $user->interestedTags->count() > 0 ? 'inline' : 'none' }};"
                >
                    {{ __('ลบหัวข้อทั้งหมด') }}
                </button>
                <span id="tag-counter" class="pl-3 text-sm text-gray-500">
                    <span id="current-count">{{ $user->interestedTags->count() }}</span>/10
                </span>
            </div>

            <!-- Existing Tags for Autocomplete -->
            <datalist id="existing-tags">
                @foreach(\App\Models\Tag::all() as $tag)
                    <option value="{{ $tag->name }}">
                @endforeach
            </datalist>

            <x-input-error class="mt-2" :messages="$errors->get('tag_names')" />
        </div>

        <div class="pt-6 border-t border-gray-200">
            <header>
                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('เอกสารของฉัน') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('อัปโหลดเอกสารของคุณ เพื่อประกอบการตรวจสอบการสำเร็จการศึกษา ระบบจะใช้เอกสารที่ถูกอัปเดตล่าสุด') }}

                </p>
            </header>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Transcript Upload --}}
                <x-file-input
                    name="documents[transcript]"
                    label="ใบรายงานคะแนนผลการเรียน (ฉบับล่าสุด)"
                    accept=".pdf"
                    :document="$user->getDocument('transcript')"
                />
                <x-input-error class="mt-2" :messages="$errors->get('documents.transcript')" />

                {{-- Activity Certificate Upload --}}
                <x-file-input
                    name="documents[activity]"
                    label="ใบรายงานการเข้าร่วมกิจกรรม (ฉบับล่าสุด)"
                    accept=".pdf"
                    :document="$user->getDocument('activity')"
                />
                <x-input-error class="mt-2" :messages="$errors->get('documents.activity')" />

                {{-- Payment Receipt Upload --}}
                <x-file-input
                    name="documents[receipt]"
                    label="ใบเสร็จค่าธรรมเนียมการศึกษา (ภาคการศึกษาล่าสุด)"
                    accept=".pdf"
                    :document="$user->getDocument('receipt')"
                />
                <x-input-error class="mt-2" :messages="$errors->get('documents.receipt')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('บันทึก') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600"
                >{{ __('บันทึกข้อมูลเรียบร้อย!') }}</p>
            @endif
        </div>
    </form>
</section>

<script>
    const MAX_TAGS = 10;
    let tagInput = document.getElementById('tag-input');
    let selectedTagsContainer = document.getElementById('selected-tags');
    let currentCountSpan = document.getElementById('current-count');
    let deleteAllButton = document.getElementById('delete-all-tags');

    function updateTagCounter() {
        const currentCount = selectedTagsContainer.children.length;
        currentCountSpan.textContent = currentCount;

        // Show/hide delete all button
        deleteAllButton.style.display = currentCount > 0 ? 'inline' : 'none';

        // Disable input if max tags reached
        if (currentCount >= MAX_TAGS) {
            tagInput.disabled = true;
            tagInput.placeholder = 'เพิ่มได้สูงสุด 10 ห้วข้อ';
        } else {
            tagInput.disabled = false;
            tagInput.placeholder = 'เพิ่มหัวข้อ';
        }
    }

    function addTag(tagName) {
        tagName = tagName.trim();

        if (!tagName) return false;

        // Check if we've reached the maximum
        if (selectedTagsContainer.children.length >= MAX_TAGS) {
            alert('เพิ่มได้สูงสุด 10 ห้วข้อ');
            return false;
        }

        // Check if tag already exists
        const existingTags = Array.from(selectedTagsContainer.querySelectorAll('input[name="tag_names[]"]'))
            .map(input => input.value.toLowerCase());

        if (existingTags.includes(tagName.toLowerCase())) {
            alert('มีหัวข้อนี้แล้ว');
            return false;
        }

        // Create tag element
        const span = document.createElement('span');
        span.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800 border border-green-200';
        span.innerHTML = `
            ${tagName}
            <button type="button" class="ml-2 text-green-600 hover:text-green-800 focus:outline-none" onclick="removeTag(this)">
                ×
            </button>
            <input type="hidden" name="tag_names[]" value="${tagName}">
        `;

        selectedTagsContainer.appendChild(span);
        updateTagCounter();
        return true;
    }

    function removeTag(button, tagId = null) {
        const tagElement = button.closest('span');
        tagElement.remove();
        updateTagCounter();
    }

    function deleteAllTags() {
        if (confirm('ต้องการลบหัวข้อทั้งหมดหรือไม่?')) {
            selectedTagsContainer.innerHTML = '';
            updateTagCounter();
        }
    }

    function processTags(input) {
        const tags = input.split(',').map(tag => tag.trim()).filter(tag => tag);
        let addedCount = 0;

        tags.forEach(tag => {
            if (addTag(tag)) {
                addedCount++;
            }
        });

        return addedCount;
    }

    // Handle input events
    tagInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const input = tagInput.value.trim();

            if (input) {
                if (e.key === ',') {
                    // For comma, process all tags up to the comma
                    const parts = input.split(',');
                    const tagsToAdd = parts.slice(0, -1); // All parts except the last one
                    const remainingText = parts[parts.length - 1].trim();

                    tagsToAdd.forEach(tag => addTag(tag));
                    tagInput.value = remainingText;
                } else {
                    // For Enter, process the entire input
                    processTags(input);
                    tagInput.value = '';
                }
            }
        } else if (e.key === 'Backspace' && tagInput.value === '') {
            // Remove last tag if backspace is pressed on empty input
            const lastTag = selectedTagsContainer.lastElementChild;
            if (lastTag) {
                lastTag.remove();
                updateTagCounter();
            }
        }
    });

    // Handle paste events
    tagInput.addEventListener('paste', function(e) {
        setTimeout(() => {
            const input = tagInput.value;
            const addedCount = processTags(input);
            if (addedCount > 0) {
                tagInput.value = '';
            }
        }, 10);
    });

    // Handle blur events (when user clicks away)
    tagInput.addEventListener('blur', function(e) {
        const input = tagInput.value.trim();
        if (input) {
            addTag(input);
            tagInput.value = '';
        }
    });

    // Initialize counter
    updateTagCounter();

    // Add autocomplete functionality
    tagInput.setAttribute('list', 'existing-tags');

    function handleDocumentDelete(buttonElement, url) {
        if (!confirm('ต้องการลบไฟล์นี้หรือไม่?')) {
            return;
        }

        buttonElement.disabled = true;
        buttonElement.textContent = 'กำลังลบ...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                response.json().then(data => {
                    alert('Deletion failed: ' + (data.message || 'Unknown error'));
                }).catch(() => {
                    alert('Deletion failed. Please check the console for details.');
                });
                buttonElement.disabled = false;
                buttonElement.innerHTML = '<svg ...>...</svg> Delete';
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert('A network error occurred. Please check your connection.');
            buttonElement.disabled = false;
            buttonElement.innerHTML = '<svg ...>...</svg> Delete';
        });
    }
</script>
