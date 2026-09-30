@extends('layouts.app')

@section('konten')
    {{-- pesan sukses --}}
    @if (session('success'))
        <div
            class="mb-6 flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- header halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Siswa</h2>
            <p class="mt-1 text-gray-600">Kelola data siswa, kelas, dan status keaktifan.</p>
        </div>
        <a href="{{ route('cms.student.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
            <i class="fas fa-user-plus"></i>
            Tambah Siswa
        </a>
    </div>

    {{-- tabel siswa --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        {{-- toolbar: pencarian & filter --}}
        <form method="GET" action="{{ route('cms.student') }}"
            class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:max-w-xs">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau NIS..."
                    class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <select name="kelas" data-filter-kelas aria-label="Filter kelas"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Kelas</option>
                    @foreach ($daftarKelas as $kelas)
                        <option value="{{ $kelas }}" @selected($filterKelas === $kelas)>{{ $kelas }}</option>
                    @endforeach
                </select>
                <select name="status" data-filter-status aria-label="Filter status"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    @foreach ($daftarStatus as $status)
                        <option value="{{ $status }}" @selected($filterStatus === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <button type="submit" data-terapkan
                    class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60">
                    Terapkan
                </button>
                @if ($filterKelas !== null || $filterStatus !== null)
                    <a href="{{ route('cms.student', array_filter(['q' => request('q')])) }}"
                        class="rounded-lg px-2 py-2 text-sm font-medium text-gray-500 transition-colors hover:text-gray-700">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Siswa</th>
                        <th class="px-6 py-3">NIS</th>
                        <th class="px-6 py-3">Kelas</th>
                        <th class="px-6 py-3">Jenis Kelamin</th>
                        <th class="px-6 py-3">Wali / Orang Tua</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($students as $student)
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-green-100 text-sm font-semibold text-green-600">
                                        {{ strtoupper(substr($student->user->name, 0, 1)) }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-800">{{ $student->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $student->telepon_wali }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $student->nisn }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-block rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-600">
                                    {{ $student->kelas }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $student->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $student->wali }}</td>
                            <td class="px-6 py-4">
                                @if ($student->status === 'Aktif')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-600">
                                        <span class="h-2 w-2 rounded-full bg-green-500"></span> Aktif
                                    </span>
                                @elseif ($student->status === 'Pindah')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-600">
                                        <span class="h-2 w-2 rounded-full bg-amber-500"></span> Pindah
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-400">
                                        <span class="h-2 w-2 rounded-full bg-gray-300"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('cms.student.edit', $student) }}" title="Ubah"
                                        aria-label="Ubah data {{ $student->user->name }}"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 transition-colors hover:bg-blue-50">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <button type="button" title="Lihat Detail" data-student-detail
                                        data-modal-open="modal-detail"
                                        data-student-name="{{ $student->user->name }}"
                                        data-student-nis="{{ $student->nisn }}"
                                        data-student-class="{{ $student->kelas }}"
                                        data-student-gender="{{ $student->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}"
                                        data-student-parent="{{ $student->wali }}"
                                        data-student-phone="{{ $student->telepon_wali }}"
                                        data-student-status="{{ $student->status }}"
                                        aria-label="Lihat detail {{ $student->user->name }}"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-purple-600 transition-colors hover:bg-purple-50">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                    <button type="button" title="Hapus" data-delete-student
                                        data-delete-url="{{ route('cms.student.destroy', $student) }}"
                                        data-student-name="{{ $student->user->name }}" data-modal-open="modal-hapus"
                                        aria-label="Hapus data {{ $student->user->name }}"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition-colors hover:bg-red-50">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                @if ($filterKelas !== null || $filterStatus !== null || request('q'))
                                    Tidak ada siswa yang cocok dengan filter.
                                    <a href="{{ route('cms.student') }}"
                                        class="font-medium text-indigo-600 hover:underline">Reset filter</a>
                                @else
                                    Belum ada data siswa.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- footer tabel (pagination) --}}
        <div class="flex flex-col gap-3 border-t border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs text-gray-500">
                    Menampilkan {{ $students->count() }} dari <span
                        class="font-medium text-gray-700">{{ $students->total() }}</span> siswa
                </p>
                @if ($filterKelas !== null || $filterStatus !== null)
                    <p class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-gray-500">
                        <span>Filter aktif:</span>
                        @if ($filterKelas !== null)
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 font-medium text-blue-700">
                                Kelas {{ $filterKelas }}
                            </span>
                        @endif
                        @if ($filterStatus !== null)
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 font-medium text-green-700">
                                Status {{ $filterStatus }}
                            </span>
                        @endif
                    </p>
                @endif
            </div>
            {{ $students->links() }}
        </div>
    </div>

    {{-- modal: tambah siswa --}}
    <div id="modal-tambah"
        class="fixed inset-0 z-50 {{ $errors->any() ? 'flex' : 'hidden' }} items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h3 class="font-semibold text-gray-800">Tambah Siswa</h3>
                <button type="button" data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form class="px-6 py-4" method="POST" action="{{ route('cms.student.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="name">Nama Lengkap</label>
                    <input id="name" name="name" type="text" required placeholder="Nama siswa"
                        value="{{ old('name') }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700" for="nis">NIS</label>
                        <input id="nis" name="nis" type="text" required placeholder="8 digit NIS"
                            value="{{ old('nis') }}"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @error('nis')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700" for="class">Kelas</label>
                        <select id="class" name="class"
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="X-A" @selected(old('class') === 'X-A')>X-A</option>
                            <option value="X-B" @selected(old('class') === 'X-B')>X-B</option>
                            <option value="XI-A" @selected(old('class') === 'XI-A')>XI-A</option>
                            <option value="XI-B" @selected(old('class') === 'XI-B')>XI-B</option>
                            <option value="XII-A" @selected(old('class') === 'XII-A')>XII-A</option>
                        </select>
                        @error('class')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="gender">Jenis Kelamin</label>
                    <select id="gender" name="gender"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="L" @selected(old('gender') === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('gender') === 'P')>Perempuan</option>
                    </select>
                    @error('gender')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="parent">Wali / Orang Tua</label>
                    <input id="parent" name="parent" type="text" placeholder="Nama wali atau orang tua"
                        value="{{ old('parent') }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('parent')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700" for="phone">No. Telepon Wali</label>
                    <input id="phone" name="phone" type="text" placeholder="08xx-xxxx-xxxx"
                        value="{{ old('phone') }}"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" data-modal-close
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- modal: detail siswa --}}
    <div id="modal-detail" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
        role="dialog" aria-modal="true" aria-labelledby="student-detail-title">
        <div class="w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <div>
                    <h3 id="student-detail-title" class="font-semibold text-gray-800">Detail Siswa</h3>
                    <p class="mt-0.5 text-xs text-gray-500">Informasi lengkap data siswa</p>
                </div>
                <button type="button" data-modal-close aria-label="Tutup modal detail"
                    class="text-gray-400 transition-colors hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="mb-5 flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-100 text-lg font-semibold text-green-600">
                        <span id="detail-student-initial">S</span>
                    </div>
                    <div>
                        <h4 id="detail-student-name" class="font-semibold text-gray-800">-</h4>
                        <p id="detail-student-nis" class="text-sm text-gray-500">-</p>
                    </div>
                </div>
                <dl class="grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Kelas</dt>
                        <dd id="detail-student-class" class="mt-1 font-medium text-gray-700">-</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Jenis Kelamin</dt>
                        <dd id="detail-student-gender" class="mt-1 font-medium text-gray-700">-</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Wali / Orang Tua</dt>
                        <dd id="detail-student-parent" class="mt-1 font-medium text-gray-700">-</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Nomor Telepon</dt>
                        <dd id="detail-student-phone" class="mt-1 font-medium text-gray-700">-</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Status</dt>
                        <dd id="detail-student-status" class="mt-1 font-medium text-gray-700">-</dd>
                    </div>
                </dl>
            </div>
            <div class="flex justify-end border-t border-gray-200 bg-gray-50 px-6 py-4">
                <button type="button" data-modal-close
                    class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- modal: konfirmasi hapus --}}
    <div id="modal-hapus" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
        role="dialog" aria-modal="true" aria-labelledby="delete-student-title">
        <div class="w-full max-w-sm rounded-xl bg-white shadow-xl">
            <div class="px-6 py-5 text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600">
                    <i class="fas fa-trash"></i>
                </div>
                <h3 id="delete-student-title" class="font-semibold text-gray-800">Hapus Siswa?</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Data <span id="delete-student-name" class="font-medium text-gray-700"></span> akan dihapus permanen.
                </p>
            </div>
            <form id="delete-student-form" method="POST" action="#"
                class="flex gap-3 border-t border-gray-200 px-6 py-4">
                @csrf
                @method('DELETE')
                <button type="button" data-modal-close
                    class="flex-1 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit"
                    class="flex-1 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Hapus</button>
            </form>
        </div>
    </div>

    <script>
        // Filter kelas & status langsung tersubmit begitu berubah, tombol
        // "Terapkan" tetap ada untuk submit lewat keyboard atau pencarian.
        document.querySelectorAll('[data-filter-kelas], [data-filter-status]').forEach(function(select) {
            select.addEventListener('change', function() {
                select.form.submit();
            });
        });

        var deleteStudentForm = document.getElementById('delete-student-form');
        var deleteStudentName = document.getElementById('delete-student-name');
        var detailStudentInitial = document.getElementById('detail-student-initial');
        var detailStudentName = document.getElementById('detail-student-name');
        var detailStudentNis = document.getElementById('detail-student-nis');
        var detailStudentClass = document.getElementById('detail-student-class');
        var detailStudentGender = document.getElementById('detail-student-gender');
        var detailStudentParent = document.getElementById('detail-student-parent');
        var detailStudentPhone = document.getElementById('detail-student-phone');
        var detailStudentStatus = document.getElementById('detail-student-status');

        function setModalVisibility(modal, isVisible) {
            if (!modal) {
                return;
            }

            modal.classList.toggle('hidden', !isVisible);
            modal.classList.toggle('flex', isVisible);
        }

        document.querySelectorAll('[data-student-detail]').forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                var studentName = trigger.dataset.studentName || 'Siswa';

                detailStudentInitial.textContent = studentName.charAt(0).toUpperCase();
                detailStudentName.textContent = studentName;
                detailStudentNis.textContent = 'NIS: ' + (trigger.dataset.studentNis || '-');
                detailStudentClass.textContent = trigger.dataset.studentClass || '-';
                detailStudentGender.textContent = trigger.dataset.studentGender || '-';
                detailStudentParent.textContent = trigger.dataset.studentParent || '-';
                detailStudentPhone.textContent = trigger.dataset.studentPhone || '-';
                detailStudentStatus.textContent = trigger.dataset.studentStatus || '-';
            });
        });

        document.querySelectorAll('[data-delete-student]').forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                deleteStudentForm.action = trigger.dataset.deleteUrl;
                deleteStudentName.textContent = trigger.dataset.studentName;
            });
        });

        document.querySelectorAll('[data-modal-open]').forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                setModalVisibility(document.getElementById(trigger.dataset.modalOpen), true);
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach(function(button) {
            button.addEventListener('click', function() {
                setModalVisibility(button.closest('[role="dialog"]') || button.closest('.fixed'), false);
            });
        });

        document.querySelectorAll('.fixed').forEach(function(modal) {
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    setModalVisibility(modal, false);
                }
            });
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.fixed.flex').forEach(function(modal) {
                    setModalVisibility(modal, false);
                });
            }
        });
    </script>
@endsection
