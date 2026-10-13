/**
 * Design tokens AgriScan untuk Tailwind Play CDN (v3).
 * Dimuat tepat setelah <script src="https://cdn.tailwindcss.com"> di layout
 * dashboard, landing page, dan halaman login/daftar.
 *
 * Warna:
 * - brand  : hijau hutan (forest) yang agak redup, satu-satunya warna aksen.
 * - green & teal dipetakan ke brand, supaya halaman lama yang masih memakai
 *   bg-green-* atau bg-teal-* otomatis ikut palet yang sama.
 * - blue & sky dipetakan ke dusty (biru keabuan) untuk status informasi
 *   dan aksi sekunder. Aksi utama selalu memakai brand.
 * - gray dipetakan ke stone (abu-abu hangat), supaya semua netral di
 *   aplikasi senada dengan nuansa tanah.
 * - primary dipertahankan sebagai alias brand untuk halaman login/daftar.
 *
 * Aturan bentuk: kontrol interaktif (tombol, input, item menu) rounded-lg,
 * wadah (kartu, panel, gambar) rounded-2xl, wadah besar di landing page
 * (foto hero, panel CTA) rounded-3xl, badge dan avatar rounded-full.
 */
(function () {
    const forest = {
        50: '#f3f8f4',
        100: '#e3efe6',
        200: '#c7dfcf',
        300: '#9dc6ab',
        400: '#6ca682',
        500: '#4a8a63',
        600: '#376f4e',
        700: '#2d5940',
        800: '#264835',
        900: '#203c2d',
        950: '#0f2219',
    };

    // Biru kusam untuk status informasi (mis. "Ditinjau", "Diproses") dan
    // aksi sekunder. Sengaja tidak memakai biru bawaan yang terlalu terang.
    const dusty = {
        50: '#f3f6f9',
        100: '#e3eaf1',
        200: '#c8d6e3',
        300: '#a1b9ce',
        400: '#7596b3',
        500: '#567a99',
        600: '#44627e',
        700: '#394f66',
        800: '#324355',
        900: '#2c3947',
        950: '#1c242e',
    };

    const stone = {
        50: '#fafaf9',
        100: '#f5f5f4',
        200: '#e7e5e4',
        300: '#d6d3d1',
        400: '#a8a29e',
        500: '#78716c',
        600: '#57534e',
        700: '#44403c',
        800: '#292524',
        900: '#1c1917',
        950: '#0c0a09',
    };

    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: forest,
                    primary: forest,
                    green: forest,
                    teal: forest,
                    blue: dusty,
                    sky: dusty,
                    gray: stone,
                },
                fontFamily: {
                    sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                },
                boxShadow: {
                    // Bayangan diberi rona hijau gelap, bukan hitam murni
                    card: '0 1px 2px rgb(15 34 25 / 0.04), 0 4px 16px -6px rgb(15 34 25 / 0.08)',
                    lift: '0 2px 4px rgb(15 34 25 / 0.05), 0 14px 32px -10px rgb(15 34 25 / 0.18)',
                },
                keyframes: {
                    'fade-up': {
                        from: { opacity: '0', transform: 'translateY(12px)' },
                        to: { opacity: '1', transform: 'translateY(0)' },
                    },
                },
                animation: {
                    'fade-up': 'fade-up .7s cubic-bezier(.16, 1, .3, 1) both',
                },
            },
        },
    };
})();
