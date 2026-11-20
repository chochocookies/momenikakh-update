<x-layouts.marketing
    title="Undangan pernikahan digital"
    description="Buat undangan pernikahan digital yang indah, bagikan lewat satu tautan WhatsApp, dan biarkan tamu membuka cerita kalian dari ponsel mereka."
    :assets="['resources/css/app.css', 'resources/js/app.js', 'resources/css/landing.css', 'resources/js/landing.js']">
    <div class="lp" data-countdown-target="{{ $countdownTarget }}">
        <div id="top"></div>

        <section class="lp-w lp-hero" aria-labelledby="hero-title">
            <div>
                <h1 id="hero-title">Satu link untuk semua momen nikahmu.</h1>
                <p class="lp-lead">Buat undangan digital yang indah, bagikan lewat WhatsApp, dan biarkan tamu membuka cerita kalian dari ponsel masing-masing.</p>
                <div class="lp-cta">
                    <a class="lp-btn" href="{{ route('register') }}">Buat undangan</a>
                    <a class="lp-btn lp-btn--ghost" href="#gaya">Lihat semua template</a>
                </div>
                <p class="lp-note">Tanpa unduh aplikasi. Cukup satu tautan.</p>
            </div>

            <div class="lp-demo">
                <div id="hero" class="lp-hero-phone" role="group" aria-label="Contoh undangan: {{ $showcase[0]['name'] }}">
                    @foreach ($showcase as $i => $template)
                        <x-marketing.phone :template="$template" size="lg" :date-long="$dateLong" :date-short="$dateShort" :hidden="$i > 0" />
                    @endforeach
                </div>
                <div class="lp-chips" role="group" aria-label="Pilih template contoh">
                    @foreach ($showcase as $i => $template)
                        <button type="button" class="lp-chip lp-chip--{{ $template['key'] }}" data-i="{{ $i }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}"><i></i>{{ $template['name'] }}</button>
                    @endforeach
                </div>
                <button type="button" class="lp-btn lp-btn--ghost" id="replay">Putar ulang pembukaan</button>
            </div>
        </section>

        <section class="lp-sec" id="gaya" aria-labelledby="gaya-title">
            <div class="lp-w">
                <h2 id="gaya-title">Enam gaya, satu cerita yang sama.</h2>
                <p class="lp-sub">Setiap template punya suasana dan gerak sendiri. Pilih yang paling mirip dengan hari bahagiamu, isi datanya, selesai.</p>
                <ul class="lp-gal" role="list">
                    @foreach ($showcase as $i => $template)
                        <li class="lp-gc">
                            <x-marketing.phone :template="$template" :date-long="$dateLong" :date-short="$dateShort" />
                            <h3>{{ $template['name'] }}</h3>
                            <p>{{ $template['desc'] }}</p>
                            <button type="button" class="lp-btn lp-btn--ghost" data-try="{{ $i }}" aria-label="Coba template {{ $template['name'] }}">Coba template</button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section class="lp-sec" aria-labelledby="langkah-title">
            <div class="lp-w">
                <h2 id="langkah-title">Dari ide sampai terkirim, tiga langkah.</h2>
                <ol class="lp-steps">
                    <li><b>Pilih template</b><span>Coba semua gaya dengan data contoh sebelum memutuskan.</span></li>
                    <li><b>Isi cerita kalian</b><span>Nama, tanggal, lokasi, foto, kisah cinta, dan musik latar.</span></li>
                    <li><b>Bagikan lewat WhatsApp</b><span>Kirim satu tautan. Tamu langsung membuka undangan di ponselnya.</span></li>
                </ol>
            </div>
        </section>

        <section class="lp-sec" aria-labelledby="fitur-title">
            <div class="lp-w">
                <h2 id="fitur-title">Yang ada di dalam undanganmu.</h2>
                <div class="lp-feat">
                    <div><b>Kisah cinta</b><span>Ceritakan pertemuan sampai lamaran dalam garis waktu.</span></div>
                    <div><b>Galeri foto</b><span>Tampilkan foto prewedding yang tetap ringan dibuka di ponsel.</span></div>
                    <div><b>Musik latar</b><span>Pasang lagu pilihan sebagai pengiring undangan.</span></div>
                    <div><b>Lokasi acara</b><span>Tamu membuka peta menuju tempat akad dan resepsi.</span></div>
                    <div><b>Amplop digital</b><span>Cantumkan rekening atau e-wallet, tamu menyalinnya dengan satu ketukan.</span></div>
                    <div><b>Hitung mundur</b><span>Hitungan menuju hari bahagia, tampil langsung di halaman utama.</span></div>
                </div>
            </div>
        </section>

        <section class="lp-sec" id="harga" aria-labelledby="harga-title">
            <div class="lp-w">
                <h2 id="harga-title">Mulai gratis, bayar saat siap terbit.</h2>
                <p class="lp-sub">Coba dulu semua template. Bayar hanya untuk undangan yang benar-benar kamu bagikan.</p>
                <div class="lp-plans">
                    <article class="lp-plan" aria-labelledby="plan-free">
                        <h3 id="plan-free">{{ $plans['free']['name'] }}</h3>
                        <p class="lp-price">Rp 0</p>
                        <p>Undangan sample untuk dicoba.</p>
                        <ul>
                            <li>Isi data dan lihat pratinjau di semua template</li>
                            <li>Belum bisa diterbitkan</li>
                        </ul>
                    </article>
                    <article class="lp-plan lp-plan--paid" aria-labelledby="plan-paid">
                        <h3 id="plan-paid">{{ $plans['paid']['name'] }}</h3>
                        <p class="lp-price">{{ \App\Domain\Billing\Money::rupiah($plans['paid']['price']) }}</p>
                        <p>Sekali bayar untuk satu undangan.</p>
                        <ul>
                            <li>Undangan terbit dan siap dibagikan lewat satu tautan</li>
                            <li>Tanpa masa kedaluwarsa</li>
                        </ul>
                        @unless (config('momenikah.features.payments'))
                            <p class="lp-note">Pembayaran online segera dibuka.</p>
                        @endunless
                    </article>
                </div>
            </div>
        </section>

        <section class="lp-sec lp-brand" aria-labelledby="brand-title">
            <div class="lp-w lp-brand-grid">
                <div>
                    <x-brand.logo />
                    <h2 id="brand-title">Satu link untuk semua momen nikahmu.</h2>
                    <p class="lp-sub">Momen dan nikah, disatukan seperti dua cincin yang saling mengait.</p>
                    <ul class="lp-values">
                        <li>Hangat, bukan formal yang kaku.</li>
                        <li>Jelas: satu tombol, satu tujuan.</li>
                        <li>Romantis tanpa berlebihan.</li>
                    </ul>
                </div>
                <div>
                    <ul class="lp-sw" role="list" aria-label="Warna Momenikah">
                        <li class="lp-sw-rose"><b>Rose</b></li>
                        <li class="lp-sw-rose-light"><b>Rose lembut</b></li>
                        <li class="lp-sw-ink"><b>Tinta</b></li>
                        <li class="lp-sw-ivory"><b>Gading</b></li>
                        <li class="lp-sw-gold"><b>Emas</b></li>
                        <li class="lp-sw-muted"><b>Abu hangat</b></li>
                    </ul>
                    <div class="lp-spec"><p>Cormorant Garamond</p><p>Plus Jakarta Sans untuk teks dan tombol.</p></div>
                </div>
            </div>
        </section>

        <section class="lp-sec lp-final" aria-labelledby="akhir-title">
            <div class="lp-w">
                <h2 id="akhir-title">Mulai dari satu template.</h2>
                <a class="lp-btn" href="{{ route('register') }}">Buat undangan</a>
            </div>
        </section>
    </div>

    <x-slot:footer>Semua data di halaman ini hanya contoh.</x-slot:footer>
</x-layouts.marketing>
