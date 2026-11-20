<x-layouts.app title="Akun">
    <div class="grid max-w-3xl gap-8">
        <header class="grid gap-1">
            <h1 class="font-serif text-4xl font-semibold text-ink">Akun</h1>
            <p class="text-muted">Data dirimu dan hakmu atas data itu.</p>
        </header>

        <section aria-labelledby="identity-title" class="grid gap-3 rounded-2xl border border-ink/10 bg-white p-6">
            <h2 id="identity-title" class="font-serif text-2xl font-semibold text-ink">Dirimu</h2>
            <dl class="grid gap-3 sm:grid-cols-[10rem_1fr]">
                <dt class="text-muted">Nama</dt><dd class="font-semibold text-ink">{{ $user->name }}</dd>
                <dt class="text-muted">Email</dt><dd class="font-semibold text-ink [overflow-wrap:anywhere]">{{ $user->email }}</dd>
                <dt class="text-muted">Undangan</dt><dd class="tabular font-semibold text-ink">{{ $invitations }}</dd>
            </dl>
        </section>

        <section aria-labelledby="export-title" class="grid gap-3 rounded-2xl border border-ink/10 bg-white p-6">
            <h2 id="export-title" class="font-serif text-2xl font-semibold text-ink">Salinan datamu</h2>
            <p class="text-muted">Unduh satu berkas JSON berisi datamu, semua undanganmu, daftar tamu, konfirmasi kehadiran, dan ucapan. Berkas ini tidak berisi kata sandi.</p>
            <div><x-ui.button variant="secondary" :href="route('dashboard.account.export')">Unduh data saya</x-ui.button></div>
        </section>

        <section aria-labelledby="delete-title" class="grid gap-4 rounded-2xl border border-danger/40 bg-white p-6">
            <h2 id="delete-title" class="font-serif text-2xl font-semibold text-ink">Hapus akun</h2>
            <x-ui.alert tone="danger" title="Tidak bisa dibatalkan">
                Semua undanganmu, daftar tamu, konfirmasi kehadiran, ucapan, dan foto ikut terhapus. Tautan undangan yang sudah dibagikan tidak lagi terbuka. Unduh salinan datamu dulu bila perlu.
            </x-ui.alert>
            <form method="POST" action="{{ route('dashboard.account.destroy') }}" class="grid gap-4">
                @csrf
                @method('DELETE')
                <x-ui.field name="password" label="Kata sandi" required>
                    <x-ui.input type="password" autocomplete="current-password" />
                </x-ui.field>
                <x-ui.field name="confirm" label="Ketik HAPUS untuk mengonfirmasi" required>
                    <x-ui.input autocomplete="off" />
                </x-ui.field>
                <div><x-ui.button variant="danger" type="submit">Hapus akun saya</x-ui.button></div>
            </form>
        </section>
    </div>
</x-layouts.app>
