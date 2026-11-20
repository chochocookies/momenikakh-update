<x-layouts.marketing title="Styleguide" description="Komponen dan token desain Momenikah.">
    <div class="mx-auto max-w-6xl space-y-16 px-5 py-10">
        <header class="space-y-3">
            <h1 class="font-serif text-5xl font-semibold text-ink">Styleguide</h1>
            <p class="max-w-2xl text-lg text-muted">Semua token dan komponen antarmuka Momenikah di satu halaman. Halaman ini hanya aktif di luar produksi.</p>
            <nav aria-label="Bagian styleguide" class="flex flex-wrap gap-2">
                <x-ui.button href="#warna" variant="ghost">Warna</x-ui.button>
                <x-ui.button href="#tipografi" variant="ghost">Tipografi</x-ui.button>
                <x-ui.button href="#komponen" variant="ghost">Komponen</x-ui.button>
                <x-ui.button href="#formulir" variant="ghost">Formulir</x-ui.button>
                <x-ui.button href="#template" variant="ghost">Template</x-ui.button>
                <x-ui.button href="#bentuk" variant="ghost">Bentuk dan ruang</x-ui.button>
            </nav>
        </header>

        <section id="warna" aria-labelledby="warna-judul" class="space-y-8">
            <h2 id="warna-judul" class="border-b border-ink/10 pb-3 font-serif text-3xl font-semibold text-ink">Warna</h2>

            <div class="grid gap-8 md:grid-cols-2">
                <div class="space-y-4">
                    <h3 class="font-serif text-xl font-semibold text-ink">Mode terang</h3>
                    <ul class="grid grid-cols-2 gap-4">
                        <li class="space-y-2"><div class="h-24 rounded-lg border border-ink/10 bg-ivory"></div><code class="text-sm text-muted">ivory</code></li>
                        <li class="space-y-2"><div class="h-24 rounded-lg border border-ink/10 bg-ink"></div><code class="text-sm text-muted">ink</code></li>
                        <li class="space-y-2"><div class="h-24 rounded-lg border border-ink/10 bg-rose"></div><code class="text-sm text-muted">rose</code></li>
                        <li class="space-y-2"><div class="h-24 rounded-lg border border-ink/10 bg-muted"></div><code class="text-sm text-muted">muted</code></li>
                        <li class="space-y-2"><div class="h-24 rounded-lg border border-ink/10 bg-rose-light"></div><code class="text-sm text-muted">rose-light</code></li>
                        <li class="space-y-2"><div class="h-24 rounded-lg border border-ink/10 bg-gold"></div><code class="text-sm text-muted">gold (hanya dekorasi)</code></li>
                        <li class="space-y-2"><div class="h-24 rounded-lg border border-ink/10 bg-danger"></div><code class="text-sm text-muted">danger</code></li>
                    </ul>
                </div>

                <div class="space-y-4 rounded-xl bg-brand-bg-dark p-6">
                    <h3 class="font-serif text-xl font-semibold text-brand-ink-dark">Mode gelap</h3>
                    <ul class="grid grid-cols-2 gap-4">
                        <li class="space-y-2"><div class="h-24 rounded-lg border border-brand-line-dark bg-brand-bg-dark"></div><code class="text-sm text-brand-muted-dark">brand-bg-dark</code></li>
                        <li class="space-y-2"><div class="h-24 rounded-lg border border-brand-line-dark bg-brand-ink-dark"></div><code class="text-sm text-brand-muted-dark">brand-ink-dark</code></li>
                        <li class="space-y-2"><div class="h-24 rounded-lg border border-brand-line-dark bg-brand-rose-dark"></div><code class="text-sm text-brand-muted-dark">brand-rose-dark</code></li>
                        <li class="space-y-2"><div class="h-24 rounded-lg border border-brand-line-dark bg-brand-muted-dark"></div><code class="text-sm text-brand-muted-dark">brand-muted-dark</code></li>
                        <li class="space-y-2"><div class="h-24 rounded-lg border border-brand-line-dark bg-brand-rose-light-dark"></div><code class="text-sm text-brand-muted-dark">brand-rose-light-dark</code></li>
                    </ul>
                </div>
            </div>
        </section>

        <section id="tipografi" aria-labelledby="tipografi-judul" class="space-y-8">
            <h2 id="tipografi-judul" class="border-b border-ink/10 pb-3 font-serif text-3xl font-semibold text-ink">Tipografi</h2>

            <div class="grid gap-6 md:grid-cols-2">
                <x-ui.card>
                    <p class="font-serif text-4xl font-semibold text-ink">Cormorant Garamond</p>
                    <p class="mt-3 font-serif text-lg text-muted">Untuk judul dan elemen dekoratif.</p>
                </x-ui.card>
                <x-ui.card>
                    <p class="font-sans text-4xl font-bold text-ink">Plus Jakarta Sans</p>
                    <p class="mt-3 font-sans text-lg text-muted">Untuk isi, formulir, dan antarmuka.</p>
                </x-ui.card>
            </div>

            <h3 class="font-serif text-xl font-semibold text-ink">Skala huruf</h3>
            <ul class="space-y-3">
                <li class="flex flex-wrap items-baseline gap-4"><span class="w-24 text-sm text-muted">text-xs</span><span class="text-xs text-ink">Satu link untuk semua momen nikahmu</span></li>
                <li class="flex flex-wrap items-baseline gap-4"><span class="w-24 text-sm text-muted">text-sm</span><span class="text-sm text-ink">Satu link untuk semua momen nikahmu</span></li>
                <li class="flex flex-wrap items-baseline gap-4"><span class="w-24 text-sm text-muted">text-base</span><span class="text-base text-ink">Satu link untuk semua momen nikahmu</span></li>
                <li class="flex flex-wrap items-baseline gap-4"><span class="w-24 text-sm text-muted">text-lg</span><span class="text-lg text-ink">Satu link untuk semua momen nikahmu</span></li>
                <li class="flex flex-wrap items-baseline gap-4"><span class="w-24 text-sm text-muted">text-xl</span><span class="text-xl text-ink">Satu link untuk semua momen nikahmu</span></li>
                <li class="flex flex-wrap items-baseline gap-4"><span class="w-24 text-sm text-muted">text-2xl</span><span class="text-2xl text-ink">Satu link untuk semua momen</span></li>
                <li class="flex flex-wrap items-baseline gap-4"><span class="w-24 text-sm text-muted">text-3xl</span><span class="font-serif text-3xl text-ink">Satu link untuk semua momen</span></li>
                <li class="flex flex-wrap items-baseline gap-4"><span class="w-24 text-sm text-muted">text-4xl</span><span class="font-serif text-4xl text-ink">Satu link untuk semua</span></li>
                <li class="flex flex-wrap items-baseline gap-4"><span class="w-24 text-sm text-muted">text-5xl</span><span class="font-serif text-5xl text-ink">Satu link</span></li>
                <li class="flex flex-wrap items-baseline gap-4"><span class="w-24 text-sm text-muted">text-6xl</span><span class="font-serif text-6xl text-ink">Satu link</span></li>
            </ul>

            <h3 class="font-serif text-xl font-semibold text-ink">Ketebalan</h3>
            <ul class="grid gap-3 sm:grid-cols-2 md:grid-cols-5">
                <li class="font-normal text-ink">Normal 400</li>
                <li class="font-medium text-ink">Medium 500</li>
                <li class="font-semibold text-ink">Semibold 600</li>
                <li class="font-bold text-ink">Bold 700</li>
                <li class="font-extrabold text-ink">Extrabold 800</li>
            </ul>
        </section>

        <section id="komponen" aria-labelledby="komponen-judul" class="space-y-10">
            <h2 id="komponen-judul" class="border-b border-ink/10 pb-3 font-serif text-3xl font-semibold text-ink">Komponen</h2>

            <div class="space-y-4">
                <h3 class="font-serif text-xl font-semibold text-ink">Tombol</h3>
                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.button>Utama</x-ui.button>
                    <x-ui.button variant="secondary">Sekunder</x-ui.button>
                    <x-ui.button variant="ghost">Ghost</x-ui.button>
                    <x-ui.button variant="danger">Hapus</x-ui.button>
                    <x-ui.button size="lg">Besar</x-ui.button>
                    <x-ui.button href="#komponen">Tautan</x-ui.button>
                    <x-ui.button :loading="true">Menyimpan</x-ui.button>
                    <x-ui.button disabled>Nonaktif</x-ui.button>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="font-serif text-xl font-semibold text-ink">Lencana</h3>
                <div class="flex flex-wrap gap-3">
                    <x-ui.badge>Draf</x-ui.badge>
                    <x-ui.badge tone="rose">Terbit</x-ui.badge>
                    <x-ui.badge tone="success">Lunas</x-ui.badge>
                    <x-ui.badge tone="danger">Kedaluwarsa</x-ui.badge>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="font-serif text-xl font-semibold text-ink">Ikon</h3>
                <ul class="flex flex-wrap gap-4 text-ink">
                    <li><x-ui.icon name="heart" size="lg" /></li>
                    <li><x-ui.icon name="calendar" size="lg" /></li>
                    <li><x-ui.icon name="map-pin" size="lg" /></li>
                    <li><x-ui.icon name="gift" size="lg" /></li>
                    <li><x-ui.icon name="music" size="lg" /></li>
                    <li><x-ui.icon name="link" size="lg" /></li>
                    <li><x-ui.icon name="share-2" size="lg" label="Bagikan" /></li>
                </ul>
            </div>

            <div class="space-y-4">
                <h3 class="font-serif text-xl font-semibold text-ink">Kartu dan keadaan kosong</h3>
                <div class="grid gap-6 md:grid-cols-2">
                    <x-ui.card>
                        <p class="font-serif text-2xl font-semibold text-ink">Raka dan Sinta</p>
                        <p class="mt-2 text-muted">Kartu dipakai untuk mengelompokkan isi.</p>
                    </x-ui.card>
                    <x-ui.empty-state title="Belum ada tamu" description="Tambahkan tamu pertama, lalu bagikan tautannya lewat WhatsApp." icon="users">
                        <x-ui.button variant="secondary">Tambah tamu</x-ui.button>
                    </x-ui.empty-state>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="font-serif text-xl font-semibold text-ink">Tab</h3>
                <x-ui.tabs label="Contoh tab" :tabs="[['id' => 'sg-detail', 'label' => 'Detail'], ['id' => 'sg-tamu', 'label' => 'Tamu'], ['id' => 'sg-statistik', 'label' => 'Statistik']]">
                    <div role="tabpanel" id="panel-sg-detail" aria-labelledby="tab-sg-detail" tabindex="0" class="py-4 text-ink">Isi tab detail.</div>
                    <div role="tabpanel" id="panel-sg-tamu" aria-labelledby="tab-sg-tamu" tabindex="0" hidden class="py-4 text-ink">Isi tab tamu.</div>
                    <div role="tabpanel" id="panel-sg-statistik" aria-labelledby="tab-sg-statistik" tabindex="0" hidden class="py-4 text-ink">Isi tab statistik.</div>
                </x-ui.tabs>
            </div>

            <div class="space-y-4">
                <h3 class="font-serif text-xl font-semibold text-ink">Pesan (alert)</h3>
                <div class="grid gap-3 md:grid-cols-2">
                    <x-ui.alert title="Informasi">Undangan sample bisa diisi dan dipratinjau.</x-ui.alert>
                    <x-ui.alert tone="success" title="Tersimpan">Perubahan sudah disimpan.</x-ui.alert>
                    <x-ui.alert tone="warning" title="Periksa lagi">Tanggal acara belum diisi.</x-ui.alert>
                    <x-ui.alert tone="danger" title="Belum bisa diterbitkan">Lengkapi data wajib terlebih dahulu.</x-ui.alert>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="font-serif text-xl font-semibold text-ink">Pagination</h3>
                <x-ui.pagination :paginator="$demoPaginator" />
            </div>
            <div class="space-y-4">
                <h3 class="font-serif text-xl font-semibold text-ink">Dialog</h3>
                <x-ui.button variant="secondary" data-modal-open="sg-dialog">Buka dialog</x-ui.button>
            </div>
        </section>

        <section id="formulir" aria-labelledby="formulir-judul" class="space-y-8">
            <h2 id="formulir-judul" class="border-b border-ink/10 pb-3 font-serif text-3xl font-semibold text-ink">Formulir</h2>

            <div class="grid gap-6 md:grid-cols-2">
                <x-ui.field name="sg_nama" label="Nama tamu" hint="Tulis nama seperti yang kamu panggil." :required="true">
                    <x-ui.input placeholder="Contoh: Ni Luh Ayu" />
                </x-ui.field>

                <x-ui.field name="sg_telepon" label="Nomor WhatsApp">
                    <x-ui.input type="tel" placeholder="0812…" />
                </x-ui.field>

                <x-ui.select name="sg_kelompok" label="Kelompok">
                    <option value="keluarga">Keluarga</option>
                    <option value="teman">Teman</option>
                    <option value="rekan">Rekan kerja</option>
                </x-ui.select>

                <x-ui.textarea name="sg_ucapan" label="Ucapan" />

                <x-ui.textarea name="sg_ucapan_error" label="Ucapan (contoh galat)" error="Ucapan wajib diisi." />

                <x-ui.field name="sg_nonaktif" label="Kolom nonaktif">
                    <x-ui.input disabled placeholder="Tidak bisa diubah" />
                </x-ui.field>
            </div>
        </section>

        <section id="template" aria-labelledby="template-judul" class="space-y-8">
            <h2 id="template-judul" class="border-b border-ink/10 pb-3 font-serif text-3xl font-semibold text-ink">Warna template</h2>
            <p class="max-w-2xl text-lg text-muted">Setiap template memakai token sendiri. Gradien memakai token <code>background-image</code>.</p>

            <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <li class="overflow-hidden rounded-xl border border-ink/10">
                    <div class="h-28 bg-template-classic-bg"></div>
                    <div class="space-y-1 p-4"><p class="font-serif text-lg font-semibold text-ink">Classic</p><code class="text-sm text-muted">template-classic-*</code></div>
                </li>
                <li class="overflow-hidden rounded-xl border border-ink/10">
                    <div class="h-28 bg-template-beach-bg"></div>
                    <div class="space-y-1 p-4"><p class="font-serif text-lg font-semibold text-ink">Beach</p><code class="text-sm text-muted">template-beach-bg (gradien)</code></div>
                </li>
                <li class="overflow-hidden rounded-xl border border-ink/10">
                    <div class="h-28 bg-template-floral-bg"></div>
                    <div class="space-y-1 p-4"><p class="font-serif text-lg font-semibold text-ink">Floral</p><code class="text-sm text-muted">template-floral-bg (gradien)</code></div>
                </li>
                <li class="overflow-hidden rounded-xl border border-ink/10">
                    <div class="h-28 bg-template-luxury-bg"></div>
                    <div class="space-y-1 p-4"><p class="font-serif text-lg font-semibold text-ink">Luxury</p><code class="text-sm text-muted">template-luxury-*</code></div>
                </li>
                <li class="overflow-hidden rounded-xl border border-ink/10">
                    <div class="h-28 bg-template-minimal-bg"></div>
                    <div class="space-y-1 p-4"><p class="font-serif text-lg font-semibold text-ink">Minimal</p><code class="text-sm text-muted">template-minimal-*</code></div>
                </li>
                <li class="overflow-hidden rounded-xl border border-ink/10">
                    <div class="h-28 bg-template-rustic-bg"></div>
                    <div class="space-y-1 p-4"><p class="font-serif text-lg font-semibold text-ink">Rustic</p><code class="text-sm text-muted">template-rustic-*</code></div>
                </li>
            </ul>
        </section>

        <section id="bentuk" aria-labelledby="bentuk-judul" class="space-y-8">
            <h2 id="bentuk-judul" class="border-b border-ink/10 pb-3 font-serif text-3xl font-semibold text-ink">Bentuk dan ruang</h2>

            <div class="grid gap-8 md:grid-cols-2">
                <div class="space-y-4">
                    <h3 class="font-serif text-xl font-semibold text-ink">Radius</h3>
                    <ul class="flex flex-wrap gap-4">
                        <li class="space-y-2"><div class="size-16 rounded-xs border border-ink/20 bg-rose-light"></div><code class="text-sm text-muted">xs</code></li>
                        <li class="space-y-2"><div class="size-16 rounded-sm border border-ink/20 bg-rose-light"></div><code class="text-sm text-muted">sm</code></li>
                        <li class="space-y-2"><div class="size-16 rounded-md border border-ink/20 bg-rose-light"></div><code class="text-sm text-muted">md</code></li>
                        <li class="space-y-2"><div class="size-16 rounded-lg border border-ink/20 bg-rose-light"></div><code class="text-sm text-muted">lg</code></li>
                        <li class="space-y-2"><div class="size-16 rounded-xl border border-ink/20 bg-rose-light"></div><code class="text-sm text-muted">xl</code></li>
                        <li class="space-y-2"><div class="size-16 rounded-full border border-ink/20 bg-rose-light"></div><code class="text-sm text-muted">full</code></li>
                    </ul>
                </div>

                <div class="space-y-4">
                    <h3 class="font-serif text-xl font-semibold text-ink">Ruang</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center gap-3"><code class="w-20 text-sm text-muted">spacing-1</code><div class="h-4 w-1 rounded-xs bg-rose"></div></li>
                        <li class="flex items-center gap-3"><code class="w-20 text-sm text-muted">spacing-2</code><div class="h-4 w-2 rounded-xs bg-rose"></div></li>
                        <li class="flex items-center gap-3"><code class="w-20 text-sm text-muted">spacing-4</code><div class="h-4 w-4 rounded-xs bg-rose"></div></li>
                        <li class="flex items-center gap-3"><code class="w-20 text-sm text-muted">spacing-8</code><div class="h-4 w-8 rounded-xs bg-rose"></div></li>
                        <li class="flex items-center gap-3"><code class="w-20 text-sm text-muted">spacing-12</code><div class="h-4 w-12 rounded-xs bg-rose"></div></li>
                        <li class="flex items-center gap-3"><code class="w-20 text-sm text-muted">spacing-16</code><div class="h-4 w-16 rounded-xs bg-rose"></div></li>
                    </ul>
                </div>
            </div>
        </section>
    </div>

    <x-ui.modal id="sg-dialog" title="Contoh dialog">
        <p class="text-ink">Dialog memakai elemen <code>dialog</code> bawaan peramban.</p>
        <div class="mt-6 flex justify-end">
            <x-ui.button variant="secondary" data-modal-close>Tutup</x-ui.button>
        </div>
    </x-ui.modal>
</x-layouts.marketing>
