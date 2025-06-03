<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" autofocus autocomplete="name" disabled/>
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" autocomplete="username" disabled/>
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <!-- Interested Tags Section -->
        <div id="interested-tags-section">
            <x-input-label :value="__('Interested Fields')" />
            <p class="mt-1 text-sm text-gray-500">{{ __('Press Enter to separate each tag.') }}</p>

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
                    placeholder="{{ __('Add Tags') }}"
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
                    {{ __('Delete all tags') }}
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

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600"
                >{{ __('Saved successfully!') }}</p>
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
            tagInput.placeholder = 'Maximum tags reached';
        } else {
            tagInput.disabled = false;
            tagInput.placeholder = 'Add Tags';
        }
    }

    function addTag(tagName) {
        tagName = tagName.trim();

        if (!tagName) return false;

        // Check if we've reached the maximum
        if (selectedTagsContainer.children.length >= MAX_TAGS) {
            alert('Maximum 10 tags allowed');
            return false;
        }

        // Check if tag already exists
        const existingTags = Array.from(selectedTagsContainer.querySelectorAll('input[name="tag_names[]"]'))
            .map(input => input.value.toLowerCase());

        if (existingTags.includes(tagName.toLowerCase())) {
            alert('Tag already selected');
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
        if (confirm('Are you sure you want to delete all tags?')) {
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
</script>
