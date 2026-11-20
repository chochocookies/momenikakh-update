<x-layouts.auth title="Masuk">
    @if (session('status'))
        <x-ui.alert tone="success" class="mb-5">{{ session('status') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="grid gap-5">
        @csrf
        <x-ui.field name="email" label="Email" required>
            <x-ui.input type="email" :value="old('email')" autofocus autocomplete="email" />
        </x-ui.field>

        <x-ui.field name="password" label="Kata sandi" required>
            <x-ui.input type="password" autocomplete="current-password" />
        </x-ui.field>

        <label for="remember" class="inline-flex min-h-11 items-center gap-3 text-base text-ink">
            <input id="remember" name="remember" type="checkbox" @checked(old('remember')) class="size-5">
            Ingat saya di perangkat ini
        </label>

        <x-ui.button type="submit" class="w-full" data-test="login-button">Masuk</x-ui.button>
    </form>

    <div class="mt-6 grid gap-1 text-center text-sm text-muted">
        @if (Route::has('password.request'))
            <p><a href="{{ route('password.request') }}" class="inline-flex min-h-11 items-center text-rose underline underline-offset-4">Lupa kata sandi?</a></p>
        @endif
        @if (Route::has('register'))
            <p>Belum punya akun? <a href="{{ route('register') }}" class="inline-flex min-h-11 items-center text-rose underline underline-offset-4">Daftar</a></p>
        @endif
    </div>
</x-layouts.auth>
