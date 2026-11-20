<x-layouts.auth title="Lupa kata sandi">
    <p class="mb-5 text-muted">Masukkan emailmu. Kami kirim tautan untuk membuat kata sandi baru.</p>

    @if (session('status'))
        <x-ui.alert tone="success" class="mb-5">{{ session('status') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="grid gap-5">
        @csrf
        <x-ui.field name="email" label="Email" required>
            <x-ui.input type="email" :value="old('email')" autofocus autocomplete="email" />
        </x-ui.field>

        <x-ui.button type="submit" class="w-full" data-test="email-password-reset-link-button">Kirim tautan</x-ui.button>
    </form>

    <p class="mt-6 text-center text-sm text-muted"><a href="{{ route('login') }}" class="inline-flex min-h-11 items-center text-rose underline underline-offset-4">Kembali ke halaman masuk</a></p>
</x-layouts.auth>
