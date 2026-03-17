{{--<x-guest-layout>--}}
{{--    <!-- Session Status -->--}}
{{--    <x-auth-session-status class="mb-4" :status="session('status')" />--}}

{{--    <form method="POST" action="{{ route('login') }}">--}}
{{--        @csrf--}}

{{--        <!-- Email Address -->--}}
{{--        <div>--}}
{{--            <x-input-label for="email" :value="__('อีเมล')" />--}}
{{--            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />--}}
{{--            <x-input-error :messages="$errors->get('email')" class="mt-2" />--}}
{{--        </div>--}}

{{--        <!-- Password -->--}}
{{--        <div class="mt-4">--}}
{{--            <x-input-label for="password" :value="__('รหัสผ่าน')" />--}}

{{--            <x-text-input id="password" class="block mt-1 w-full"--}}
{{--                            type="password"--}}
{{--                            name="password"--}}
{{--                            required autocomplete="current-password" />--}}

{{--            <x-input-error :messages="$errors->get('password')" class="mt-2" />--}}
{{--        </div>--}}

{{--        <!-- Remember Me -->--}}
{{--        <div class="block mt-4">--}}
{{--            <label for="remember_me" class="inline-flex items-center">--}}
{{--                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">--}}
{{--                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>--}}
{{--            </label>--}}
{{--        </div>--}}

{{--        <div class="flex items-center justify-end mt-4">--}}
{{--            @if (Route::has('password.request'))--}}
{{--                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">--}}
{{--                    {{ __('Forgot your password?') }}--}}
{{--                </a>--}}
{{--            @endif--}}

{{--            <x-primary-button id="system_login_btn" class="ms-3">--}}
{{--                {{ __('เข้าสู่ระบบ') }}--}}
{{--            </x-primary-button>--}}
{{--        </div>--}}

{{--        <!-- Google Auth -->--}}
{{--        <div class="flex items-center justify-center mt-4">--}}
{{--            <a id="google_login_btn"--}}
{{--               href="{{ route('auth.google.redirect') }}"--}}
{{--               class="btn bg-blue-100 p-3 shadow-sm border rounded-md text-blue-900 hover:bg-blue-200 transition-colors flex items-center gap-2 cursor-pointer">--}}
{{--                <svg class="w-5 h-5" viewBox="0 0 24 24">--}}
{{--                    <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>--}}
{{--                    <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>--}}
{{--                    <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>--}}
{{--                    <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>--}}
{{--                </svg>--}}
{{--                Continue with Google--}}
{{--            </a>--}}
{{--        </div>--}}

{{--        <!-- Privacy Policy -->--}}
{{--        <div class="block mt-4">--}}
{{--            <label for="privacy_policy" class="inline-flex items-center">--}}
{{--                <input id="privacy_policy" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="policy">--}}
{{--                <span class="ms-2 text-sm text-gray-600">{{ __('ฉันได้อ่านและยอมรับข้อตกลง') }}--}}
{{--                    <div class="relative inline-block group">--}}
{{--                        <span class="underline text-indigo-600 cursor-help">--}}
{{--                            นโยบายความเป็นส่วนตัว *--}}
{{--                        </span>--}}
{{--                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-64 p-3 bg-white text-gray-700 text-xs rounded-lg shadow-xl border border-gray-200 hidden group-hover:block z-50">--}}
{{--                            เราให้ความสำคัญกับความเป็นส่วนตัวของคุณ การลงชื่อเข้าใช้งานถือว่าคุณได้ยินยอมให้มีการเก็บรวบรวมอีเมล รายละเอียดโปรไฟล์ และเอกสารที่คุณอัปโหลด ซึ่งเป็นไปตามนโยบายความเป็นส่วนตัวของเรา--}}
{{--                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1 border-4 border-transparent border-t-white"></div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </span>--}}
{{--            </label>--}}
{{--            <p id="policy_error" class="text-red-500 text-sm mt-1 ml-6 hidden">--}}
{{--                * โปรดยอมรับเงื่อนไขนโยบายความเป็นส่วนตัวเพื่อดำเนินการต่อ--}}
{{--            </p>--}}
{{--        </div>--}}

{{--        <script>--}}
{{--            document.addEventListener('DOMContentLoaded', function () {--}}
{{--                const googleBtn = document.getElementById('google_login_btn');--}}
{{--                const systemBtn = document.getElementById('system_login_btn')--}}
{{--                const checkbox = document.getElementById('privacy_policy');--}}
{{--                const errorMsg = document.getElementById('policy_error');--}}

{{--                googleBtn.addEventListener('click', function (e) {--}}
{{--                    if (!checkbox.checked) {--}}

{{--                        e.preventDefault();--}}

{{--                        checkbox.focus();--}}

{{--                        errorMsg.classList.remove('hidden');--}}
{{--                    }--}}
{{--                });--}}

{{--                systemBtn.addEventListener('click', function (e) {--}}
{{--                    if (!checkbox.checked) {--}}

{{--                        e.preventDefault();--}}

{{--                        checkbox.focus();--}}

{{--                        errorMsg.classList.remove('hidden');--}}
{{--                    }--}}
{{--                });--}}

{{--                checkbox.addEventListener('change', function () {--}}
{{--                    if (this.checked) {--}}
{{--                        errorMsg.classList.add('hidden');--}}
{{--                    }--}}
{{--                });--}}
{{--            });--}}
{{--        </script>--}}
{{--    </form>--}}
{{--</x-guest-layout>--}}

<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="space-y-6">

            <div class="text-center mt-2 mb-6">
                <h2 class="text-xl font-bold text-gray-800">เข้าสู่ระบบ</h2>
                <p class="text-sm text-gray-500 mt-1">เข้าสู่ระบบด้วยอีเมลของมหาวิทยาลัย</p>
            </div>

            <div class="flex items-center justify-center">
                <a id="google_login_btn"
                   href="{{ route('auth.google.redirect') }}"
                   class="w-full justify-center btn bg-blue-50 p-3 shadow-sm border border-blue-200 rounded-md text-blue-700 hover:bg-blue-100 transition-colors flex items-center gap-3 cursor-pointer font-medium text-base">
                    <svg class="w-6 h-6" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continue with Google
                </a>
            </div>

            <div class="block">
                <label for="privacy_policy" class="inline-flex items-start cursor-pointer">
                    <input id="privacy_policy" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mt-1" name="policy">
                    <span class="ms-2 text-sm text-gray-600 leading-tight">{{ __('ฉันได้อ่านและยอมรับข้อตกลง') }}
                        <div class="relative inline-block group">
                            <span class="underline text-indigo-600 cursor-help font-medium">
                                นโยบายความเป็นส่วนตัว *
                            </span>
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-64 p-3 bg-white text-gray-700 text-xs rounded-lg shadow-xl border border-gray-200 hidden group-hover:block z-50">
                                เราให้ความสำคัญกับความเป็นส่วนตัวของคุณ การลงชื่อเข้าใช้งานถือว่าคุณได้ยินยอมให้มีการเก็บรวบรวมอีเมล รายละเอียดโปรไฟล์ และเอกสารที่คุณอัปโหลด ซึ่งเป็นไปตามนโยบายความเป็นส่วนตัวของเรา
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1 border-4 border-transparent border-t-white"></div>
                            </div>
                        </div>
                    </span>
                </label>
                <p id="policy_error" class="text-red-500 text-sm mt-2 ml-6 hidden">
                    * โปรดยอมรับเงื่อนไขนโยบายความเป็นส่วนตัวเพื่อดำเนินการต่อ
                </p>
            </div>
        </div>

        <div class="relative flex py-6 items-center">
            <div class="flex-grow border-t border-gray-200"></div>
            <button type="button" id="toggle_admin_btn" class="flex-shrink-0 mx-4 text-gray-400 text-xs font-medium hover:text-gray-600 transition-colors focus:outline-none">
                เข้าสู่ระบบสำหรับผู้ดูแลระบบ ▼
            </button>
            <div class="flex-grow border-t border-gray-200"></div>
        </div>

        <div id="admin_section" class="{{ $errors->any() ? '' : 'hidden' }} space-y-4 bg-gray-50/50 p-5 rounded-lg border border-gray-100">

            <div>
                <x-input-label for="email" :value="__('อีเมล')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="password" :value="__('รหัสผ่าน')" />
                <x-text-input id="password" class="block mt-1 w-full"
                              type="password"
                              name="password"
                              autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-primary-button id="system_login_btn" class="w-full justify-center">
                    {{ __('เข้าสู่ระบบ') }}
                </x-primary-button>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const googleBtn = document.getElementById('google_login_btn');
                const systemBtn = document.getElementById('system_login_btn');
                const checkbox = document.getElementById('privacy_policy');
                const errorMsg = document.getElementById('policy_error');

                // Toggle Admin Section Logic
                const toggleAdminBtn = document.getElementById('toggle_admin_btn');
                const adminSection = document.getElementById('admin_section');

                toggleAdminBtn.addEventListener('click', function() {
                    adminSection.classList.toggle('hidden');
                    if (adminSection.classList.contains('hidden')) {
                        toggleAdminBtn.innerText = 'เข้าสู่ระบบสำหรับผู้ดูแลระบบ ▼';
                    } else {
                        toggleAdminBtn.innerText = 'เข้าสู่ระบบสำหรับผู้ดูแลระบบ ▲';
                        // โฟกัสช่องอีเมลอัตโนมัติเมื่อกดเปิด
                        document.getElementById('email').focus();
                    }
                });

                // Validation Logic
                function validatePolicy(e) {
                    if (!checkbox.checked) {
                        e.preventDefault();
                        checkbox.focus();
                        errorMsg.classList.remove('hidden');
                        // เลื่อนหน้าจอกลับไปที่ Checkbox ในกรณีที่จอเล็ก
                        checkbox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }

                googleBtn.addEventListener('click', validatePolicy);
                systemBtn.addEventListener('click', validatePolicy);

                checkbox.addEventListener('change', function () {
                    if (this.checked) {
                        errorMsg.classList.add('hidden');
                    }
                });
            });
        </script>
    </form>
</x-guest-layout>
