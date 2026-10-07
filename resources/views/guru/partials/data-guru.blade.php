{{--
    Data layer bersama untuk seluruh halaman Guru.

    Sumber data hanya satu: **Server (database)**, yang dipasang lewat
    `window.HADIRIN_GURU` oleh `DashboardGuruController`. Ini sumber yang sama
    dengan dashboard admin, jadi kelas, siswa, dan mata pelajaran yang baru
    ditambah dari dashboard CMS langsung muncul di sini tanpa langkah
    tambahan.

    Sebelumnya file ini masih punya data contoh bawaan (`XII.RPL`, `XI.RPL`,
    dst.) yang dipakai kalau server tidak mengirim kelas. Itu membuat dashboard
    guru menunjukkan mata pelajaran dan kelas milik guru lain — misalnya
    Martha Arinda (Desain Komunikasi Visual) terlihat mengajar `XII.RPL`.
    Data contoh tersebut sudah dihapus: kalau guru memang belum diampu kelas,
    halamannya menampilkan kondisi kosong, bukan kelas palsu.

    File ini hanya menangani state + pemuatan data. Logic render per halaman
    tetap ada di file halaman masing-masing. Dipakai lewat:
        @push('scripts')
            @include('guru.partials.data-guru')
        @endpush
--}}
<script>
    // ============================================================
    // KUNCI STORAGE
    // ============================================================
    const GLOBAL_DATA_KEY = 'hadirin_global_data';
    const STORAGE_KEY_SISWA_ABSEN = 'siswa_absen';
    const STORAGE_KEY_DAFTAR_ABSEN = 'daftar_absen';

    // Data dari server, dipasang oleh halaman sebelum include ini.
    const SERVER_DATA = window.HADIRIN_GURU ?? null;

    // Identitas guru. Semua isinya berasal dari database; tidak ada lagi nilai
    // bawaan yang mengarang nama kelas atau mata pelajaran.
    const IDENTITAS_KOSONG = {
        nama: 'Guru',
        nip: '-',
        mapel: '-',
        kodeMapel: '-',
        kelasLengkap: '-'
    };

    // ============================================================
    // STATE
    // ============================================================
    let identitasGuru = null;
    let presensiList = [];
    let nextId = 1;
    let globalData = sourceDataAwal();

    // ============================================================
    // PILIH SUMBER DATA
    // ============================================================

    /**
     * Data awal untuk halaman.
     *
     * Kalau server tidak mengirim kelas, `siswaPerKelas` dibiarkan kosong.
     * Halaman-halaman guru sudah punya kondisi "belum ada kelas yang diampu"
     * untuk kasus itu, jadi tidak perlu data contoh apa pun.
     */
    function sourceDataAwal() {
        const data = {
            siswaPerKelas: {},
            lastUpdate: new Date().toISOString(),
            sumber: 'database'
        };

        if (!SERVER_DATA || !Array.isArray(SERVER_DATA.kelas)) {
            return data;
        }

        SERVER_DATA.kelas.forEach(function (kelas) {
            data.siswaPerKelas[kelas.nama] = (kelas.siswa || []).map(function (siswa) {
                return { nama: siswa.nama, nis: siswa.nis };
            });
        });

        return data;
    }

    /**
     * Nama kelas yang tersedia untuk guru ini, langsung dari server.
     */
    function daftarKelasServer() {
        if (!SERVER_DATA || !Array.isArray(SERVER_DATA.kelas)) return [];

        return SERVER_DATA.kelas.map(function (kelas) {
            return kelas.nama;
        });
    }

    /**
     * Data satu kelas dari server, atau null kalau kelas itu tidak ada.
     */
    function dataKelasServer(kelas) {
        if (!SERVER_DATA || !Array.isArray(SERVER_DATA.kelas)) return null;

        const found = SERVER_DATA.kelas.find(function (item) {
            return item.nama === kelas;
        });

        return found || null;
    }

    // ============================================================
    // IDENTITAS GURU
    // ============================================================
    function cekIdentitasGuru() {
        if (SERVER_DATA && SERVER_DATA.guru) {
            const kelasPertama = (SERVER_DATA.guru.kelas || '').split(',')[0].trim();

            identitasGuru = {
                nama: SERVER_DATA.guru.nama || IDENTITAS_KOSONG.nama,
                nip: SERVER_DATA.guru.nip || IDENTITAS_KOSONG.nip,
                mapel: SERVER_DATA.guru.mapel || IDENTITAS_KOSONG.mapel,
                kodeMapel: SERVER_DATA.guru.kodeMapel || IDENTITAS_KOSONG.kodeMapel,
                // Guru yang belum diampu kelas tampil sebagai "-", bukan kelas
                // hardcode. Kondisinya sudah ditangani tiap halaman.
                kelasLengkap: kelasPertama || IDENTITAS_KOSONG.kelasLengkap
            };
        } else {
            identitasGuru = { ...IDENTITAS_KOSONG };
        }

        isiIdentitasKeHalaman(identitasGuru);

        return identitasGuru;
    }

    /**
     * Tulis identitas guru ke seluruh elemen yang ditandai, supaya sidebar,
     * header, dan banner di setiap halaman konsisten tanpa harus cek id satu-satu.
     */
    function isiIdentitasKeHalaman(data) {
        const inisial = (data.nama || 'G').charAt(0).toUpperCase();

        setText('[data-guru-inisial]', inisial);
        setText('#sidebarInitial', inisial);
        setText('#headerInitial', inisial);
        setText('#sidebarUserName', data.nama);
        setText('#sidebarUserMeta', data.kelasLengkap);
        setText('[data-guru-nama]', data.nama);
        setText('[data-guru-kelas]', data.kelasLengkap);
    }

    function setText(selector, value) {
        document.querySelectorAll(selector).forEach((el) => {
            el.textContent = value;
        });
    }

    // ============================================================
    // LOAD DATA KELAS
    // ============================================================

    /**
     * Susun `presensiList` untuk satu kelas.
     *
     * Semua baris berasal dari database. Siswa yang belum punya catatan absensi
     * hari ini tetap ikut masuk dengan status null supaya tidak salah
     * dihitung sebagai alpha. Kelas yang tidak ada di database guru ini
     * menghasilkan daftar kosong, bukan data contoh.
     */
    function loadDataKelas(kelas) {
        presensiList = [];
        nextId = 1;

        const server = dataKelasServer(kelas);

        if (!server) return presensiList;

        (server.siswa || []).forEach(function (siswa) {
            presensiList.push({
                id: nextId++,
                nama: siswa.nama,
                nis: siswa.nis,
                kelas: kelas,
                waktu: siswa.waktu ? new Date(siswa.waktu) : null,
                metode: siswa.metode,
                status: toLabelStatus(siswa.absensi)
            });
        });

        return presensiList;
    }

    /**
     * Status dari database tersimpan huruf kecil (`hadir`, `izin`, ...),
     * sedangkan tampilan memakai huruf besar. Students yang belum punya
     * catatan absensi bernilai null dan dibiarkan null.
     */
    function toLabelStatus(status) {
        if (!status) return null;

        const kapital = String(status).charAt(0).toUpperCase() + String(status).slice(1);

        return ['Hadir', 'Izin', 'Sakit', 'Alpha'].indexOf(kapital) === -1 ? null : kapital;
    }

    // ============================================================
    // ABSENSI SISWA (dari halaman absen siswa, via localStorage)
    // ============================================================
    function loadAllAbsensiSiswa() {
        let semua = [];

        // Sumber utama: daftar_absen
        try {
            const daftar = JSON.parse(localStorage.getItem(STORAGE_KEY_DAFTAR_ABSEN) || '[]');
            if (Array.isArray(daftar)) semua = daftar;
        } catch (e) {
            console.warn('Gagal parse daftar_absen:', e);
        }

        // Fallback: siswa_absen (kalau belum ada di daftar)
        try {
            const terakhir = JSON.parse(localStorage.getItem(STORAGE_KEY_SISWA_ABSEN) || 'null');
            if (terakhir && terakhir.timestamp) {
                const sudahAda = semua.some((d) => d.nama === terakhir.nama && d.timestamp === terakhir.timestamp);
                if (!sudahAda) semua.push(terakhir);
            }
        } catch (e) {
            console.warn('Gagal parse siswa_absen:', e);
        }

        // Urutkan terbaru di atas
        semua.sort((a, b) => (b.timestamp || 0) - (a.timestamp || 0));

        return semua;
    }

    /**
     * Rekap jumlah per status.
     *
     * `belum` dipakai untuk siswa yang belum absen hari ini, supaya tidak
     * salah terhitung sebagai alpha.
     */
    function hitungStatAbsensi(list) {
        const stat = { hadir: 0, izin: 0, sakit: 0, alpha: 0, belum: 0 };

        list.forEach(function (d) {
            if (d.status === 'Hadir') stat.hadir++;
            else if (d.status === 'Izin') stat.izin++;
            else if (d.status === 'Sakit') stat.sakit++;
            else if (d.status === 'Alpha') stat.alpha++;
            else stat.belum++;
        });

        return stat;
    }

    // ============================================================
    // AKUN
    // ============================================================
    function logout() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('logout') }}';

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        document.body.appendChild(form);
        form.submit();
    }

    /**
     * Poin masuk untuk setiap halaman Guru: pastikan identitas sudah ada,
     * lalu muat data kelas ke `presensiList`.
     */
    function initHalamanGuru(render) {
        document.addEventListener('DOMContentLoaded', function () {
            cekIdentitasGuru();
            loadDataKelas(identitasGuru.kelasLengkap);

            if (typeof render === 'function') render();
        });
    }
</script>
