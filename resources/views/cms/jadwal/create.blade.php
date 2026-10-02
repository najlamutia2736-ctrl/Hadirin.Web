@extends('layouts.app')

@section('title', 'Tambah Jadwal · Hadirin.Web')

@section('konten')
    <div class="mx-auto max-w-3xl">
        <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('cms.jadwal') }}"
                class="inline-flex items-center gap-1.5 transition-colors hover:text-indigo-600">
                <i class="fas fa-arrow-left text-xs"></i>
                Jadwal Mengajar
            </a>
            <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
            <span class="font-medium text-gray-700">Tambah Jadwal</span>
        </nav>

        <div class="mb-6 flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                <i class="fas fa-calendar-plus text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Tambah Jadwal</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Satu slot jadwal = satu mata pelajaran di satu kelas pada hari dan jam tertentu.
                </p>
            </div>
        </div>

        @if ($errors->any())
            <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <div class="flex gap-3">
                    <i class="fas fa-exclamation-circle mt-0.5"></i>
                    <div>
                        <p class="font-semibold">Periksa kembali data yang diisi.</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('cms.jadwal.store') }}"
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @csrf

            <div class="border-b border-gray-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-gray-800">Detail Jadwal</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Kolom bertanda <span class="text-red-500">*</span> wajib diisi.
                </p>
            </div>

            @include('cms.jadwal.partials.form')

            <div
                class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end">
                <a href="{{ route('cms.jadwal') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 transition-colors hover:bg-gray-100">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
                    <i class="fas fa-check"></i>
                    Simpan Jadwal
                </button>
            </div>
        </form>

        <div class="mt-5 flex gap-3 rounded-xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-800">
            <i class="fas fa-info-circle mt-0.5 shrink-0"></i>
            <p>
                Satu kelas dan satu guru tidak boleh punya dua jadwal yang rentang jamnya bertumpuk
                pada hari yang sama. Slot yang bersambung, misalnya 07:30-08:30 lalu 08:30-09:00,
                tetap diperbolehkan.
            </p>
        </div>
    </div>
@endsection
