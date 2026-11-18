<x-layouts.app title="Pratinjau impor tamu">
    <div class="mx-auto grid max-w-4xl gap-6 px-5 py-8">
        <header class="grid gap-2">
            <h1 class="font-serif text-4xl font-semibold text-brand-ink">Pratinjau impor</h1>
            <p class="text-brand-muted">Belum ada yang disimpan. Periksa ringkasannya, lalu konfirmasi.</p>
        </header>

        <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <x-ui.card><dt class="text-sm text-brand-muted">Baris terbaca</dt><dd class="font-serif text-3xl font-semibold">{{ $report->totalRows }}</dd></x-ui.card>
            <x-ui.card><dt class="text-sm text-brand-muted">Siap diimpor</dt><dd class="font-serif text-3xl font-semibold">{{ count($report->valid) }}</dd></x-ui.card>
            <x-ui.card><dt class="text-sm text-brand-muted">Ditolak</dt><dd class="font-serif text-3xl font-semibold">{{ count($report->errors) }}</dd></x-ui.card>
            <x-ui.card><dt class="text-sm text-brand-muted">Duplikat dilewati</dt><dd class="font-serif text-3xl font-semibold">{{ count($report->duplicates) }}</dd></x-ui.card>
        </dl>

        @if ($report->errors)
            <section aria-labelledby="errors" class="grid gap-3">
                <h2 id="errors" class="font-serif text-2xl font-semibold text-brand-ink">Baris yang ditolak</h2>
                <div class="overflow-x-auto rounded-2xl ring-1 ring-brand-ink/10">
                    <table class="w-full min-w-[28rem] text-left">
                        <caption class="sr-only">Baris CSV yang ditolak beserta alasannya</caption>
                        <thead class="bg-brand-rose-light"><tr><th scope="col" class="px-4 py-3">Baris</th><th scope="col" class="px-4 py-3">Kolom</th><th scope="col" class="px-4 py-3">Masalah</th></tr></thead>
                        <tbody class="divide-y divide-brand-ink/10">
                            @foreach (array_slice($report->errors, 0, 100) as $e)
                                <tr>
                                    <td class="px-4 py-3">{{ $e['line'] }}</td>
                                    <td class="px-4 py-3">{{ ['name' => 'Nama', 'group' => 'Kelompok', 'phone' => 'Telepon'][$e['field']] ?? $e['field'] }}</td>
                                    <td class="px-4 py-3">{{ $e['message'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <div class="flex flex-wrap gap-3">
            @if ($report->canCommit())
                <form method="POST" action="{{ route('dashboard.guests.import.commit', [$invitation, $importId]) }}">
                    @csrf
                    <x-ui.button type="submit">Impor {{ count($report->valid) }} tamu</x-ui.button>
                </form>
            @endif
            <form method="POST" action="{{ route('dashboard.guests.import.cancel', [$invitation, $importId]) }}">
                @csrf
                @method('DELETE')
                <x-ui.button variant="secondary" type="submit">Batal</x-ui.button>
            </form>
        </div>
    </div>
</x-layouts.app>
