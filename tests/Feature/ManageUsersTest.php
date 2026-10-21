<?php

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
| Form pengguna berada di area CMS, jadi setiap test di file ini dijalankan
| sambil login sebagai admin.
*/
beforeEach(function () {
    loginAdmin();
});

test('halaman manajemen pengguna menampilkan data dari database', function () {
    $user = User::factory()->create(['name' => 'Ahmad Fauzi']);

    Mahasiswa::factory()->create([
        'user_id' => $user->id,
        'jenis_kelamin' => 'P',
    ]);

    $this->get(route('cms.users'))
        ->assertOk()
        ->assertSee('Manajemen Pengguna')
        ->assertSee('Ahmad Fauzi')
        ->assertSee('Jenis Kelamin')
        ->assertSee('Perempuan');
});

test('pengguna baru dapat ditambahkan melalui halaman tambah', function () {
    $response = $this->post(route('cms.users.store'), [
        'name' => 'Dewi Lestari',
        'email' => 'dewi.lestari@kampus.ac.id',
        'role' => 'Dosen',
        'status' => 'Aktif',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ]);

    $response
        ->assertRedirect(route('cms.users'))
        ->assertSessionHas('success', 'Pengguna berhasil ditambahkan.');

    $this->assertDatabaseHas('users', [
        'email' => 'dewi.lestari@kampus.ac.id',
        'role' => 'Dosen',
        'status' => 'Aktif',
        'name' => 'Dewi Lestari',
    ]);
});

test('form tambah pengguna menolak email yang sudah terdaftar', function () {
    User::factory()->create(['email' => 'ahmad.fauzi@kampus.ac.id']);

    $response = $this->from(route('cms.users'))->post(route('cms.users.store'), [
        'name' => 'Ahmad Fauzi Lain',
        'email' => 'ahmad.fauzi@kampus.ac.id',
        'role' => 'Admin',
        'password' => 'rahasia123',
    ]);

    $response
        ->assertRedirect(route('cms.users'))
        ->assertSessionHasErrors('email');

    // Akun admin dari `loginAdmin()` ikut ada, jadi yang dicek email yang
    // bentrok itu tetap cuma satu baris dan tidak jadi dua.
    expect(User::query()->where('email', 'ahmad.fauzi@kampus.ac.id')->count())->toBe(1);
});

test('form tambah pengguna memvalidasi input wajib dan panjang password', function () {
    $this->from(route('cms.users'))
        ->post(route('cms.users.store'), [
            'name' => '',
            'email' => 'bukan-email',
            'role' => 'Super Admin',
            'password' => 'pendek',
        ])
        ->assertRedirect(route('cms.users'))
        ->assertSessionHasErrors(['name', 'email', 'role', 'password']);
});

/*
| Validasi tambahan yang mengikuti kolom tabel `users`.
*/

test('form tambah pengguna mewajibkan status sesuai kolom di tabel', function () {
    // `status` sudah ada di tabel dan sudah ada di form ubah, jadi form
    // tambah tidak boleh lagi bergantung pada nilai bawaan database.
    $this->from(route('cms.users'))
        ->post(route('cms.users.store'), [
            'name' => 'Dewi Lestari',
            'email' => 'dewi@kampus.ac.id',
            'role' => 'Dosen',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])
        ->assertSessionHasErrors('status');

    $this->assertDatabaseMissing('users', ['email' => 'dewi@kampus.ac.id']);

    $this->from(route('cms.users'))
        ->post(route('cms.users.store'), [
            'name' => 'Dewi Lestari',
            'email' => 'dewi@kampus.ac.id',
            'role' => 'Dosen',
            'status' => 'Dibekukan',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])
        ->assertSessionHasErrors('status');

    $this->assertDatabaseMissing('users', ['email' => 'dewi@kampus.ac.id']);
});

test('form tambah pengguna bisa menyimpan status nonaktif', function () {
    $this->post(route('cms.users.store'), [
        'name' => 'Dewi Lestari',
        'email' => 'dewi@kampus.ac.id',
        'role' => 'Dosen',
        'status' => 'Nonaktif',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'email' => 'dewi@kampus.ac.id',
        'status' => 'Nonaktif',
    ]);
});

