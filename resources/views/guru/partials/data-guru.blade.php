{{--
    Data layer bersama untuk seluruh halaman Guru.

    Hanya berisi pengelolaan state + localStorage. Logic render per halaman
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
    const STORAGE_KEY_IDENTITAS = 'identitas_guru';

    // Data default (dipakai kalau belum ada di localStorage)
    const defaultSiswaPerKelas = {
        'XII.RPL': [
            { nama: 'Najla Mutia', nis: '12345' },
            { nama: 'Yasmin Zahra', nis: '12346' },
            { nama: 'Dina Karima', nis: '12347' },
            { nama: 'Alex Pratama', nis: '12348' },
            { nama: 'Arjuna Wijaya', nis: '12349' }
        ],
        'XI.RPL': [
            { nama: 'Budi Santoso', nis: '22345' },
            { nama: 'Siti Rahayu', nis: '22346' },
            { nama: 'Ahmad Fauzi', nis: '22347' }
        ],
        'XII.TKJ': [
            { nama: 'Rizky Ramadhan', nis: '32345' },
            { nama: 'Maya Sari', nis: '32346' }
        ],
        'X.RPL': [
            { nama: 'Hana Permata', nis: '42345' },
            { nama: 'Gilang Pratama', nis: '42346' }
        ]
    };

    // Identitas hanya dipakai untuk mengisi nama & kelas di tampilan.
    // Kalau guru belum pernah mengisi form identitas, pakai nilai bawaan
    // supaya halaman dashboard tetap bisa langsung dibuka.
    const DEFAULT_IDENTITAS = {
        nama: 'Guru',
        kelasLengkap: 'XII.RPL',
        jurusan: '-',
        kepentingan: '-'
    };

    // ============================================================
    // STATE
    // ============================================================
    let identitasGuru = null;
    let presensiList = [];
    let nextId = 1;
    let globalData = loadGlobalData();

    // ============================================================
    // LOAD / SAVE DATA GLOBAL
    // ============================================================
    function loadGlobalData() {
        const saved = localStorage.getItem(GLOBAL_DATA_KEY);

        if (saved) {
            try {
                return JSON.parse(saved);
            } catch (e) {
                console.error('Error parsing global data:', e);
            }
        }

        // Kalau belum ada, simpan default
        const defaultData = {
            siswaPerKelas: defaultSiswaPerKelas,
            lastUpdate: new Date().toISOString()
        };
        localStorage.setItem(GLOBAL_DATA_KEY, JSON.stringify(defaultData));

        return defaultData;
    }

    function saveGlobalData(data) {
        data.lastUpdate = new Date().toISOString();
        localStorage.setItem(GLOBAL_DATA_KEY, JSON.stringify(data));
    }

    // ============================================================
    // IDENTITAS GURU
    // ============================================================
    function cekIdentitasGuru() {
        const saved = localStorage.getItem(STORAGE_KEY_IDENTITAS);

        // Belum pernah mengisi identitas -> pakai nilai bawaan, jangan diarahkan.
        if (!saved) {
            identitasGuru = { ...DEFAULT_IDENTITAS };
        } else {
            try {
                identitasGuru = { ...DEFAULT_IDENTITAS, ...JSON.parse(saved) };
            } catch (e) {
                console.error('Error:', e);
                identitasGuru = { ...DEFAULT_IDENTITAS };
            }
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
    function loadDataKelas(kelas) {
        presensiList = [];
        nextId = 1;

        const students = globalData.siswaPerKelas[kelas] || globalData.siswaPerKelas['XII.RPL'] || [];
        const statuses = ['Hadir', 'Hadir', 'Hadir', 'Izin', 'Sakit'];
        const metodes = ['Scan QR', 'ID Unik', 'Izin/Sakit'];
        const now = new Date();

        for (let i = 0; i < students.length; i++) {
            const time = new Date(now);
            time.setMinutes(now.getMinutes() - (students.length - 1 - i) * 5);

            presensiList.push({
                id: nextId++,
                nama: students[i].nama,
                nis: students[i].nis,
                kelas: kelas,
                waktu: time,
                metode: metodes[i % metodes.length],
                status: statuses[i % statuses.length]
            });
        }

        return presensiList;
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

    function hitungStatAbsensi(list) {
        const stat = { hadir: 0, izin: 0, sakit: 0, alpha: 0 };

        list.forEach((d) => {
            if (d.status === 'Hadir') stat.hadir++;
            else if (d.status === 'Izin') stat.izin++;
            else if (d.status === 'Sakit') stat.sakit++;
            else stat.alpha++;
        });

        return stat;
    }

    // ============================================================
    // AKUN
    // ============================================================
    function logout() {
        if (confirm('Yakin ingin logout?')) {
            window.location.href = '{{ route('login') }}';
        }
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
