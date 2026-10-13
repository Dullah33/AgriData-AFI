/**
 * Perilaku kerangka dashboard (sidebar + menu profil).
 *
 * - Desktop (>= 1024px): sidebar selalu tampil.
 * - HP/tablet: sidebar jadi panel geser dari kiri. Bisa ditutup lewat
 *   tombol X, klik latar gelap, tombol Esc, atau usap ke kiri. Selama
 *   terbuka, halaman di belakangnya tidak ikut ter-scroll.
 * - Saat tertutup di HP, sidebar diberi atribut inert supaya tidak bisa
 *   difokus lewat Tab dan tidak dibaca screen reader.
 */
(function () {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const tombolBuka = document.querySelector('[data-sidebar-open]');
    const desktop = window.matchMedia('(min-width: 1024px)');

    if (sidebar && backdrop && tombolBuka) {
        let terbuka = false;
        let fokusSebelumnya = null;

        const terapkan = () => {
            const tampil = desktop.matches || terbuka;

            sidebar.classList.toggle('-translate-x-full', !tampil);
            sidebar.inert = !tampil;
            backdrop.classList.toggle('opacity-0', !terbuka);
            backdrop.classList.toggle('pointer-events-none', !terbuka);
            document.documentElement.classList.toggle('overflow-hidden', terbuka && !desktop.matches);
            tombolBuka.setAttribute('aria-expanded', String(terbuka));

            if (terbuka && !desktop.matches) {
                sidebar.setAttribute('role', 'dialog');
                sidebar.setAttribute('aria-modal', 'true');
            } else {
                sidebar.removeAttribute('role');
                sidebar.removeAttribute('aria-modal');
            }
        };

        const buka = () => {
            fokusSebelumnya = document.activeElement;
            terbuka = true;
            terapkan();
            // Fokus ke item menu aktif (atau tombol tutup) setelah panel mulai bergeser
            const target = sidebar.querySelector('[aria-current="page"]') || sidebar.querySelector('[data-sidebar-close]');
            setTimeout(() => target && target.focus({ preventScroll: true }), 50);
        };

        const tutup = () => {
            if (!terbuka) return;
            terbuka = false;
            terapkan();
            if (fokusSebelumnya && fokusSebelumnya.focus) fokusSebelumnya.focus();
        };

        tombolBuka.addEventListener('click', buka);
        document.querySelectorAll('[data-sidebar-close]').forEach((el) => el.addEventListener('click', tutup));
        document.addEventListener('keydown', (e) => e.key === 'Escape' && tutup());
        desktop.addEventListener('change', () => {
            terbuka = false;
            terapkan();
        });

        // Usap ke kiri untuk menutup
        let awalX = null;
        sidebar.addEventListener('touchstart', (e) => (awalX = e.touches[0].clientX), { passive: true });
        sidebar.addEventListener('touchend', (e) => {
            if (awalX !== null && e.changedTouches[0].clientX - awalX < -60) tutup();
            awalX = null;
        });

        terapkan();
    }

    // Menu dropdown berbasis <details data-menu>: tutup saat klik di luar atau Esc
    const menus = document.querySelectorAll('details[data-menu]');
    document.addEventListener('click', (e) => {
        menus.forEach((menu) => menu.open && !menu.contains(e.target) && (menu.open = false));
    });
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        menus.forEach((menu) => {
            if (!menu.open) return;
            menu.open = false;
            menu.querySelector('summary').focus();
        });
    });
})();