test('password harus sama dengan kolom konfirmasi', function () {
    $this->from(route('cms.users'))
        ->post(route('cms.users.store'), [
            'name' => 'Dewi Lestari',
            'email' => 'dewi@kampus.ac.id',
            'role' => 'Dosen',
            'status' => 'Aktif',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia456',
        ])
        ->assertSessionHasErrors('password');

    $this->assertDatabaseMissing('users', ['email' => 'dewi@kampus.ac.id']);

    $this->from(route('cms.users'))
        ->post(route('cms.users.store'), [
            'name' => 'Dewi Lestari',
            'email' => 'dewi@kampus.ac.id',
            'role' => 'Dosen',
            'status' => 'Aktif',
            'password' => 'rahasia123',
        ])
        ->assertSessionHasErrors('password');

    $this->assertDatabaseMissing('users', ['email' => 'dewi@kampus.ac.id']);
});

test('email dan nama dirapikan sebelum disimpan', function () {
    // Spasi pinggir dan huruf besar pada email tidak boleh ikut tersimpan,
    // kalau tidak `Budi@Kampus.ac.id` dan `budi@kampus.ac.id` jadi dua akun.
    $this->post(route('cms.users.store'), [
        'name' => '  Budi Santoso  ',
        'email' => '  BUDI.Santoso@Kampus.ac.id  ',
        'role' => 'Dosen',
        'status' => 'Aktif',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'name' => 'Budi Santoso',
        'email' => 'budi.santoso@kampus.ac.id',
    ]);
});

test('email yang berbeda huruf besar dianggap sudah terdaftar', function () {
    User::factory()->create(['email' => 'budi.santoso@kampus.ac.id']);

    $this->from(route('cms.users'))
        ->post(route('cms.users.store'), [
            'name' => 'Budi Santoso',
            'email' => 'BUDI.Santoso@Kampus.ac.id',
            'role' => 'Dosen',
            'status' => 'Aktif',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])
        ->assertSessionHasErrors('email');

    // Email yang bentrok tidak boleh membuat akun kedua. Email uppercase-nya
    // sendiri tidak pernah tersimpan karena selalu dirapikan jadi huruf kecil.
    expect(User::query()->where('email', 'budi.santoso@kampus.ac.id')->count())->toBe(1)
        ->and(User::query()->where('email', 'BUDI.Santoso@Kampus.ac.id')->exists())->toBeFalse();
});

test('nama harus minimal dua karakter', function () {
    $this->from(route('cms.users'))
        ->post(route('cms.users.store'), [
            'name' => 'A',
            'email' => 'a@kampus.ac.id',
            'role' => 'Dosen',
            'status' => 'Aktif',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])
        ->assertSessionHasErrors('name');

    $this->assertDatabaseMissing('users', ['email' => 'a@kampus.ac.id']);
});

test('form tambah pengguna memuat field status dan konfirmasi password', function () {
    $this->get(route('cms.users.tambah'))
        ->assertOk()
        ->assertSee('name="status"', false)
        ->assertSee('name="password_confirmation"', false)
        ->assertSee('Ulangi Password');
});

test('filter peran pada halaman pengguna', function () {
    User::factory()->create(['name' => 'Ahmad Fauzi', 'role' => 'Admin']);
    User::factory()->create(['name' => 'Siti Nurhaliza', 'role' => 'Dosen']);
    User::factory()->create(['name' => 'Rina Wijaya', 'role' => 'Mahasiswa']);

    $this->get(route('cms.users', ['role' => 'Dosen']))
        ->assertOk()
        ->assertSee('Siti Nurhaliza')
        ->assertDontSee('Ahmad Fauzi')
        ->assertDontSee('Rina Wijaya');

    $this->get(route('cms.users', ['role' => 'Admin']))
        ->assertOk()
        ->assertSee('Ahmad Fauzi')
        ->assertDontSee('Siti Nurhaliza');

    $this->get(route('cms.users', ['role' => '']))
        ->assertOk()
        ->assertSee('Ahmad Fauzi')
        ->assertSee('Siti Nurhaliza')
        ->assertSee('Rina Wijaya');
});

