<x-layouts.app title="Pratinjau impor tamu">
    <div class="grid max-w-4xl gap-6">
        <header class="grid gap-2">
            <h1 class="font-serif text-4xl font-semibold text-ink">Pratinjau impor</h1>
            <p class="text-muted">Belum ada yang disimpan. Periksa ringkasannya, lalu konfirmasi.</p>
        </header>

        <dl class="tabular grid grid-cols-[1fr_auto] gap-x-6 gap-y-2 rounded-2xl border border-ink/10 bg-white p-5 sm:max-w-sm">
            <dt class="text-muted">Baris terbaca</dt><dd class="text-right font-semibold text-ink">{{ $report->totalRows }}</dd>
            <dt class="text-muted">Siap diimpor</dt><dd class="text-right font-semibold text-ink">{{ count($report->valid) }}</dd>
            <dt class="text-muted">Ditolak</dt><dd class="text-right font-semibold text-ink">{{ count($report->errors) }}</dd>
            <dt class="text-muted">Duplikat dilewati</dt><dd class="text-right font-semibold text-ink">{{ count($report->duplicates) }}</dd>
        </dl>

        @if ($report->errors)
            <section aria-labelledby="errors" class="grid gap-3">
                <h2 id="errors" class="font-serif text-2xl font-semibold text-ink">Baris yang ditolak</h2>
                <div class="relative overflow-x-auto rounded-2xl border border-ink/10 bg-white" tabindex="0" role="region" aria-label="Pratinjau impor tamu">
                    <table class="w-full min-w-[28rem] text-left">
                        <caption class="sr-only">Baris CSV yang ditolak beserta alasannya</caption>
                        <thead class="bg-rose-light"><tr><th scope="col" class="px-4 py-3">Baris</th><th scope="col" class="px-4 py-3">Kolom</th><th scope="col" class="px-4 py-3">Masalah</th></tr></thead>
                        <tbody class="divide-y divide-ink/10">
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
