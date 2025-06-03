@props([
    'user' => null,
    'size' => 'w-11 h-11',
    'textSize' => 'text-sm'
])

@php
    $user = $user ?? Illuminate\Support\Facades\Auth::user();
    $hasAvatar = !empty($user->avatar);
    $initial = strtoupper(substr($user->name, 0, 1));
    $colors = [
        'bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500',
        'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-teal-500'
    ];
    $colorIndex = ord($initial) % count($colors);
    $bgColor = $colors[$colorIndex];
@endphp

<div class="relative {{ $size }} rounded-full border-2 border-gray-300 overflow-hidden">
    @if($hasAvatar)
        <img class="w-full h-full object-cover"
             src="{{ $user->avatar }}"
             alt="{{ $user->name }}'s avatar"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
    @endif

    <!-- Fallback avatar -->
    <div class="w-full h-full {{ $bgColor }} flex items-center justify-center text-white font-semibold {{ $textSize }}"
         @if($hasAvatar) style="display: none;" @endif>
        {{ $initial }}
    </div>
</div>
