<?php

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Mahasiswa lengkap dengan akun pengguna, untuk test tampilan barcode.
 */
function mahasiswaBarcode(array $atributMahasiswa = []): Mahasiswa
{
    $user = User::factory()->create(['role' => 'Mahasiswa', 'status' => 'Aktif']);

    return Mahasiswa::factory()->create([
        'user_id' => $user->id,
        ...$atributMahasiswa,
    ]);
}

test('mahasiswa baru langsung menampilkan barcode di halaman daftar', function () {
    Kelas::factory()->create(['nama_kelas' => 'Kelas RPL']);

    loginAdmin();

    $this->post(route('cms.mahasiswa.store'), [
        'name' => 'Siti Uji Barcode',
        'nim' => '20269999',
        'class' => 'Kelas RPL',
        'gender' => 'P',
    ])
        ->assertRedirect(route('cms.mahasiswa'))
        ->assertSessionHas('mahasiswa_baru');

    // Halaman tujuan sudah membuka modal barcode milik mahasiswa barunya.
    $this->get(route('cms.mahasiswa'))
        ->assertOk()
        ->assertSee('Barcode Presensi')
        ->assertSee('data-barcode-teks="20269999"', false)
        ->assertSee('qrcodejs', false);
});

test('tiap baris mahasiswa punya tombol untuk membuka barcode', function () {
    mahasiswaBarcode(['nim' => '20260001', 'kelas' => 'Kelas RPL']);

    loginAdmin();

    $this->get(route('cms.mahasiswa'))
        ->assertOk()
        ->assertSee('data-barcode-nim="20260001"', false)
        ->assertSee('Lihat barcode presensi', false);
});

test('halaman identitas menampilkan barcode milik mahasiswa sendiri', function () {
    $mahasiswa = mahasiswaBarcode([
        'nim' => '20260002',
        'kelas' => 'Kelas RPL',
    ]);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.identitas'))
        ->assertOk()
        ->assertSee('Barcode Presensi')
        ->assertSee('data-barcode-teks="20260002"', false)
        ->assertSee($mahasiswa->nim);
});

test('halaman identitas mahasiswa lain tidak tercampur barcode-nya', function () {
    $mahasiswa = mahasiswaBarcode(['nim' => '20260003']);
    mahasiswaBarcode(['nim' => '20260004']);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.identitas'))
        ->assertOk()
        ->assertSee('data-barcode-teks="20260003"', false)
        ->assertDontSee('data-barcode-teks="20260004"', false);
});

test('halaman kelola dosen menyertakan modal barcode', function () {
    $dosenLogin = loginDosen();

    $this->get(route('dosen.kelola'))
        ->assertOk()
        ->assertSee('modalBarcodeMahasiswa')
        ->assertSee('qrcodejs', false);
});
