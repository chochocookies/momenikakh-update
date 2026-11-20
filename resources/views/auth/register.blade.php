<x-layouts.auth title="Buat akun">
    <form method="POST" action="{{ route('register.store') }}" class="grid gap-5">
        @csrf
        <x-ui.field name="name" label="Nama" required>
            <x-ui.input :value="old('name')" autofocus autocomplete="name" />
        </x-ui.field>

        <x-ui.field name="email" label="Email" required>
            <x-ui.input type="email" :value="old('email')" autocomplete="email" />
        </x-ui.field>

        <x-ui.field name="password" label="Kata sandi" hint="Minimal 8 karakter." required>
            <x-ui.input type="password" autocomplete="new-password" />
        </x-ui.field>

        <x-ui.field name="password_confirmation" label="Ulangi kata sandi" required>
            <x-ui.input type="password" autocomplete="new-password" />
        </x-ui.field>

        <x-ui.button type="submit" class="w-full" data-test="register-button">Buat akun</x-ui.button>
    </form>

    <p class="mt-6 text-center text-sm text-muted">Sudah punya akun? <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center text-rose underline underline-offset-4">Masuk</a></p>
</x-layouts.auth>
