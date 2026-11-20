<x-layouts.auth title="Buat kata sandi baru">
    <form method="POST" action="{{ route('password.update') }}" class="grid gap-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-ui.field name="email" label="Email" required>
            <x-ui.input type="email" :value="old('email', $request->email)" autocomplete="email" />
        </x-ui.field>

        <x-ui.field name="password" label="Kata sandi baru" hint="Minimal 8 karakter." required>
            <x-ui.input type="password" autofocus autocomplete="new-password" />
        </x-ui.field>

        <x-ui.field name="password_confirmation" label="Ulangi kata sandi baru" required>
            <x-ui.input type="password" autocomplete="new-password" />
        </x-ui.field>

        <x-ui.button type="submit" class="w-full" data-test="reset-password-button">Simpan kata sandi</x-ui.button>
    </form>
</x-layouts.auth>
