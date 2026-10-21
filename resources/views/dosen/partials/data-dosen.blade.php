{{--
    Data layer bersama untuk seluruh halaman Dosen.

    Sumber data hanya satu: **Server (database)**, yang dipasang lewat
    `window.HADIRIN_DOSEN` oleh `DashboardDosenController`. Ini sumber yang sama
    dengan dashboard admin, jadi kelas, mahasiswa, dan mata kuliah yang baru
    ditambah dari dashboard CMS langsung muncul di sini tanpa langkah
    tambahan.

    Sebelumnya file ini masih punya data contoh bawaan (`XII.RPL`, `XI.RPL`,
    dst.) yang dipakai kalau server tidak mengirim kelas. Itu membuat dashboard
    dosen menunjukkan mata kuliah dan kelas milik dosen lain — misalnya
    Martha Arinda (Desain Komunikasi Visual) terlihat mengajar `XII.RPL`.
    Data contoh tersebut sudah dihapus: kalau dosen memang belum diampu kelas,
    halamannya menampilkan kondisi kosong, bukan kelas palsu.

    File ini hanya menangani state + pemuatan data. Logic render per halaman
    tetap ada di file halaman masing-masing. Dipakai lewat:
        @push('scripts')
            @include('dosen.partials.data-dosen')
        @endpush
--}}
<script>
    // ============================================================
    // KUNCI STORAGE
    // ============================================================
    const GLOBAL_DATA_KEY = 'hadirin_global_data';
    const STORAGE_KEY_SISWA_ABSEN = 'mahasiswa_absen';
    const STORAGE_KEY_DAFTAR_ABSEN = 'daftar_absen';

    // Data dari server, dipasang oleh halaman sebelum include ini.
    const SERVER_DATA = window.HADIRIN_DOSEN ?? null;

    // Identitas dosen. Semua isinya berasal dari database; tidak ada lagi nilai
    // bawaan yang mengarang nama kelas atau mata kuliah.
    const IDENTITAS_KOSONG = {
        nama: 'Dosen',
        nidn: '-',
        mataKuliah: '-',
        kodeMataKuliah: '-',
        kelasLengkap: '-'
    };

    // ============================================================
    // STATE
    // ============================================================
    let identitasDosen = null;
    let presensiList = [];
    let nextId = 1;
    let globalData = sourceDataAwal();

    // ============================================================
    // PILIH SUMBER DATA
    // ============================================================

    /**
     * Data awal untuk halaman.
     *
     * Kalau server tidak mengirim kelas, `mahasiswaPerKelas` dibiarkan kosong.
     * Halaman-halaman dosen sudah punya kondisi "belum ada kelas yang diampu"
     * untuk kasus itu, jadi tidak perlu data contoh apa pun.
     */
    function sourceDataAwal() {
        const data = {
            mahasiswaPerKelas: {},
            lastUpdate: new Date().toISOString(),
            sumber: 'database'
        };

        if (!SERVER_DATA || !Array.isArray(SERVER_DATA.kelas)) {
            return data;
        }

        SERVER_DATA.kelas.forEach(function (kelas) {
            data.mahasiswaPerKelas[kelas.nama] = (kelas.mahasiswa || []).map(function (mahasiswa) {
                return { nama: mahasiswa.nama, nim: mahasiswa.nim };
            });
        });

        return data;
    }

    /**
     * Nama kelas yang tersedia untuk dosen ini, langsung dari server.
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
    function cekIdentitasDosen() {
        if (SERVER_DATA && SERVER_DATA.dosen) {
            const kelasPertama = (SERVER_DATA.dosen.kelas || '').split(',')[0].trim();

            identitasDosen = {
                nama: SERVER_DATA.dosen.nama || IDENTITAS_KOSONG.nama,
                nidn: SERVER_DATA.dosen.nidn || IDENTITAS_KOSONG.nidn,
                mataKuliah: SERVER_DATA.dosen.mataKuliah || IDENTITAS_KOSONG.mataKuliah,
                kodeMataKuliah: SERVER_DATA.dosen.kodeMataKuliah || IDENTITAS_KOSONG.kodeMataKuliah,
                // Dosen yang belum diampu kelas tampil sebagai "-", bukan kelas
                // hardcode. Kondisinya sudah ditangani tiap halaman.
                kelasLengkap: kelasPertama || IDENTITAS_KOSONG.kelasLengkap
            };
        } else {
            identitasDosen = { ...IDENTITAS_KOSONG };
        }

        isiIdentitasKeHalaman(identitasDosen);

        return identitasDosen;
    }

    /**
     * Tulis identitas dosen ke seluruh elemen yang ditandai, supaya sidebar,
     * header, dan banner di setiap halaman konsisten tanpa harus cek id satu-satu.
     */
    function isiIdentitasKeHalaman(data) {
        const inisial = (data.nama || 'G').charAt(0).toUpperCase();

        setText('[data-dosen-inisial]', inisial);
        setText('#sidebarInitial', inisial);
        setText('#headerInitial', inisial);
        setText('#sidebarUserName', data.nama);
        setText('#sidebarUserMeta', data.kelasLengkap);
        setText('[data-dosen-nama]', data.nama);
        setText('[data-dosen-kelas]', data.kelasLengkap);
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
     * Presensi dihitung per mata kuliah: kalau server mengirim `sesi` (daftar
     * sesi hari ini), satu baris dibuat untuk tiap pasangan mahasiswa × sesi,
     * jadi satu mahasiswa bisa muncul beberapa kali — sekali per jam pelajaran.
     * Status tiap baris diambil dari catatan pada sesi itu saja, bukan dari
     * absensi terakhir hari ini, supaya kehadiran di mata kuliah pagi tidak
     * menutupi ketidakhadiran di mata kuliah siang.
     *
     * Kalau kelas itu tidak punya jadwal hari ini, daftar jatuh ke bentuk lama:
     * satu baris per mahasiswa dengan status terakhir hari ini, jadi tampilan
     * tetap masuk akal walau jadwal mengajarnya belum diisi.
     */
    function loadDataKelas(kelas) {
        presensiList = [];
        nextId = 1;

        const server = dataKelasServer(kelas);

        if (!server) return presensiList;

        const daftarSesi = Array.isArray(server.sesi) ? server.sesi : [];

        if (daftarSesi.length > 0) {
            daftarSesi.forEach(function (sesi) {
                const catatan = sesi.absensi || {};

                (server.mahasiswa || []).forEach(function (mahasiswa) {
                    const absen = catatan[mahasiswa.id] || null;

                    presensiList.push({
                        id: nextId++,
                        nama: mahasiswa.nama,
                        nim: mahasiswa.nim,
                        kelas: kelas,
                        waktu: absen && absen.waktu ? new Date(absen.waktu) : null,
                        metode: absen ? (absen.metode || 'Scan QR') : null,
                        status: toLabelStatus(absen ? absen.status : null),
                        // Konteks sesi, dipakai filter mata kuliah di dashboard.
                        sesiId: sesi.id,
                        mataKuliah: sesi.mata_kuliah,
                        jam: sesi.jam,
                        kondisiSesi: sesi.kondisi
                    });
                });
            });

            return presensiList;
        }

        (server.mahasiswa || []).forEach(function (mahasiswa) {
            presensiList.push({
                id: nextId++,
                nama: mahasiswa.nama,
                nim: mahasiswa.nim,
                kelas: kelas,
                waktu: mahasiswa.waktu ? new Date(mahasiswa.waktu) : null,
                metode: mahasiswa.metode,
                status: toLabelStatus(mahasiswa.absensi)
            });
        });

        return presensiList;
    }

    /**
     * Status dari database tersimpan huruf kecil (`hadir`, `izin`, ...),
     * sedangkan tampilan memakai huruf besar. Students yang belum punya
     * catatan presensi bernilai null dan dibiarkan null.
     */
    function toLabelStatus(status) {
        if (!status) return null;

        const kapital = String(status).charAt(0).toUpperCase() + String(status).slice(1);

        return ['Hadir', 'Izin', 'Sakit', 'Alpha'].indexOf(kapital) === -1 ? null : kapital;
    }

    // ============================================================
    // ABSENSI SISWA (dari halaman absen mahasiswa, via localStorage)
    // ============================================================
    function loadAllAbsensiMahasiswa() {
        let semua = [];

        // Sumber utama: daftar_absen
        try {
            const daftar = JSON.parse(localStorage.getItem(STORAGE_KEY_DAFTAR_ABSEN) || '[]');
            if (Array.isArray(daftar)) semua = daftar;
        } catch (e) {
            console.warn('Gagal parse daftar_absen:', e);
        }

        // Fallback: mahasiswa_absen (kalau belum ada di daftar)
        try {
            const terakhir = JSON.parse(localStorage.getItem(STORAGE_KEY_SISWA_ABSEN) || 'null');
            if (terakhir && terakhir.timestamp) {
                const sudahAda = semua.some((d) => d.nama === terakhir.nama && d.timestamp === terakhir.timestamp);
                if (!sudahAda) semua.push(terakhir);
            }
        } catch (e) {
            console.warn('Gagal parse mahasiswa_absen:', e);
        }

        // Urutkan terbaru di atas
        semua.sort((a, b) => (b.timestamp || 0) - (a.timestamp || 0));

        return semua;
    }

    /**
     * Rekap jumlah per status.
     *
     * `belum` dipakai untuk mahasiswa yang belum absen hari ini, supaya tidak
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
     * Poin masuk untuk setiap halaman Dosen: pastikan identitas sudah ada,
     * lalu muat data kelas ke `presensiList`.
     */
    function initHalamanDosen(render) {
        document.addEventListener('DOMContentLoaded', function () {
            cekIdentitasDosen();
            loadDataKelas(identitasDosen.kelasLengkap);

            if (typeof render === 'function') render();
        });
    }
</script>
