<x-layouts.app :title="'Tamu: '.$invitation->title">
    @if (session('toast'))
        <div hidden data-toast="{{ session('toast') }}" data-tone="success"></div>
    @endif

    <div class="mx-auto grid max-w-6xl gap-8 px-5 py-8">
        <header class="grid gap-2">
            <h1 class="font-serif text-4xl font-semibold text-brand-ink">Daftar tamu</h1>
            <p class="text-brand-muted">{{ $invitation->title }}: {{ $total }} tamu, {{ $opened }} sudah membuka undangan.</p>
        </header>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-ui.card>
                <h2 class="font-serif text-2xl font-semibold text-brand-ink">Tambah tamu</h2>
                <form method="POST" action="{{ route('dashboard.guests.store', $invitation) }}" class="mt-4 grid gap-4">
                    @csrf
                    <x-ui.field name="name" label="Nama tamu" required>
                        <x-ui.input autocomplete="off" :value="old('name')" />
                    </x-ui.field>
                    <x-ui.field name="group" label="Kelompok" hint="Misalnya Keluarga, Teman kantor.">
                        <x-ui.input autocomplete="off" :value="old('group')" />
                    </x-ui.field>
                    <x-ui.field name="phone" label="Nomor WhatsApp" hint="Boleh dikosongkan. Contoh: 081234567890.">
                        <x-ui.input type="tel" autocomplete="off" inputmode="tel" :value="old('phone')" />
                    </x-ui.field>
                    <div><x-ui.button type="submit">Tambah tamu</x-ui.button></div>
                </form>
            </x-ui.card>

            <x-ui.card>
                <h2 class="font-serif text-2xl font-semibold text-brand-ink">Impor dari CSV</h2>
                <p class="mt-2 text-brand-muted">Kolom yang dikenali: nama, kelompok, telepon. Baris pertama harus berisi judul kolom. Tulis nomor telepon sebagai teks agar angka nol di depan tidak hilang.</p>
                <form method="POST" action="{{ route('dashboard.guests.import.preview', $invitation) }}" enctype="multipart/form-data" class="mt-4 grid gap-4">
                    @csrf
                    <x-ui.field name="file" label="Berkas CSV" required>
                        <x-ui.input type="file" accept=".csv,text/csv" />
                    </x-ui.field>
                    <div class="flex flex-wrap gap-3">
                        <x-ui.button type="submit">Lihat pratinjau</x-ui.button>
                        <x-ui.button variant="secondary" :href="route('dashboard.guests.template', $invitation)">Unduh templat</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <section aria-labelledby="guest-list" class="grid gap-4">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h2 id="guest-list" class="font-serif text-2xl font-semibold text-brand-ink">Tamu dan tautan</h2>
                <form method="GET" role="search" class="flex items-end gap-2">
                    <x-ui.field name="q" label="Cari nama">
                        <x-ui.input type="search" :value="$q" />
                    </x-ui.field>
                    <x-ui.button variant="secondary" type="submit">Cari</x-ui.button>
                </form>
            </div>

            @if ($rows->isEmpty())
                <x-ui.empty-state title="Belum ada tamu" icon="users">
                    Tambahkan tamu satu per satu atau impor dari CSV. Setiap tamu mendapat tautan sendiri.
                </x-ui.empty-state>
            @else
                <div class="overflow-x-auto rounded-2xl ring-1 ring-brand-ink/10">
                    <table class="w-full min-w-[40rem] text-left">
                        <caption class="sr-only">Daftar tamu undangan beserta tautan pribadi</caption>
                        <thead class="bg-brand-rose-light text-brand-ink">
                            <tr>
                                <th scope="col" class="px-4 py-3">Nama</th>
                                <th scope="col" class="px-4 py-3">Kelompok</th>
                                <th scope="col" class="px-4 py-3">WhatsApp</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3">Tautan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-ink/10">
                            @foreach ($rows as $row)
                                @php($g = $row['guest'])
                                <tr>
                                    <th scope="row" class="px-4 py-3 font-semibold text-brand-ink">{{ $g->name }}</th>
                                    <td class="px-4 py-3 text-brand-muted">{{ $g->group ?? '-' }}</td>
                                    <td class="px-4 py-3 text-brand-muted">{{ $g->phone ? '+'.$g->phone : '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($g->opened_at)
                                            <x-ui.badge tone="rose">Sudah dibuka</x-ui.badge>
                                        @else
                                            <x-ui.badge>Belum dibuka</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <x-ui.button variant="secondary" type="button" data-copy="{{ $row['url'] }}" data-copy-done="Tautan {{ $g->name }} disalin">Salin</x-ui.button>
                                            <x-ui.button :href="$row['whatsapp']" target="_blank" rel="noopener noreferrer">Kirim WhatsApp</x-ui.button>
                                            <form method="POST" action="{{ route('dashboard.guests.token', [$invitation, $g]) }}">
                                                @csrf
                                                <x-ui.button variant="ghost" type="submit">Reset tautan</x-ui.button>
                                            </form>
                                            <form method="POST" action="{{ route('dashboard.guests.destroy', [$invitation, $g]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.button variant="ghost" type="submit">Hapus</x-ui.button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $guests->links() }}
            @endif
        </section>

        <section aria-labelledby="exports" class="grid gap-3">
            <h2 id="exports" class="font-serif text-2xl font-semibold text-brand-ink">Ekspor data</h2>
            <div class="flex flex-wrap gap-3">
                <x-ui.button variant="secondary" :href="route('dashboard.guests.export.rsvps', $invitation)">RSVP (CSV)</x-ui.button>
                <x-ui.button variant="secondary" :href="route('dashboard.guests.export.messages', $invitation)">Ucapan (CSV)</x-ui.button>
                <x-ui.button variant="ghost" :href="route('dashboard.guests.export.rsvps', [$invitation, 'format' => 'excel-id'])">RSVP untuk Excel Indonesia</x-ui.button>
            </div>
        </section>
    </div>
</x-layouts.app>
