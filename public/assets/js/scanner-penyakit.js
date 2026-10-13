/**
 * Komponen Alpine.js "scannerPenyakit" untuk halaman AI Scanner Penyakit Tanaman.
 * Markup-nya ada di resources/views/components/scanner-penyakit.blade.php.
 *
 * Alurnya:
 *  1. Pengguna memilih foto dari galeri atau memotret langsung lewat kamera
 *     (WebRTC getUserMedia). Kalau browser tidak mendukung kamera langsung,
 *     tersedia tombol untuk membuka kamera bawaan HP (input capture).
 *  2. Foto diperkecil ke sisi terpanjang 1600 px dan disimpan ulang sebagai
 *     JPEG. Ukurannya biasanya tinggal ratusan KB, jadi upload lebih cepat di
 *     sinyal lemah dan tidak mentok batas upload_max_filesize PHP (default 2 MB).
 *     Sebagai bonus, data EXIF seperti lokasi GPS ikut terbuang.
 *  3. Foto dikirim lewat XMLHttpRequest (bukan fetch) supaya progres upload
 *     bisa ditampilkan, lalu animasi pemindaian berjalan sampai server selesai
 *     menganalisis dan mengembalikan URL halaman hasil.
 *
 * File ini harus dimuat SEBELUM script Alpine (yang memakai atribut defer).
 */