test('filter status pada halaman pengguna', function () {
    User::factory()->create(['name' => 'Ahmad Fauzi', 'status' => 'Aktif']);
    User::factory()->create(['name' => 'Dewi Lestari', 'status' => 'Nonaktif']);

    $this->get(route('cms.users', ['status' => 'Nonaktif']))
        ->assertOk()
        ->assertSee('Dewi Lestari')
        ->assertDontSee('Ahmad Fauzi');

    $this->get(route('cms.users', ['status' => 'Aktif']))
        ->assertOk()
        ->assertSee('Ahmad Fauzi')
        ->assertDontSee('Dewi Lestari');
});

test('filter peran dan status bisa digabung', function () {
    User::factory()->create(['name' => 'Dosen Aktif Satu', 'role' => 'Dosen', 'status' => 'Aktif']);
    User::factory()->create(['name' => 'Admin Aktif Dua', 'role' => 'Admin', 'status' => 'Aktif']);
    User::factory()->create(['name' => 'Dosen Nonaktif Tiga', 'role' => 'Dosen', 'status' => 'Nonaktif']);

    $this->get(route('cms.users', ['role' => 'Dosen', 'status' => 'Aktif']))
        ->assertOk()
        ->assertSee('Dosen Aktif Satu')
        ->assertDontSee('Admin Aktif Dua')
        ->assertDontSee('Dosen Nonaktif Tiga');
});

test('pencarian pada halaman pengguna tetap bisa digabung dengan filter', function () {
    User::factory()->create(['name' => 'Rina Wijaya', 'email' => 'rina@kampus.ac.id', 'role' => 'Mahasiswa']);
    User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@kampus.ac.id', 'role' => 'Dosen']);

    $this->get(route('cms.users', ['q' => 'Rina', 'role' => 'Mahasiswa', 'status' => 'Aktif']))
        ->assertOk()
        ->assertSee('Rina Wijaya')
        ->assertDontSee('Budi Santoso');

    $this->get(route('cms.users', ['q' => 'rina@kampus.ac.id', 'role' => 'Dosen']))
        ->assertOk()
        ->assertSee('Tidak ada pengguna yang cocok dengan filter.');
});

test('dropdown peran dan status dibangun dari data pengguna', function () {
    // Peran di luar daftar baku tetap bisa difilter.
    User::factory()->create(['role' => 'Tutor', 'status' => 'Ditangguhkan']);

    $this->get(route('cms.users'))
        ->assertOk()
        ->assertSee('<option value="Tutor"', false)
        ->assertSee('<option value="Ditangguhkan"', false);
});

test('filter yang tidak dikenal diabaikan, bukan menghasilkan halaman kosong', function () {
    User::factory()->create(['name' => 'Ahmad Fauzi', 'role' => 'Admin']);

    $this->get(route('cms.users', ['role' => 'Super Admin', 'status' => 'Dibekukan']))
        ->assertOk()
        ->assertSee('Ahmad Fauzi');
});

test('halaman pengguna menampilkan badge filter aktif dan tautan reset', function () {
    User::factory()->create(['role' => 'Dosen', 'status' => 'Aktif']);

    $this->get(route('cms.users', ['role' => 'Dosen', 'status' => 'Aktif']))
        ->assertOk()
        ->assertSee('Filter aktif:')
        ->assertSee('Peran Dosen')
        ->assertSee('Status Aktif')
        ->assertSee(route('cms.users'), false);
});

test('form filter pengguna hanya tersubmit lewat tombol terapkan', function () {
    $response = $this->get(route('cms.users'))
        ->assertOk()
        ->assertSee('name="role"', false)
        ->assertSee('name="status"', false)
        ->assertSee('name="q"', false)
        ->assertSee('Terapkan');

    // Dropdown tidak boleh punya listener change yang otomatis submit,
    // perubahan baru berlaku setelah menekan tombol "Terapkan".
    expect($response->getContent())
        ->not->toContain('form.submit()')
        ->not->toContain('data-filter-peran')
        ->not->toContain('data-filter-status');
});

