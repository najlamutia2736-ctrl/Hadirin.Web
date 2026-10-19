@extends('layouts.app')

@section('title', 'Kelola Data Kelas · Hadirin.Web')

@php
    $pageTitle = 'Kelola Data Kelas';
    $breadcrumbItems = [
        ['label' => 'Beranda'],
        ['label' => 'Dashboard Dosen'],
        ['label' => 'Kelola Data Kelas', 'current' => true],
    ];

    $genderOptions = [
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
    ];

    $statusOptions = ['Aktif', 'Nonaktif', 'Pindah'];
@endphp

@section('konten')
    {{-- pemilih kelas --}}
    <div class="mb-6 flex flex-col gap-3 rounded-xl border border-gray-100 bg-white px-5 py-4 shadow-sm lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                <i class="fas fa-chalkboard"></i>
            </span>
            <div>
                <h3 class="text-sm font-semibold text-gray-800">Kelas yang Saya Ampu</h3>
                <p class="text-xs text-gray-500">Pilih kelas untuk melihat dan mengelola daftar mahasiswanya</p>
            </div>
        </div>
        <div id="kelolaKelasChips" class="-mx-1 flex flex-wrap gap-2 px-1"></div>
    </div>

    <div id="kelolaKosong"
        class="mb-6 hidden rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
        <div class="flex items-start gap-3">
            <i class="fas fa-circle-info mt-0.5 shrink-0"></i>
            <div>
                <p class="font-semibold">Belum ada kelas yang diampu.</p>
                <p class="mt-0.5">
                    Hubungkan kelas dengan akun Anda di dashboard admin, menu
                    <a href="{{ route('cms.kelas') }}" class="font-semibold underline">Classes</a>,
                    kolom <span class="font-semibold">Dosen Pengampu</span>. Daftar mahasiswa
                    kelas tersebut akan muncul di halaman ini.
                </p>
            </div>
        </div>
    </div>

    {{-- daftar mahasiswa --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-violet-50 text-violet-600">
                    <i class="fas fa-users"></i>
                </span>
                <div>
                    <h3 class="font-semibold text-gray-800">Daftar Mahasiswa</h3>
                    <p class="text-xs text-gray-500" id="kelolaSubjudul">-</p>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative w-full sm:w-56">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </span>
                    <input type="search" id="cariMahasiswa" placeholder="Cari nama atau NIM..."
                        class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
                <button type="button" id="tombolTambahMahasiswa" onclick="tambahMahasiswa()"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
                    <i class="fas fa-plus"></i>
                    Tambah Mahasiswa
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Mahasiswa</th>
                        <th class="px-4 py-3 font-semibold">NIM</th>
                        <th class="px-4 py-3 font-semibold">L/P</th>
                        <th class="px-4 py-3 font-semibold">Orang Tua</th>
                        <th class="px-4 py-3 font-semibold">Kontak Orang Tua</th>
                        <th class="px-4 py-3 font-semibold">Presensi Hari Ini</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-6 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody id="kelolaTabelMahasiswa" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-6 py-3 text-center text-xs text-gray-500">
            <span id="kelolaJumlahMahasiswa">0</span> mahasiswa ditampilkan
            <a href="{{ route('dosen.dashboard') }}" class="ml-2 font-semibold text-indigo-600 hover:text-indigo-700">
                Lihat rekap kehadiran <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>

    {{-- modal tambah / ubah mahasiswa --}}
    <div id="modalMahasiswa" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-lg rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                        <i class="fas fa-user-plus"></i>
                    </span>
                    <h3 class="font-semibold text-gray-800" id="judulModalMahasiswa">Tambah Mahasiswa</h3>
                </div>
                <button type="button" data-modal-mahasiswa-close class="text-gray-400 transition-colors hover:text-gray-600">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <form id="formMahasiswa" class="px-6 py-5" onsubmit="return simpanMahasiswa(event)">
                <input type="hidden" id="mahasiswaId" value="">

                <div id="modalMahasiswaError"
                    class="mb-4 hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-700"></div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="mahasiswaName" class="mb-1.5 block text-sm font-semibold text-gray-700">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input id="mahasiswaName" name="name" type="text" maxlength="255" required
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="mahasiswaNim" class="mb-1.5 block text-sm font-semibold text-gray-700">
                            NIM <span class="text-red-500">*</span>
                        </label>
                        <input id="mahasiswaNim" name="nim" type="text" inputmode="numeric" maxlength="8" required
                            pattern="[0-9]{8}"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <p class="mt-1.5 text-xs text-gray-500">8 angka, sekaligus jadi password awal mahasiswa.</p>
                    </div>

                    <div>
                        <label for="mahasiswaKelas" class="mb-1.5 block text-sm font-semibold text-gray-700">
                            Kelas <span class="text-red-500">*</span>
                        </label>
                        <select id="mahasiswaKelas" name="class" required
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"></select>
                    </div>

                    <div>
                        <label for="mahasiswaGender" class="mb-1.5 block text-sm font-semibold text-gray-700">
                            Jenis Kelamin <span class="text-red-500">*</span>
                        </label>
                        <select id="mahasiswaGender" name="gender" required
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach ($genderOptions as $nilai => $label)
                                <option value="{{ $nilai }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="mahasiswaStatus" class="mb-1.5 block text-sm font-semibold text-gray-700">
                            Status
                        </label>
                        <select id="mahasiswaStatus" name="status" disabled
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-500 disabled:cursor-not-allowed">
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-xs text-gray-500">Mahasiswa baru selalu berstatus Aktif.</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="mahasiswaWali" class="mb-1.5 block text-sm font-semibold text-gray-700">
                            Nama Orang Tua / Wali
                        </label>
                        <input id="mahasiswaWali" name="parent" type="text" maxlength="255"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="mahasiswaTelepon" class="mb-1.5 block text-sm font-semibold text-gray-700">
                            Nomor Telepon Orang Tua
                        </label>
                        <input id="mahasiswaTelepon" name="phone" type="text" maxlength="20"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end">
                    <button type="button" data-modal-mahasiswa-close
                        class="rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 transition-colors hover:bg-gray-100">
                        Batal
                    </button>
                    <button type="submit" id="tombolSimpanMahasiswa"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-indigo-700">
                        <i class="fas fa-check"></i>
                        Simpan Mahasiswa
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- modal konfirmasi hapus --}}
    <div id="modalHapusMahasiswa" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-sm rounded-xl bg-white shadow-xl">
            <div class="px-6 py-5 text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600">
                    <i class="fas fa-trash"></i>
                </div>
                <h3 class="font-semibold text-gray-800">Hapus Mahasiswa?</h3>
                <p class="mt-1 text-sm text-gray-500">
                    <span id="hapusMahasiswaNama" class="font-medium text-gray-700"></span>
                    beserta akunnya akan dihapus permanen.
                </p>
            </div>
            <div class="flex gap-3 border-t border-gray-200 px-6 py-4">
                <button type="button" data-modal-hapus-close
                    class="flex-1 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Batal
                </button>
                <button type="button" id="tombolKonfirmasiHapus"
                    class="flex-1 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                    Hapus
                </button>
            </div>
        </div>
    </div>

    {{-- notifikasi --}}
    <div id="notifikasiKelola"
        class="pointer-events-none fixed bottom-5 right-5 z-50 hidden max-w-sm rounded-xl border px-4 py-3 text-sm font-medium shadow-lg"></div>
