<x-layouts.auth title="Verifikasi email">
    <p class="mb-5 text-muted">Kami sudah mengirim tautan verifikasi ke emailmu. Buka tautan itu untuk mulai. Belum menerima? Kami kirim lagi.</p>

    @if (session('status') === 'verification-link-sent')
        <x-ui.alert tone="success" class="mb-5">Tautan verifikasi baru sudah dikirim ke emailmu.</x-ui.alert>
    @endif

    <div class="flex flex-wrap items-center gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-ui.button type="submit">Kirim ulang email verifikasi</x-ui.button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button variant="ghost" type="submit">Keluar</x-ui.button>
        </form>
    </div>
</x-layouts.auth>