test('pilihan dropdown baru berlaku setelah tombol terapkan ditekan', function () {
    User::factory()->create(['name' => 'Ahmad Fauzi', 'role' => 'Admin']);
    User::factory()->create(['name' => 'Siti Nurhaliza', 'role' => 'Dosen']);

    // Tanpa parameter filter, halaman menampilkan seluruh pengguna.
    $this->get(route('cms.users'))
        ->assertOk()
        ->assertSee('Ahmad Fauzi')
        ->assertSee('Siti Nurhaliza')
        ->assertDontSee('Filter aktif:');

    // Setelah "Terapkan" dikirim sebagai query string, baris tersaring.
    $this->get(route('cms.users', ['role' => 'Dosen']))
        ->assertOk()
        ->assertSee('Siti Nurhaliza')
        ->assertDontSee('Ahmad Fauzi')
        ->assertSee('Filter aktif:');
});

test('tombol tambah pengguna membuka halaman tersendiri, bukan modal', function () {
    // Form tambah punya halaman sendiri seperti bagian tambah lainnya, jadi
    // tombol di halaman daftar harus menautkan ke halaman itu dan modal
    // mengambang tidak boleh ikut terender lagi.
    $html = $this->get(route('cms.users'))->assertOk()->getContent();

    expect($html)->toContain('href="'.route('cms.users.tambah').'"')
        ->and($html)->not->toContain('id="modal-tambah"');
});

test('halaman tambah pengguna mengirim ke endpoint simpan pengguna', function () {
    $this->get(route('cms.users.tambah'))
        ->assertOk()
        ->assertSee(route('cms.users.store'), false);
});

test('modal hapus ikut terender di halaman pengguna', function () {
    // `{{--` yang tidak ditutup akan menelan markup berikutnya sebagai isi
    // komentar, sehingga bagian halaman hilang tanpa error apa pun. Itulah yang
    // sebelumnya membuat modal diam-diam tidak pernah muncul di DOM.
    $html = $this->get(route('cms.users'))->assertOk()->getContent();

    expect($html)->toContain('id="modal-hapus"')
        ->and($html)->toContain('id="delete-user-form"');
});

test('form pada halaman tambah memuat seluruh kolom yang diminta endpoint simpan', function () {
    // Form /tambahuser dan endpoint simpan harus mengirim kolom yang sama.
    // Kalau form kehilangan satu kolom yang menjadi wajib, setiap penyimpanan
    // akan gagal saat validasi.
    $html = $this->get(route('cms.users.tambah'))->assertOk()->getContent();

    preg_match('/<form[^>]*action="'.preg_quote(route('cms.users.store'), '/').'".*?<\/form>/s', $html, $formulir);
    $form = $formulir[0] ?? '';

    expect($form)->not->toBe('');

    foreach (['name', 'email', 'role', 'status', 'password', 'password_confirmation'] as $kolom) {
        expect($form)->toContain('name="'.$kolom.'"');
    }
});

test('data yang dikirim form tambah bisa disimpan tanpa error validasi', function () {
    // Kirim persis kolom yang ada di form tambah, supaya kolom yang diwajibkan
    // di backend tidak luput dari view.
    $this->post(route('cms.users.store'), [
        'name' => 'Dewi Lestari',
        'email' => 'dewi.lestari@kampus.ac.id',
        'role' => 'Dosen',
        'status' => 'Aktif',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success', 'Pengguna berhasil ditambahkan.');

    $this->assertDatabaseHas('users', [
        'email' => 'dewi.lestari@kampus.ac.id',
        'status' => 'Aktif',
    ]);
});

test('pagination pengguna mempertahankan filter yang aktif', function () {
    User::factory()->count(12)->create(['role' => 'Dosen', 'status' => 'Aktif']);
    User::factory()->count(3)->create(['role' => 'Mahasiswa', 'status' => 'Aktif']);

    $this->get(route('cms.users', ['role' => 'Dosen', 'status' => 'Aktif']))
        ->assertOk()
        ->assertSee('role=Dosen', false)
        ->assertSee('status=Aktif', false);
});

test('pengguna yang baru ditambahkan muncul di tabel setelah redirect', function () {
    $this->post(route('cms.users.store'), [
        'name' => 'Rina Wijaya',
        'email' => 'rina.wijaya@kampus.ac.id',
        'role' => 'Mahasiswa',
        'status' => 'Aktif',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ]);

    $this->get(route('cms.users'))
        ->assertOk()
        ->assertSee('Rina Wijaya')
        ->assertSee('rina.wijaya@kampus.ac.id')
        ->assertSee('Pengguna berhasil ditambahkan.');
});