document.addEventListener('alpine:init', () => {
    const MAKS_BYTE = 5 * 1024 * 1024;   // samakan dengan ScanPenyakitRequest::MAKS_KB
    const SISI_MAKS = 1600;              // px
    const SISI_MIN = 200;                // px, samakan dengan rule dimensions di server
    const KUALITAS_JPEG = 0.88;
    const TIPE_DITERIMA = ['image/jpeg', 'image/png', 'image/webp'];

    const PESAN_ANALISIS = [
        'AI sedang mengamati daun dan batang…',
        'Mencocokkan pola gejala dengan penyakit tanaman…',
        'Memperkirakan tingkat risiko serangan…',
        'Menyusun rekomendasi penanganan…',
    ];

    Alpine.data('scannerPenyakit', (opsi = {}) => {
        // Disimpan di luar state reaktif Alpine karena bukan data tampilan
        let stream = null;
        let timerPesan = null;
        let timerProgres = null;

        const hentikanTimer = () => {
            clearInterval(timerPesan);
            clearInterval(timerProgres);
            timerPesan = timerProgres = null;
        };

        return {
            mode: 'galeri',             // 'galeri' | 'kamera'
            berkas: null,               // File yang akan dikirim
            pratinjau: null,            // object URL untuk <img>
            infoBerkas: '',
            galat: opsi.galat || '',
            seret: false,
            menyiapkan: false,

            kameraAktif: false,
            memuatKamera: false,
            galatKamera: '',
            arahKamera: 'environment',  // kamera belakang dulu
            jumlahKamera: 0,
            kilat: false,
            dukungKameraLangsung: window.isSecureContext && !!navigator.mediaDevices?.getUserMedia,

            sedangProses: false,
            tahap: '',                  // 'unggah' | 'analisis' | 'selesai'
            progresUnggah: 0,
            progresTampil: 0,
            indeksPesan: 0,

            init() {
                // Jangan biarkan lampu kamera tetap menyala saat tab ditinggal atau halaman ditutup
                this._saatTersembunyi = () => document.hidden && this.matikanKamera();
                this._saatPindahHalaman = () => this.matikanKamera();
                document.addEventListener('visibilitychange', this._saatTersembunyi);
                window.addEventListener('pagehide', this._saatPindahHalaman);
            },

            destroy() {
                this.matikanKamera();
                hentikanTimer();
                document.removeEventListener('visibilitychange', this._saatTersembunyi);
                window.removeEventListener('pagehide', this._saatPindahHalaman);
            },

            get labelTahap() {
                if (this.tahap === 'unggah') return this.progresUnggah > 0 ? `Mengunggah foto… ${this.progresUnggah}%` : 'Mengunggah foto…';
                if (this.tahap === 'analisis') return PESAN_ANALISIS[this.indeksPesan % PESAN_ANALISIS.length];
                if (this.tahap === 'selesai') return 'Analisis selesai!';
                return '';
            },

            get keteranganTahap() {
                if (this.tahap === 'unggah') return 'Jangan tutup halaman ini.';
                if (this.tahap === 'analisis') return 'Biasanya selesai dalam 5-20 detik.';
                if (this.tahap === 'selesai') return 'Membuka hasil diagnosis…';
                return '';
            },

            pilihMode(mode) {
                if (this.sedangProses || this.mode === mode) return;

                this.mode = mode;
                this.galat = '';
                this.hapusBerkas();

                if (mode === 'kamera') {
                    this.nyalakanKamera();
                } else {
                    this.matikanKamera();
                }
            },

            // ---------------------------------------------------------------
            // Kamera (WebRTC)
            // ---------------------------------------------------------------

            async nyalakanKamera() {
                this.galatKamera = '';

                if (!this.dukungKameraLangsung) {
                    this.galatKamera = window.isSecureContext
                        ? 'Browser ini belum mendukung kamera langsung. Pakai kamera bawaan HP atau unggah dari galeri.'
                        : 'Kamera langsung hanya bisa dipakai lewat koneksi HTTPS. Pakai kamera bawaan HP atau unggah dari galeri.';
                    return;
                }

                this.matikanKamera();
                this.memuatKamera = true;

                try {
                    let mediaStream;

                    try {
                        mediaStream = await navigator.mediaDevices.getUserMedia({
                            audio: false,
                            video: {
                                facingMode: { ideal: this.arahKamera },
                                width: { ideal: 1920 },
                                height: { ideal: 1080 },
                            },
                        });
                    } catch (e) {
                        // Sebagian perangkat menolak resolusi yang diminta, coba lagi tanpa syarat
                        if (e.name !== 'OverconstrainedError') throw e;
                        mediaStream = await navigator.mediaDevices.getUserMedia({ audio: false, video: true });
                    }

                    // Pengguna mungkin sudah pindah ke mode galeri selama dialog izin terbuka
                    if (this.mode !== 'kamera' || this.berkas) {
                        mediaStream.getTracks().forEach((track) => track.stop());
                        return;
                    }

                    stream = mediaStream;
                    this.$refs.video.srcObject = stream;
                    await this.$refs.video.play().catch(() => {});
                    this.kameraAktif = true;
                    this.hitungKamera();
                    // Di HP tombol rana bisa berada di bawah layar, gulir sampai terlihat.
                    // Pakai jeda, bukan $nextTick: x-show menampilkan elemen lewat
                    // setTimeout, jadi saat $nextTick jalan tombolnya masih display:none.
                    setTimeout(() => this.$refs.rana?.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 120);
                } catch (e) {
                    this.galatKamera = this.pesanGalatKamera(e);
                } finally {
                    this.memuatKamera = false;
                }
            },

            matikanKamera() {
                if (stream) {
                    stream.getTracks().forEach((track) => track.stop());
                    stream = null;
                }

                if (this.$refs.video) this.$refs.video.srcObject = null;
                this.kameraAktif = false;
            },

            gantiKamera() {
                this.arahKamera = this.arahKamera === 'environment' ? 'user' : 'environment';
                this.nyalakanKamera();
            },

            async hitungKamera() {
                try {
                    const perangkat = await navigator.mediaDevices.enumerateDevices();
                    this.jumlahKamera = perangkat.filter((p) => p.kind === 'videoinput').length;
                } catch {
                    this.jumlahKamera = 0;
                }
            },

            pesanGalatKamera(e) {
                switch (e?.name) {
                    case 'NotAllowedError':
                    case 'SecurityError':
                        return 'Izin kamera ditolak. Izinkan akses kamera di pengaturan browser, atau unggah foto dari galeri.';
                    case 'NotFoundError':
                    case 'OverconstrainedError':
                        return 'Kamera tidak ditemukan di perangkat ini.';
                    case 'NotReadableError':
                    case 'AbortError':
                        return 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi tersebut lalu coba lagi.';
                    default:
                        return 'Kamera tidak bisa dinyalakan. Coba lagi atau unggah foto dari galeri.';
                }
            },

            async jepret() {
                const video = this.$refs.video;
                if (!this.kameraAktif || !video.videoWidth) return;

                // Video ditampilkan dengan object-cover, jadi tepinya terpotong.
                // Ambil hanya area yang terlihat di layar supaya hasil foto
                // sama persis dengan yang dibidik pengguna.
                const { videoWidth: vw, videoHeight: vh } = video;
                const kotak = video.getBoundingClientRect();
                const skalaTampil = Math.max(kotak.width / vw, kotak.height / vh);
                const lebarSumber = Math.min(vw, kotak.width / skalaTampil);
                const tinggiSumber = Math.min(vh, kotak.height / skalaTampil);

                const skala = Math.min(1, SISI_MAKS / Math.max(lebarSumber, tinggiSumber));
                const kanvas = document.createElement('canvas');
                kanvas.width = Math.round(lebarSumber * skala);
                kanvas.height = Math.round(tinggiSumber * skala);
                kanvas.getContext('2d').drawImage(
                    video,
                    (vw - lebarSumber) / 2, (vh - tinggiSumber) / 2, lebarSumber, tinggiSumber,
                    0, 0, kanvas.width, kanvas.height
                );

                this.kilat = true;
                setTimeout(() => (this.kilat = false), 120);

                try {
                    const blob = await keBlob(kanvas);
                    this.matikanKamera();
                    this.pasangBerkas(new File([blob], `kamera-${Date.now()}.jpg`, { type: 'image/jpeg' }));
                } catch {
                    this.galat = 'Gagal mengambil foto dari kamera. Silakan coba lagi.';
                }
            },

            // ---------------------------------------------------------------
            // Foto dari galeri
            // ---------------------------------------------------------------

            terimaDrop(event) {
                this.seret = false;
                if (this.sedangProses) return;
                this.pilihBerkas(event.dataTransfer?.files?.[0]);
            },

            async pilihBerkas(file) {
                this.galat = '';
                if (!file) return;

                if (!file.type.startsWith('image/') && !/\.(jpe?g|png|webp|hei[cf])$/i.test(file.name)) {
                    this.galat = 'File yang dipilih bukan gambar. Pilih foto berformat JPG, PNG, atau WEBP.';
                    this.$refs.input.value = '';
                    return;
                }

                this.menyiapkan = true;

                try {
                    const hasil = await siapkanGambar(file);

                    if (hasil.size > MAKS_BYTE) {
                        throw new Error('Ukuran foto melebihi 5 MB. Pilih foto lain atau kecilkan ukurannya.');
                    }

                    this.pasangBerkas(hasil);
                } catch (e) {
                    this.galat = e.message || 'Foto tidak bisa dibaca. Coba pilih foto lain.';
                    this.$refs.input.value = '';
                } finally {
                    this.menyiapkan = false;
                }
            },

            pasangBerkas(file) {
                this.lepasPratinjau();
                this.berkas = file;
                this.pratinjau = URL.createObjectURL(file);
                this.infoBerkas = formatUkuran(file.size);
            },

            hapusBerkas() {
                this.lepasPratinjau();
                this.berkas = null;
                this.infoBerkas = '';
                if (this.$refs.input) this.$refs.input.value = '';
            },

            lepasPratinjau() {
                if (this.pratinjau) URL.revokeObjectURL(this.pratinjau);
                this.pratinjau = null;
            },

            ulangi() {
                this.galat = '';
                this.hapusBerkas();

                if (this.mode === 'kamera') {
                    this.nyalakanKamera();
                } else {
                    this.$refs.input.click();
                }
            },

            // ---------------------------------------------------------------
            // Kirim ke server + animasi pemindaian
            // ---------------------------------------------------------------

            kirim() {
                if (this.sedangProses || this.menyiapkan) return;

                if (!this.berkas) {
                    this.galat = this.mode === 'kamera'
                        ? 'Ambil foto tanaman terlebih dahulu.'
                        : 'Pilih foto tanaman terlebih dahulu.';
                    return;
                }

                const form = this.$refs.form;
                const data = new FormData(form);   // ikut membawa _token & lahan_id
                data.set('foto_tanaman', this.berkas, this.berkas.name);

                this.galat = '';
                this.sedangProses = true;
                this.tahap = 'unggah';
                this.progresUnggah = 0;
                this.progresTampil = 3;

                const xhr = new XMLHttpRequest();
                xhr.open('POST', form.action);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.timeout = 120000;

                xhr.upload.addEventListener('progress', (e) => {
                    if (!e.lengthComputable) return;
                    this.progresUnggah = Math.round((e.loaded / e.total) * 100);
                    this.progresTampil = 3 + this.progresUnggah * 0.37;   // upload = 0–40% bar
                });
                xhr.upload.addEventListener('load', () => this.mulaiTahapAnalisis());
                xhr.addEventListener('load', () => this.tanganiRespons(xhr));
                xhr.addEventListener('error', () => this.gagal('Koneksi internet terputus saat mengirim foto. Periksa sinyal lalu coba lagi.'));
                xhr.addEventListener('timeout', () => this.gagal('Server terlalu lama merespons. Silakan coba lagi.'));

                xhr.send(data);
            },

            mulaiTahapAnalisis() {
                if (this.tahap !== 'unggah') return;

                this.tahap = 'analisis';
                this.progresUnggah = 100;
                this.progresTampil = Math.max(this.progresTampil, 40);
                this.indeksPesan = 0;

                hentikanTimer();
                timerPesan = setInterval(() => this.indeksPesan++, 2600);
                // Bar terus bergerak pelan mendekati 95% selama menunggu server
                timerProgres = setInterval(() => {
                    this.progresTampil += (95 - this.progresTampil) * 0.05;
                }, 400);
            },

            tanganiRespons(xhr) {
                let json = null;
                try {
                    json = JSON.parse(xhr.responseText);
                } catch {
                    // Respons bukan JSON (mis. halaman error HTML dari server/proxy)
                }

                if (xhr.status >= 200 && xhr.status < 300 && json?.redirect) {
                    hentikanTimer();
                    this.tahap = 'selesai';
                    this.progresTampil = 100;
                    window.location.assign(json.redirect);
                    return;
                }

                const pesanValidasi = json?.errors ? Object.values(json.errors).flat()[0] : null;
                const pesanStatus = {
                    401: 'Sesi login Anda sudah berakhir. Silakan login kembali.',
                    413: 'Ukuran foto terlalu besar untuk diterima server. Gunakan foto yang lebih kecil.',
                    419: 'Halaman sudah terlalu lama dibuka. Muat ulang halaman lalu coba lagi.',
                    429: 'Terlalu banyak percobaan scan. Tunggu sekitar satu menit lalu coba lagi.',
                }[xhr.status];

                this.gagal(
                    pesanValidasi
                    || pesanStatus
                    || (xhr.status >= 500 ? 'Terjadi gangguan di server. Silakan coba lagi beberapa saat lagi.' : json?.message)
                    || 'Analisis gagal. Silakan coba lagi.'
                );
            },

            gagal(pesan) {
                hentikanTimer();
                this.sedangProses = false;
                this.tahap = '';
                this.progresTampil = 0;
                this.galat = pesan;   // foto tetap dipertahankan supaya bisa langsung dicoba lagi
            },
        };
    });

    /**
     * Perkecil foto dan simpan ulang sebagai JPEG. Kalau browser tidak bisa
     * membaca formatnya (mis. HEIC di Chrome), file asli dikirim apa adanya
     * selama formatnya memang diterima server.
     */
    async function siapkanGambar(file) {
        let gambar;

        try {
            gambar = await muatGambar(file);
        } catch {
            if (TIPE_DITERIMA.includes(file.type) && file.size <= MAKS_BYTE) return file;

            throw new Error(TIPE_DITERIMA.includes(file.type)
                ? 'Ukuran foto melebihi 5 MB. Pilih foto lain atau kecilkan ukurannya.'
                : 'Format foto tidak didukung. Gunakan foto JPG, PNG, atau WEBP.');
        }

        try {
            if (Math.min(gambar.lebar, gambar.tinggi) < SISI_MIN) {
                throw new Error(`Resolusi foto terlalu kecil. Gunakan foto minimal ${SISI_MIN} × ${SISI_MIN} piksel.`);
            }

            const skala = Math.min(1, SISI_MAKS / Math.max(gambar.lebar, gambar.tinggi));

            // JPEG yang sudah kecil tidak perlu dikompres ulang
            if (skala === 1 && file.type === 'image/jpeg' && file.size <= 1.5 * 1024 * 1024) return file;

            const kanvas = document.createElement('canvas');
            kanvas.width = Math.round(gambar.lebar * skala);
            kanvas.height = Math.round(gambar.tinggi * skala);

            const ctx = kanvas.getContext('2d');
            ctx.fillStyle = '#ffffff';   // PNG transparan jangan sampai jadi latar hitam
            ctx.fillRect(0, 0, kanvas.width, kanvas.height);
            ctx.drawImage(gambar.sumber, 0, 0, kanvas.width, kanvas.height);

            const blob = await keBlob(kanvas);
            const nama = (file.name || 'foto').replace(/\.[^.]+$/, '') + '.jpg';

            return new File([blob], nama, { type: 'image/jpeg', lastModified: Date.now() });
        } finally {
            gambar.tutup();
        }
    }

    async function muatGambar(file) {
        if ('createImageBitmap' in window) {
            try {
                // from-image = ikuti orientasi EXIF supaya foto HP tidak miring
                const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
                return { sumber: bitmap, lebar: bitmap.width, tinggi: bitmap.height, tutup: () => bitmap.close() };
            } catch {
                // Browser lama belum mendukung opsi ini, lanjut pakai <img>
            }
        }

        const url = URL.createObjectURL(file);

        try {
            const img = new Image();
            img.src = url;
            await img.decode();
            return { sumber: img, lebar: img.naturalWidth, tinggi: img.naturalHeight, tutup: () => URL.revokeObjectURL(url) };
        } catch (e) {
            URL.revokeObjectURL(url);
            throw e;
        }
    }

    function keBlob(kanvas) {
        return new Promise((resolve, reject) => {
            kanvas.toBlob(
                (blob) => (blob ? resolve(blob) : reject(new Error('Gagal memproses foto.'))),
                'image/jpeg',
                KUALITAS_JPEG
            );
        });
    }

    function formatUkuran(byte) {
        return byte < 1024 * 1024
            ? `${Math.max(1, Math.round(byte / 1024))} KB`
            : `${(byte / (1024 * 1024)).toFixed(1)} MB`;
    }
});
