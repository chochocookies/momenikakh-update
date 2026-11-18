<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Styleguide - Momenikah Design System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-text min-h-screen">
    <header class="sticky top-0 z-50 border-b border-border bg-surface/95 backdrop-blur-sm">
        <div class="container-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-primary"></div>
                    <h1 class="text-2xl font-serif font-bold">Momenikah Design System</h1>
                </div>
                <nav class="flex items-center gap-6">
                    <a href="#colors" class="text-sm font-medium hover:text-primary transition-colors">Warna</a>
                    <a href="#typography" class="text-sm font-medium hover:text-primary transition-colors">Tipografi</a>
                    <a href="#templates" class="text-sm font-medium hover:text-primary transition-colors">Template</a>
                    <a href="#spacing" class="text-sm font-medium hover:text-primary transition-colors">Spacing</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container-7xl mx-auto px-4 py-8 space-y-16">
        <!-- Colors Section -->
        <section id="colors" class="space-y-8">
            <h2 class="text-3xl font-serif font-bold border-b border-border pb-4">Warna Brand</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Brand Colors -->
                <div class="space-y-6">
                    <h3 class="text-xl font-serif font-semibold">Palet Brand (Light Mode)</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <div class="w-full h-32 rounded-lg bg-ivory border border-border"></div>
                            <div class="text-sm">
                                <div class="font-medium">Background</div>
                                <code class="text-text-muted">ivory</code>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="w-full h-32 rounded-lg bg-[#2B1B22] border border-border"></div>
                            <div class="text-sm">
                                <div class="font-medium text-text">Ink/Text</div>
                                <code class="text-text-muted">#2B1B22</code>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="w-full h-32 rounded-lg bg-[#BE185D] border border-border"></div>
                            <div class="text-sm">
                                <div class="font-medium text-on-primary">Rose (Primary)</div>
                                <code class="text-text-muted">#BE185D</code>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="w-full h-32 rounded-lg bg-[#6B5A62] border border-border"></div>
                            <div class="text-sm">
                                <div class="font-medium text-text">Muted</div>
                                <code class="text-text-muted">#6B5A62</code>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="w-full h-32 rounded-lg bg-[#FCE7F3] border border-border"></div>
                            <div class="text-sm">
                                <div class="font-medium text-text">Rose Light</div>
                                <code class="text-text-muted">#FCE7F3</code>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="w-full h-32 rounded-lg bg-[#F2D08A] border border-border"></div>
                            <div class="text-sm">
                                <div class="font-medium text-text">Gold</div>
                                <code class="text-text-muted">#F2D08A</code>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dark Mode Colors -->
                <div class="space-y-6 p-6 rounded-xl bg-[#1B1217] text-[#F6E9EE]">
                    <h3 class="text-xl font-serif font-semibold">Palet Brand (Dark Mode)</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <div class="w-full h-32 rounded-lg bg-[#1B1217] border border-[#3B2A32]"></div>
                            <div class="text-sm">
                                <div class="font-medium">Background</div>
                                <code class="text-[#B9A5AE]">#1B1217</code>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="w-full h-32 rounded-lg bg-[#F6E9EE] border border-[#3B2A32]"></div>
                            <div class="text-sm">
                                <div class="font-medium text-[#1B1217]">Ink/Text</div>
                                <code class="text-[#B9A5AE]">#F6E9EE</code>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="w-full h-32 rounded-lg bg-[#F472B6] border border-[#3B2A32]"></div>
                            <div class="text-sm">
                                <div class="font-medium text-[#2B1B22]">Rose (Primary)</div>
                                <code class="text-[#B9A5AE]">#F472B6</code>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="w-full h-32 rounded-lg bg-[#B9A5AE] border border-[#3B2A32]"></div>
                            <div class="text-sm">
                                <div class="font-medium text-[#1B1217]">Muted</div>
                                <code class="text-[#B9A5AE]">#B9A5AE</code>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="w-full h-32 rounded-lg bg-[#3A1F2C] border border-[#3B2A32]"></div>
                            <div class="text-sm">
                                <div class="font-medium text-[#F6E9EE]">Rose Light</div>
                                <code class="text-[#B9A5AE]">#3A1F2C</code>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="w-full h-32 rounded-lg bg-[#B8893B] border border-[#3B2A32]"></div>
                            <div class="text-sm">
                                <div class="font-medium text-[#1B1217]">Gold Alt</div>
                                <code class="text-[#B9A5AE]">#B8893B</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usage Examples -->
            <div class="space-y-4">
                <h3 class="text-xl font-serif font-semibold">Penggunaan Warna</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="p-6 rounded-xl bg-primary text-on-primary">
                        <h4 class="text-lg font-bold mb-2">Primary</h4>
                        <p class="text-sm">Digunakan untuk tombol utama, aksi, dan elemen interaktif utama.</p>
                    </div>
                    <div class="p-6 rounded-xl border border-border bg-surface">
                        <h4 class="text-lg font-bold mb-2">Surface</h4>
                        <p class="text-sm text-text-muted">Latar belakang utama dan kontainer konten.</p>
                    </div>
                    <div class="p-6 rounded-xl bg-primary-light border border-primary/20">
                        <h4 class="text-lg font-bold mb-2">Primary Light</h4>
                        <p class="text-sm text-text">Highlight dan feedback visual yang halus.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Typography Section -->
        <section id="typography" class="space-y-8">
            <h2 class="text-3xl font-serif font-bold border-b border-border pb-4">Tipografi</h2>

            <!-- Font Families -->
            <div class="space-y-6">
                <h3 class="text-xl font-serif font-semibold">Keluarga Font</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <div class="p-6 border border-border rounded-xl">
                            <div class="text-4xl font-serif font-bold mb-4">Cormorant Garamond</div>
                            <p class="text-lg font-serif">Font serif untuk heading, judul, dan elemen dekoratif.</p>
                            <div class="mt-4 text-sm text-text-muted">
                                <div>Weight: 400, 500, 600, 700</div>
                                <div>Style: Normal, Italic</div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="p-6 border border-border rounded-xl">
                            <div class="text-4xl font-sans font-bold mb-4">Plus Jakarta Sans</div>
                            <p class="text-lg font-sans">Font sans-serif untuk body text, UI, dan elemen fungsional.</p>
                            <div class="mt-4 text-sm text-text-muted">
                                <div>Weight: 400, 500, 600, 700, 800</div>
                                <div>Style: Normal</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Type Scale -->
            <div class="space-y-6">
                <h3 class="text-xl font-serif font-semibold">Skala Type</h3>
                <div class="space-y-6">
                    <div class="border border-border rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-border bg-surface/50">
                            <div class="text-lg font-bold">Heading Display</div>
                        </div>
                        <div class="p-6 space-y-8">
                            <div class="space-y-2">
                                <div class="text-7xl font-serif font-bold">H1 Display (72px)</div>
                                <div class="text-sm text-text-muted">font-serif, bold, leading-tight</div>
                            </div>
                            <div class="space-y-2">
                                <div class="text-6xl font-serif font-bold">H1 (60px)</div>
                                <div class="text-sm text-text-muted">font-serif, bold, leading-tight</div>
                            </div>
                            <div class="space-y-2">
                                <div class="text-5xl font-serif font-bold">H2 (48px)</div>
                                <div class="text-sm text-text-muted">font-serif, bold, leading-tight</div>
                            </div>
                            <div class="space-y-2">
                                <div class="text-4xl font-serif font-bold">H3 (36px)</div>
                                <div class="text-sm text-text-muted">font-serif, semibold, leading-tight</div>
                            </div>
                        </div>
                    </div>

                    <div class="border border-border rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-border bg-surface/50">
                            <div class="text-lg font-bold">Body Text</div>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="space-y-2">
                                <div class="text-2xl font-sans">Lead (24px)</div>
                                <div class="text-sm text-text-muted">font-sans, normal, leading-relaxed</div>
                            </div>
                            <div class="space-y-2">
                                <div class="text-xl font-sans">Large Body (20px)</div>
                                <div class="text-sm text-text-muted">font-sans, normal, leading-relaxed</div>
                            </div>
                            <div class="space-y-2">
                                <div class="text-lg font-sans">Body (18px)</div>
                                <div class="text-sm text-text-muted">font-sans, normal, leading-normal</div>
                            </div>
                            <div class="space-y-2">
                                <div class="text-base font-sans">Base (16px)</div>
                                <p class="text-sm text-text-muted">font-sans, normal, leading-normal. Standar untuk paragraf dan konten utama.</p>
                            </div>
                            <div class="space-y-2">
                                <div class="text-sm font-sans">Small (14px)</div>
                                <div class="text-sm text-text-muted">font-sans, normal, leading-normal</div>
                            </div>
                            <div class="space-y-2">
                                <div class="text-xs font-sans">Extra Small (12px)</div>
                                <div class="text-sm text-text-muted">font-sans, normal, leading-normal</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Font Weights -->
            <div class="space-y-6">
                <h3 class="text-xl font-serif font-semibold">Weight Font</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 border border-border rounded-xl">
                        <div class="text-lg font-sans font-normal">Normal (400)</div>
                        <p class="text-sm text-text-muted mt-2">Default untuk body text</p>
                    </div>
                    <div class="p-4 border border-border rounded-xl">
                        <div class="text-lg font-sans font-medium">Medium (500)</div>
                        <p class="text-sm text-text-muted mt-2">Untuk aksen dan sub-heading</p>
                    </div>
                    <div class="p-4 border border-border rounded-xl">
                        <div class="text-lg font-sans font-semibold">Semibold (600)</div>
                        <p class="text-sm text-text-muted mt-2">Untuk heading kecil</p>
                    </div>
                    <div class="p-4 border border-border rounded-xl">
                        <div class="text-lg font-sans font-bold">Bold (700)</div>
                        <p class="text-sm text-text-muted mt-2">Untuk heading utama</p>
                    </div>
                    <div class="p-4 border border-border rounded-xl">
                        <div class="text-lg font-sans font-extrabold">Extrabold (800)</div>
                        <p class="text-sm text-text-muted mt-2">Untuk display text</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Templates Section -->
        <section id="templates" class="space-y-8">
            <h2 class="text-3xl font-serif font-bold border-b border-border pb-4">Template Colors</h2>
            <p class="text-lg text-text-muted">Warna untuk enam template undangan yang berbeda.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Classic Template -->
                <div class="border border-border rounded-xl overflow-hidden">
                    <div class="h-32 bg-[#FBF5EC]"></div>
                    <div class="p-6 space-y-3">
                        <h4 class="text-lg font-serif font-bold text-[#4A1F2B]">Classic Elegant</h4>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-[#FBF5EC] border border-border"></span>
                            <code>bg: #FBF5EC</code>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-[#4A1F2B]"></span>
                            <code>text: #4A1F2B</code>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-[#7B1E3A]"></span>
                            <code>accent: #7B1E3A</code>
                        </div>
                    </div>
                </div>

                <!-- Beach Template -->
                <div class="border border-border rounded-xl overflow-hidden">
                    <div class="h-32 bg-gradient-to-br from-[#FFE6CF] via-[#FFC9A8] to-[#9FDAD6]"></div>
                    <div class="p-6 space-y-3">
                        <h4 class="text-lg font-serif font-bold text-[#5A2E2A]">Beach Sunset</h4>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-gradient-to-r from-[#FFE6CF] to-[#9FDAD6]"></span>
                            <code>bg: gradient</code>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-[#5A2E2A]"></span>
                            <code>text: #5A2E2A</code>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-[#B83A28]"></span>
                            <code>accent: #B83A28</code>
                        </div>
                    </div>
                </div>

                <!-- Floral Template -->
                <div class="border border-border rounded-xl overflow-hidden">
                    <div class="h-32 bg-gradient-to-br from-[#FFF3F5] to-[#EEF4E8]"></div>
                    <div class="p-6 space-y-3">
                        <h4 class="text-lg font-serif font-bold text-[#44503D]">Floral Garden</h4>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-gradient-to-r from-[#FFF3F5] to-[#EEF4E8]"></span>
                            <code>bg: gradient</code>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-[#44503D]"></span>
                            <code>text: #44503D</code>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-[#A8456A]"></span>
                            <code>accent: #A8456A</code>
                        </div>
                    </div>
                </div>

                <!-- Luxury Template -->
                <div class="border border-border rounded-xl overflow-hidden">
                    <div class="h-32 bg-[#15110D]"></div>
                    <div class="p-6 space-y-3">
                        <h4 class="text-lg font-serif font-bold text-[#F1E3C0]">Luxury Gold</h4>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-[#15110D]"></span>
                            <code>bg: #15110D</code>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-[#F1E3C0]"></span>
                            <code>text: #F1E3C0</code>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-[#D4AF37]"></span>
                            <code>accent: #D4AF37</code>
                        </div>
                    </div>
                </div>

                <!-- Minimal Template -->
                <div class="border border-border rounded-xl overflow-hidden">
                    <div class="h-32 bg-white border-b border-border"></div>
                    <div class="p-6 space-y-3">
                        <h4 class="text-lg font-serif font-bold text-[#111111]">Modern Minimal</h4>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-white border border-border"></span>
                            <code>bg: #ffffff</code>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-[#111111]"></span>
                            <code>text: #111111</code>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-[#BE185D]"></span>
                            <code>accent: #BE185D</code>
                        </div>
                    </div>
                </div>

                <!-- Rustic Template -->
                <div class="border border-border rounded-xl overflow-hidden">
                    <div class="h-32 bg-[#E9D8BC]"></div>
                    <div class="p-6 space-y-3">
                        <h4 class="text-lg font-serif font-bold text-[#4B3621]">Rustic Vintage</h4>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-[#E9D8BC]"></span>
                            <code>bg: #E9D8BC</code>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-[#4B3621]"></span>
                            <code>text: #4B3621</code>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-4 h-4 rounded-full bg-[#8A4B2A]"></span>
                            <code>accent: #8A4B2A</code>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Spacing Section -->
        <section id="spacing" class="space-y-8">
            <h2 class="text-3xl font-serif font-bold border-b border-border pb-4">Spacing Scale</h2>

            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Spacing Tokens -->
                    <div class="space-y-4">
                        <h3 class="text-xl font-serif font-semibold">Token Spacing</h3>
                        <div class="border border-border rounded-xl overflow-hidden">
                            <div class="px-6 py-4 border-b border-border bg-surface/50">
                                <div class="font-medium">Tingkat Spacing</div>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex items-center justify-between">
                                    <code class="text-sm">spacing-1</code>
                                    <div class="w-8 h-8 bg-primary/20 rounded flex items-center justify-center text-xs">4px</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <code class="text-sm">spacing-2</code>
                                    <div class="w-16 h-8 bg-primary/20 rounded flex items-center justify-center text-xs">8px</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <code class="text-sm">spacing-3</code>
                                    <div class="w-24 h-8 bg-primary/20 rounded flex items-center justify-center text-xs">12px</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <code class="text-sm">spacing-4</code>
                                    <div class="w-32 h-8 bg-primary/20 rounded flex items-center justify-center text-xs">16px</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <code class="text-sm">spacing-6</code>
                                    <div class="w-48 h-8 bg-primary/20 rounded flex items-center justify-center text-xs">24px</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <code class="text-sm">spacing-8</code>
                                    <div class="w-64 h-8 bg-primary/20 rounded flex items-center justify-center text-xs">32px</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <code class="text-sm">spacing-12</code>
                                    <div class="w-96 h-8 bg-primary/20 rounded flex items-center justify-center text-xs">48px</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Radius Tokens -->
                    <div class="space-y-4">
                        <h3 class="text-xl font-serif font-semibold">Border Radius</h3>
                        <div class="border border-border rounded-xl overflow-hidden">
                            <div class="px-6 py-4 border-b border-border bg-surface/50">
                                <div class="font-medium">Tingkat Radius</div>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex items-center justify-between">
                                    <code class="text-sm">radius-xs</code>
                                    <div class="w-16 h-16 bg-primary/10 rounded-xs border border-border"></div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <code class="text-sm">radius-sm</code>
                                    <div class="w-16 h-16 bg-primary/10 rounded-sm border border-border"></div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <code class="text-sm">radius-md</code>
                                    <div class="w-16 h-16 bg-primary/10 rounded-md border border-border"></div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <code class="text-sm">radius-lg</code>
                                    <div class="w-16 h-16 bg-primary/10 rounded-lg border border-border"></div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <code class="text-sm">radius-xl</code>
                                    <div class="w-16 h-16 bg-primary/10 rounded-xl border border-border"></div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <code class="text-sm">radius-full</code>
                                    <div class="w-16 h-16 bg-primary/10 rounded-full border border-border"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-border mt-16">
        <div class="container-7xl mx-auto px-4 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-text-muted">Momenikah Design System v1.0</div>
                    <div class="text-xs text-text-muted mt-1">Dibangun dengan Tailwind CSS 4</div>
                </div>
                <div class="text-sm text-text-muted">
                    &copy; {{ date('Y') }} Momenikah. All rights reserved.
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
