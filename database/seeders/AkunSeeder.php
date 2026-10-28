<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Akun bawaan untuk admin, dosen, dan mahasiswa.
 *
 * Ketiganya mengikuti pola yang dipakai form CMS: password adalah NIDN untuk
 * dosen dan NIM untuk mahasiswa (lihat AuthController: akun dibuat memakai
 * email dan NIDN/NIM sebagai password), sedangkan admin tidak punya NIDN/NIM
 * jadi passwordnya ditetapkan sendiri.
 *
 * Dosen dan mahasiswa selalu dibuat lengkap dengan baris profilnya, karena
 * dashboard dosen dan halaman absensi menolak akun yang tidak punya profil.
 *
 * Seeder ini idempoten: email, NIDN, dan NIM dipakai sebagai kunci, jadi
 * dijalankan berapa kali pun tidak menimbulkan akun ganda — isinya hanya
 * disamakan kembali dengan nilai bawaan di bawah.
 *
 * Trait `WithoutModelEvents` sengaja TIDAK dipakai di sini, berbeda dari
 * seeder lain. Kolom `dosens.mata_kuliah` berstatus NOT NULL dan isinya
 * diisi oleh event `saving` milik model `Dosen`; kalau event dimatikan,
 * pembuatan dosen akan gagal karena kolom teks itu tidak pernah terisi.
 */
class AkunSeeder extends Seeder
{
    /**
     * Email dan password akun admin.
     */
    private const EMAIL_ADMIN = 'admin@kampus.ac.id';

    private const PASSWORD_ADMIN = 'password123';

    private const NAMA_ADMIN = 'Admin Kampus';

    /**
     * NIDN dosen bawaan. Email dan password akunnya adalah NIDN ini.
     */
    private const NIDN_DOSEN = '1987123456';

    private const NAMA_DOSEN = 'Ahmad Fauzi';

    /**
     * NIM mahasiswa bawaan. Email dan password akunnya adalah NIM ini.
     */
    private const NIM_MAHASISWA = '20260001';

    private const NAMA_MAHASISWA = 'Siti Aminah';

    /**
     * Kelas yang dipegang mahasiswa bawaan, diambil dari daftar kelas baku.
     */
    private const KELAS_MAHASISWA = Kelas::KELAS_TERSEDIA[0];

    public function run(): void
    {
        // Kelas dibuat lebih dulu supaya profil mahasiswa selalu menunjuk kelas
        // yang benar-benar ada di tabel `kelas`, sama seperti yang diminta
        // validasi form CMS (`exists:kelas,nama_kelas`).
        $this->call(KelasSeeder::class);

        DB::transaction(function (): void {
            $this->buatAdmin();
            $this->buatDosen();
            $this->buatMahasiswa();
        });

        $this->command?->info('Akun bawaan siap dipakai:');
        $this->command?->line('  Admin      '.self::EMAIL_ADMIN.' / '.self::PASSWORD_ADMIN);
        $this->command?->line('  Dosen      '.self::NIDN_DOSEN.'@dosen.kampus.ac.id / '.self::NIDN_DOSEN);
        $this->command?->line('  Mahasiswa  '.self::NIM_MAHASISWA.'@mahasiswa.kampus.ac.id / '.self::NIM_MAHASISWA);
    }

    /**
     * Akun admin. Hanya baris `users` karena tabel `admins` tidak dipakai
     * oleh aplikasi — peran admin cukup dibaca dari `users.role`.
     */
    private function buatAdmin(): void
    {
        User::query()->updateOrCreate(
            ['email' => self::EMAIL_ADMIN],
            [
                'name' => self::NAMA_ADMIN,
                'password' => self::PASSWORD_ADMIN,
                'role' => 'Admin',
                'status' => 'Aktif',
            ],
        );
    }

    /**
     * Akun dosen beserta baris profil `dosens`-nya, dibuat dalam satu
     * transaksi sama seperti yang dilakukan DosenController.
     *
     * Mata kuliah diambil dari baris pertama tabel `mata_kuliah`, dan dibuat
     * bila tabelnya masih kosong, supaya dashboard dosen punya mata kuliah
     * yang bisa ditampilkan.
     */
    private function buatDosen(): void
    {
        // Kolom `dosens.mata_kuliah` berstatus NOT NULL, jadi minimal harus
        // ada satu mata kuliah. KelasSeeder biasanya sudah membuatnya lewat
        // factory, tetapi tetap dibuat di sini supaya seeder aman dijalankan
        // pada database yang benar-benar kosong.
        $mataKuliah = MataKuliah::query()->orderBy('id')->first()
            ?? MataKuliah::query()->create([
                'kode_mata_kuliah' => 'MK001',
                'nama_mata_kuliah' => 'Informatika',
            ]);

        $user = User::query()->updateOrCreate(
            ['email' => self::NIDN_DOSEN.'@dosen.kampus.ac.id'],
            [
                'name' => self::NAMA_DOSEN,
                'password' => self::NIDN_DOSEN,
                'role' => 'Dosen',
                'status' => 'Aktif',
            ],
        );

        Dosen::query()->updateOrCreate(
            ['nidn' => self::NIDN_DOSEN],
            [
                'user_id' => $user->id,
                'mata_kuliah_id' => $mataKuliah->id,
                'telepon' => '081234567890',
                'status' => 'Aktif',
            ],
        );
    }

    /**
     * Akun mahasiswa beserta baris profil `mahasiswa`-nya, dibuat dalam satu
     * transaksi sama seperti yang dilakukan MahasiswaController.
     */
    private function buatMahasiswa(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => self::NIM_MAHASISWA.'@mahasiswa.kampus.ac.id'],
            [
                'name' => self::NAMA_MAHASISWA,
                'password' => self::NIM_MAHASISWA,
                'role' => 'Mahasiswa',
                'status' => 'Aktif',
            ],
        );

        Mahasiswa::query()->updateOrCreate(
            ['nim' => self::NIM_MAHASISWA],
            [
                'user_id' => $user->id,
                'jenis_kelamin' => 'P',
                'wali' => 'Budi Santoso',
                'telepon_wali' => '081234567891',
                'kelas' => self::KELAS_MAHASISWA,
                'status' => 'Aktif',
            ],
        );
    }
}