@endsection

@push('styles')
    @include('dosen.partials.styles')

    <style>
        .kelola-row {
            transition: background-color 0.2s ease;
        }

        .kelola-row:hover {
            background-color: #f9fafb;
        }

        .kelas-chip {
            transition: all 0.2s ease;
        }

        /* notifikasi */
        @keyframes notifikasiMasuk {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #notifikasiKelola:not(.hidden) {
            animation: notifikasiMasuk 0.25s ease;
        }
    </style>
@endpush

@push('scripts')
    <script>
        window.HADIRIN_DOSEN = @json($dosenData ?? null);
    </script>

    @include('dosen.partials.data-dosen')

    <script>
        // ============================================================
        // KELOLA DATA KELAS
        // ============================================================
        // Halaman ini mengelola daftar mahasiswa pada kelas yang diampu dosen.
        // Kelas dan mahasiswanya datang dari server lewat `HADIRIN_DOSEN`, jadi
        // perubahan di dashboard admin ikut terlihat di sini.

        const ENDPOINT_SIMPAN = @json(route('dosen.kelola.mahasiswa.store'));
        const DASAR_SISWA = @json(url('/dashboard/dosen/kelola/mahasiswa'));

        /** URL satu mahasiswa, dipakai untuk ubah dan hapus. */
        function endpointMahasiswa(id) {
            return DASAR_SISWA + '/' + id;
        }

        /** Token CSRF halaman, dipakai untuk request AJAX. */
        function tokenCsrf() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.content : '';
        }

        const BADGE_STATUS_SISWA = {
            'Aktif': 'badge-hadir',
            'Nonaktif': 'badge-alpha',
            'Pindah': 'badge-izin'
        };

        let kelasAktif = null;
        let daftarKelas = [];
        let filterCari = '';
        let mahasiswaSedangDihapus = null;

        // ============================================================
        // SUMBER DATA
        // ============================================================

        /** Kelas yang sedang di layar, atau null. */
        function kelasSekarang() {
            if (!SERVER_DATA || !Array.isArray(SERVER_DATA.kelas)) return null;

            return SERVER_DATA.kelas.find(function (item) {
                return item.nama === kelasAktif;
            }) || null;
        }

        /** Daftar mahasiswa pada kelas aktif, sudah disaring pencarian. */
        function mahasiswaTerfilter() {
            const kelas = kelasSekarang();
            if (kelas === null) return [];

            const cari = filterCari.trim().toLowerCase();

            return (kelas.mahasiswa || []).filter(function (mahasiswa) {
                if (!cari) return true;

                return String(mahasiswa.nama || '').toLowerCase().indexOf(cari) !== -1
                    || String(mahasiswa.nim || '').toLowerCase().indexOf(cari) !== -1;
            });
        }

        // ============================================================
        // RENDER
        // ============================================================
        function renderPemilihKelas() {
            const container = document.getElementById('kelolaKelasChips');
            const kosong = document.getElementById('kelolaKosong');
            const tombolTambah = document.getElementById('tombolTambahMahasiswa');

            if (kosong) kosong.classList.toggle('hidden', daftarKelas.length > 0);
            if (tombolTambah) tombolTambah.classList.toggle('hidden', daftarKelas.length === 0);
            if (!container) return;

            container.innerHTML = '';
            if (daftarKelas.length === 0) return;

            daftarKelas.forEach(function (nama) {
                const kelas = SERVER_DATA.kelas.find(function (item) {
                    return item.nama === nama;
                });
                const jumlah = kelas ? (kelas.mahasiswa || []).length : 0;
                const aktif = nama === kelasAktif;

                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'kelas-chip inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-xs font-semibold ring-1 ' +
                    (aktif
                        ? 'bg-indigo-600 text-white ring-indigo-600 shadow-md shadow-indigo-500/30'
                        : 'bg-white text-gray-600 ring-gray-200 hover:bg-indigo-50 hover:text-indigo-600 hover:ring-indigo-200');
                chip.innerHTML = '<span>' + nama + '</span>' +
                    '<span class="rounded-full px-1.5 py-0.5 text-[10px] ' +
                    (aktif ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500') + '">' +
                    jumlah + '</span>';

                chip.addEventListener('click', function () {
                    kelasAktif = nama;
                    filterCari = '';

                    const cari = document.getElementById('cariMahasiswa');
                    if (cari) cari.value = '';

                    renderSeluruhHalaman();
                });

                container.appendChild(chip);
            });
        }

        function renderTabelMahasiswa() {
            const tbody = document.getElementById('kelolaTabelMahasiswa');
            if (!tbody) return;

            const kelas = kelasSekarang();
            const semua = kelas ? (kelas.mahasiswa || []) : [];
            const baris = mahasiswaTerfilter();

            setTeks('#kelolaSubjudul', kelasAktif
                ? kelasAktif + ' · ' + semua.length + ' mahasiswa terdaftar'
                : 'Pilih kelas terlebih dahulu');
            setTeks('#kelolaJumlahMahasiswa', baris.length);

            tbody.innerHTML = '';

            if (kelas === null) {
                tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-10 text-center text-sm text-gray-400">' +
                    '<i class="fas fa-inbox mb-2 block text-2xl text-gray-300"></i>' +
                    'Pilih kelas untuk melihat daftar mahasiswa.</td></tr>';
                return;
            }

            if (semua.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-10 text-center text-sm text-gray-400">' +
                    '<i class="fas fa-users-slash mb-2 block text-2xl text-gray-300"></i>' +
                    'Kelas ini belum punya mahasiswa. Klik "Tambah Mahasiswa" untuk menambahkan.</td></tr>';
                return;
            }

            if (baris.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-10 text-center text-sm text-gray-400">' +
                    'Tidak ada mahasiswa yang cocok dengan pencarian "' + filterCari + '".</td></tr>';
                return;
            }

            baris.forEach(function (mahasiswa) {
                const inisial = (mahasiswa.nama || '?').charAt(0).toUpperCase();

                const tr = document.createElement('tr');
                tr.className = 'kelola-row';
                tr.innerHTML =
                    '<td class="px-6 py-3">' +
                        '<div class="flex items-center gap-3">' +
                            '<span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-600">' +
                                inisial + '</span>' +
                            '<span class="block max-w-[14rem] truncate text-sm font-semibold text-gray-800">' +
                                (mahasiswa.nama || '-') + '</span>' +
                        '</div>' +
                    '</td>' +
                    '<td class="px-4 py-3 text-sm text-gray-600">' + (mahasiswa.nim || '-') + '</td>' +
                    '<td class="px-4 py-3 text-sm text-gray-600">' + (mahasiswa.gender === 'P' ? 'Perempuan' : 'Laki-laki') + '</td>' +
                    '<td class="px-4 py-3 text-sm text-gray-600">' + (mahasiswa.wali || '-') + '</td>' +
                    '<td class="px-4 py-3 text-sm text-gray-600">' + (mahasiswa.telepon || '-') + '</td>' +
                    '<td class="px-4 py-3">' +
                        (mahasiswa.absensi
                            ? '<span class="' + badgeAbsensi(mahasiswa.absensi) + '">' + kapital(mahasiswa.absensi) + '</span>'
                            : '<span class="badge-belum">Belum Presen</span>') +
                    '</td>' +
                    '<td class="px-4 py-3">' +
                        '<span class="' + (BADGE_STATUS_SISWA[mahasiswa.status] || 'badge-belum') + '">' +
                        (mahasiswa.status || '-') + '</span>' +
                    '</td>' +
                    '<td class="px-6 py-3">' +
                        '<div class="flex items-center justify-end gap-1">' +
                            '<button type="button" title="Ubah" onclick="ubahMahasiswa(' + mahasiswa.id + ')" ' +
                                'class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 transition-colors hover:bg-blue-50">' +
                                '<i class="fas fa-pen text-xs"></i></button>' +
                            '<button type="button" title="Hapus" onclick="konfirmasiHapusMahasiswa(' + mahasiswa.id + ')" ' +
                                'class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition-colors hover:bg-red-50">' +
                                '<i class="fas fa-trash text-xs"></i></button>' +
                        '</div>' +
                    '</td>';

                tbody.appendChild(tr);
            });
        }

        function renderSeluruhHalaman() {
            renderPemilihKelas();
            renderTabelMahasiswa();
        }

        // ============================================================
        // MODAL
        // ============================================================
        function bukaModalMahasiswa() {
            const modal = document.getElementById('modalMahasiswa');
            if (!modal) return;

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            const nama = document.getElementById('mahasiswaName');
            if (nama) nama.focus();
        }

        function tutupModalMahasiswa() {
            const modal = document.getElementById('modalMahasiswa');
            if (!modal) return;

            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function tutupModalHapus() {
            const modal = document.getElementById('modalHapusMahasiswa');
            if (!modal) return;

            modal.classList.add('hidden');
            modal.classList.remove('flex');
            mahasiswaSedangDihapus = null;
        }

        function isiDropdownKelas(terpilih) {
            const select = document.getElementById('mahasiswaKelas');
            if (!select) return;

            select.innerHTML = '';

            daftarKelas.forEach(function (nama) {
                const option = document.createElement('option');
                option.value = nama;
                option.textContent = nama;
                option.selected = nama === terpilih;
                select.appendChild(option);
            });

            select.disabled = daftarKelas.length < 2;
        }

        /**
         * Buka modal tambah mahasiswa baru.
         */
        function tambahMahasiswa() {
            if (daftarKelas.length === 0) {
                notifikasi('Belum ada kelas yang bisa ditambahkan mahasiswa.', 'amber');
                return;
            }

            document.getElementById('formMahasiswa').reset();
            document.getElementById('mahasiswaId').value = '';
            document.getElementById('judulModalMahasiswa').textContent = 'Tambah Mahasiswa';
            sembunyikanErrorModal();

            const status = document.getElementById('mahasiswaStatus');
            if (status) status.value = 'Aktif';

            isiDropdownKelas(kelasAktif);
            bukaModalMahasiswa();
        }

        /**
         * Buka modal ubah mahasiswa yang sudah ada.
         */
        function ubahMahasiswa(id) {
            const kelas = kelasSekarang();
            if (kelas === null) return;

            const mahasiswa = (kelas.mahasiswa || []).find(function (item) {
                return item.id === id;
            });

            if (!mahasiswa) return;

            document.getElementById('formMahasiswa').reset();
            document.getElementById('mahasiswaId').value = mahasiswa.id;
            document.getElementById('judulModalMahasiswa').textContent = 'Ubah Data Mahasiswa';
            sembunyikanErrorModal();

            document.getElementById('mahasiswaName').value = mahasiswa.nama || '';
            document.getElementById('mahasiswaNim').value = mahasiswa.nim || '';
            document.getElementById('mahasiswaGender').value = mahasiswa.gender === 'P' ? 'P' : 'L';
            document.getElementById('mahasiswaWali').value = mahasiswa.wali || '';
            document.getElementById('mahasiswaTelepon').value = mahasiswa.telepon || '';

            const status = document.getElementById('mahasiswaStatus');
            if (status) {
                status.value = mahasiswa.status || 'Aktif';
                status.disabled = false;
            }

            isiDropdownKelas(kelasAktif);
            bukaModalMahasiswa();
        }

        function konfirmasiHapusMahasiswa(id) {
            const kelas = kelasSekarang();
            if (kelas === null) return;

            const mahasiswa = (kelas.mahasiswa || []).find(function (item) {
                return item.id === id;
            });

            if (!mahasiswa) return;

            mahasiswaSedangDihapus = mahasiswa;

            const nama = document.getElementById('hapusMahasiswaNama');
            if (nama) nama.textContent = mahasiswa.nama || '-';

            const modal = document.getElementById('modalHapusMahasiswa');
            if (!modal) return;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        // ============================================================
        // KOMUNIKASI KE SERVER
        // ============================================================

        /**
         * Simpan mahasiswa baru atau perubahan mahasiswa yang sudah ada.
         */
        async function simpanMahasiswa(event) {
            event.preventDefault();

            const id = document.getElementById('mahasiswaId').value;
            const tombol = document.getElementById('tombolSimpanMahasiswa');

            const data = {
                name: document.getElementById('mahasiswaName').value,
                nim: document.getElementById('mahasiswaNim').value,
                class: document.getElementById('mahasiswaKelas').value,
                gender: document.getElementById('mahasiswaGender').value,
                parent: document.getElementById('mahasiswaWali').value,
                phone: document.getElementById('mahasiswaTelepon').value,
                status: document.getElementById('mahasiswaStatus').value
            };

            tombol.disabled = true;
            sembunyikanErrorModal();

            try {
                const url = id ? endpointMahasiswa(id) : ENDPOINT_SIMPAN;
                const method = id ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': tokenCsrf()
                    },
                    body: JSON.stringify(data)
                });

                const hasil = await response.json();

                if (!response.ok) {
                    tampilkanErrorModal(hasil);
                    return;
                }

                tutupModalMahasiswa();
                notifikasi(hasil.message, 'green');
                await muatUlangData();
            } catch (error) {
                notifikasi('Gagal menghubungi server. Coba lagi.', 'red');
            } finally {
                tombol.disabled = false;
            }
        }

        /**
         * Hapus mahasiswa yang dipilih pada modal konfirmasi.
         */
        async function hapusMahasiswa() {
            if (mahasiswaSedangDihapus === null) return;

            const tombol = document.getElementById('tombolKonfirmasiHapus');
            tombol.disabled = true;

            try {
                const response = await fetch(endpointMahasiswa(mahasiswaSedangDihapus.id), {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': tokenCsrf()
                    }
                });

                const hasil = await response.json();

                if (!response.ok) {
                    notifikasi(hasil.message || 'Mahasiswa gagal dihapus.', 'red');
                    return;
                }

                tutupModalHapus();
                notifikasi(hasil.message, 'green');
                await muatUlangData();
            } catch (error) {
                notifikasi('Gagal menghubungi server. Coba lagi.', 'red');
            } finally {
                tombol.disabled = false;
            }
        }

        /**
         * Muat ulang daftar mahasiswa dari server.
         *
         * Server tetap jadi satu-satunya sumber kebenaran, jadi setiap
         * perubahan selalu langsung tercermin di tabel.
         */
        async function muatUlangData() {
            try {
                const response = await fetch(@json(route('dosen.dashboard')), {
                    headers: { Accept: 'application/json' }
                });

                if (!response.ok) return;

                SERVER_DATA.kelas = (await response.json()).kelas;

                daftarKelas = daftarKelasServer();

                if (daftarKelas.indexOf(kelasAktif) === -1) {
                    kelasAktif = daftarKelas[0] || null;
                }

                renderSeluruhHalaman();
            } catch (error) {
                notifikasi('Gagal memuat data terbaru.', 'red');
            }
        }

        // ============================================================
        // BANTUAN
        // ============================================================
        function setTeks(selector, nilai) {
            const el = document.querySelector(selector);
            if (el) el.textContent = nilai;
        }

        function kapital(nilai) {
            return String(nilai || '').charAt(0).toUpperCase() + String(nilai || '').slice(1);
        }

        function badgeAbsensi(status) {
            const peta = {
                'hadir': 'badge-hadir',
                'izin': 'badge-izin',
                'sakit': 'badge-sakit',
                'alpha': 'badge-alpha'
            };

            return peta[String(status || '').toLowerCase()] || 'badge-belum';
        }

        function tampilkanErrorModal(hasil) {
            const box = document.getElementById('modalMahasiswaError');
            if (!box) return;

            const pesan = hasil && hasil.errors
                ? Object.values(hasil.errors).flat().join(' ')
                : (hasil && hasil.message) || 'Terjadi kesalahan. Coba lagi.';

            box.textContent = pesan;
            box.classList.remove('hidden');
        }

        function sembunyikanErrorModal() {
            const box = document.getElementById('modalMahasiswaError');
            if (box) box.classList.add('hidden');
        }

        function notifikasi(pesan, warna) {
            const box = document.getElementById('notifikasiKelola');
            if (!box) return;

            const gaya = {
                green: 'border-emerald-200 bg-emerald-50 text-emerald-700',
                red: 'border-red-200 bg-red-50 text-red-700',
                amber: 'border-amber-200 bg-amber-50 text-amber-700'
            };

            box.className = 'pointer-events-none fixed bottom-5 right-5 z-50 max-w-sm rounded-xl border px-4 py-3 text-sm font-medium shadow-lg '
                + (gaya[warna] || gaya.green);
            box.textContent = pesan;
            box.classList.remove('hidden');

            window.clearTimeout(box.dataset.timer);
            box.dataset.timer = window.setTimeout(function () {
                box.classList.add('hidden');
            }, 3500);
        }

        // ============================================================
        // INIT
        // ============================================================
        initHalamanDosen(function () {
            daftarKelas = daftarKelasServer();

            kelasAktif = daftarKelas.indexOf(identitasDosen.kelasLengkap) !== -1
                ? identitasDosen.kelasLengkap
                : (daftarKelas[0] || null);

            renderSeluruhHalaman();

            const cari = document.getElementById('cariMahasiswa');
            if (cari) {
                cari.addEventListener('input', function () {
                    filterCari = cari.value;
                    renderTabelMahasiswa();
                });
            }

            document.querySelectorAll('[data-modal-mahasiswa-close]').forEach(function (tombol) {
                tombol.addEventListener('click', tutupModalMahasiswa);
            });

            document.querySelectorAll('[data-modal-hapus-close]').forEach(function (tombol) {
                tombol.addEventListener('click', tutupModalHapus);
            });

            document.getElementById('tombolKonfirmasiHapus')
                .addEventListener('click', hapusMahasiswa);

            document.getElementById('modalMahasiswa').addEventListener('click', function (event) {
                if (event.target === event.currentTarget) tutupModalMahasiswa();
            });

            document.getElementById('modalHapusMahasiswa').addEventListener('click', function (event) {
                if (event.target === event.currentTarget) tutupModalHapus();
            });
        });
    </script>
@endpush
