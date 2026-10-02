<?php

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('halaman manajemen pengguna menampilkan data dari database', function () {
    $user = User::factory()->create(['name' => 'Ahmad Fauzi']);

    Siswa::factory()->create([
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

test('pengguna baru dapat ditambahkan melalui modal tambah', function () {
    $response = $this->post(route('cms.users.store'), [
        'name' => 'Dewi Lestari',
        'email' => 'dewi.lestari@sekolah.sch.id',
        'role' => 'Guru',
        'status' => 'Aktif',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ]);

    $response
        ->assertRedirect(route('cms.users'))
        ->assertSessionHas('success', 'Pengguna berhasil ditambahkan.');

    $this->assertDatabaseHas('users', [
        'email' => 'dewi.lestari@sekolah.sch.id',
        'role' => 'Guru',
        'status' => 'Aktif',
        'name' => 'Dewi Lestari',
    ]);
});

test('form tambah pengguna menolak email yang sudah terdaftar', function () {
    User::factory()->create(['email' => 'ahmad.fauzi@sekolah.sch.id']);

    $response = $this->from(route('cms.users'))->post(route('cms.users.store'), [
        'name' => 'Ahmad Fauzi Lain',
        'email' => 'ahmad.fauzi@sekolah.sch.id',
        'role' => 'Admin',
        'password' => 'rahasia123',
    ]);

    $response
        ->assertRedirect(route('cms.users'))
        ->assertSessionHasErrors('email');

    $this->assertDatabaseCount('users', 1);
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
            'email' => 'dewi@sekolah.sch.id',
            'role' => 'Guru',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])
        ->assertSessionHasErrors('status');

    $this->assertDatabaseCount('users', 0);

    $this->from(route('cms.users'))
        ->post(route('cms.users.store'), [
            'name' => 'Dewi Lestari',
            'email' => 'dewi@sekolah.sch.id',
            'role' => 'Guru',
            'status' => 'Dibekukan',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])
        ->assertSessionHasErrors('status');

    $this->assertDatabaseCount('users', 0);
});

test('form tambah pengguna bisa menyimpan status nonaktif', function () {
    $this->post(route('cms.users.store'), [
        'name' => 'Dewi Lestari',
        'email' => 'dewi@sekolah.sch.id',
        'role' => 'Guru',
        'status' => 'Nonaktif',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'email' => 'dewi@sekolah.sch.id',
        'status' => 'Nonaktif',
    ]);
});

test('password harus sama dengan kolom konfirmasi', function () {
    $this->from(route('cms.users'))
        ->post(route('cms.users.store'), [
            'name' => 'Dewi Lestari',
            'email' => 'dewi@sekolah.sch.id',
            'role' => 'Guru',
            'status' => 'Aktif',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia456',
        ])
        ->assertSessionHasErrors('password');

    $this->assertDatabaseCount('users', 0);

    $this->from(route('cms.users'))
        ->post(route('cms.users.store'), [
            'name' => 'Dewi Lestari',
            'email' => 'dewi@sekolah.sch.id',
            'role' => 'Guru',
            'status' => 'Aktif',
            'password' => 'rahasia123',
        ])
        ->assertSessionHasErrors('password');

    $this->assertDatabaseCount('users', 0);
});

test('email dan nama dirapikan sebelum disimpan', function () {
    // Spasi pinggir dan huruf besar pada email tidak boleh ikut tersimpan,
    // kalau tidak `Budi@Sekolah.ID` dan `budi@sekolah.id` jadi dua akun.
    $this->post(route('cms.users.store'), [
        'name' => '  Budi Santoso  ',
        'email' => '  BUDI.Santoso@Sekolah.ID  ',
        'role' => 'Guru',
        'status' => 'Aktif',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'name' => 'Budi Santoso',
        'email' => 'budi.santoso@sekolah.id',
    ]);
});

test('email yang berbeda huruf besar dianggap sudah terdaftar', function () {
    User::factory()->create(['email' => 'budi.santoso@sekolah.id']);

    $this->from(route('cms.users'))
        ->post(route('cms.users.store'), [
            'name' => 'Budi Santoso',
            'email' => 'BUDI.Santoso@Sekolah.ID',
            'role' => 'Guru',
            'status' => 'Aktif',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])
        ->assertSessionHasErrors('email');

    $this->assertDatabaseCount('users', 1);
});

test('nama harus minimal dua karakter', function () {
    $this->from(route('cms.users'))
        ->post(route('cms.users.store'), [
            'name' => 'A',
            'email' => 'a@sekolah.sch.id',
            'role' => 'Guru',
            'status' => 'Aktif',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])
        ->assertSessionHasErrors('name');

    $this->assertDatabaseCount('users', 0);
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
    User::factory()->create(['name' => 'Siti Nurhaliza', 'role' => 'Guru']);
    User::factory()->create(['name' => 'Rina Wijaya', 'role' => 'Siswa']);

    $this->get(route('cms.users', ['role' => 'Guru']))
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
    User::factory()->create(['name' => 'Guru Aktif Satu', 'role' => 'Guru', 'status' => 'Aktif']);
    User::factory()->create(['name' => 'Admin Aktif Dua', 'role' => 'Admin', 'status' => 'Aktif']);
    User::factory()->create(['name' => 'Guru Nonaktif Tiga', 'role' => 'Guru', 'status' => 'Nonaktif']);

    $this->get(route('cms.users', ['role' => 'Guru', 'status' => 'Aktif']))
        ->assertOk()
        ->assertSee('Guru Aktif Satu')
        ->assertDontSee('Admin Aktif Dua')
        ->assertDontSee('Guru Nonaktif Tiga');
});

test('pencarian pada halaman pengguna tetap bisa digabung dengan filter', function () {
    User::factory()->create(['name' => 'Rina Wijaya', 'email' => 'rina@sekolah.sch.id', 'role' => 'Siswa']);
    User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@sekolah.sch.id', 'role' => 'Guru']);

    $this->get(route('cms.users', ['q' => 'Rina', 'role' => 'Siswa', 'status' => 'Aktif']))
        ->assertOk()
        ->assertSee('Rina Wijaya')
        ->assertDontSee('Budi Santoso');

    $this->get(route('cms.users', ['q' => 'rina@sekolah.sch.id', 'role' => 'Guru']))
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
    User::factory()->create(['role' => 'Guru', 'status' => 'Aktif']);

    $this->get(route('cms.users', ['role' => 'Guru', 'status' => 'Aktif']))
        ->assertOk()
        ->assertSee('Filter aktif:')
        ->assertSee('Peran Guru')
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
    User::factory()->create(['name' => 'Siti Nurhaliza', 'role' => 'Guru']);

    // Tanpa parameter filter, halaman menampilkan seluruh pengguna.
    $this->get(route('cms.users'))
        ->assertOk()
        ->assertSee('Ahmad Fauzi')
        ->assertSee('Siti Nurhaliza')
        ->assertDontSee('Filter aktif:');

    // Setelah "Terapkan" dikirim sebagai query string, baris tersaring.
    $this->get(route('cms.users', ['role' => 'Guru']))
        ->assertOk()
        ->assertSee('Siti Nurhaliza')
        ->assertDontSee('Ahmad Fauzi')
        ->assertSee('Filter aktif:');
});

test('modal tambah pengguna mengirim ke endpoint simpan pengguna', function () {
    $this->get(route('cms.users'))
        ->assertOk()
        ->assertSee(route('cms.users.store'), false);
});

test('tombol tambah pengguna benar-benar terhubung ke modalnya', function () {
    // Tombol memakai `data-modal-open`, jadi id yang ditujunya harus benar-benar
    // ada di halaman. Kalau modal hilang dari DOM, tombolnya diam-diam tidak
    // melakukan apa-apa karena `getElementById` mengembalikan null.
    $html = $this->get(route('cms.users'))->assertOk()->getContent();

    preg_match('/data-modal-open="([^"]+)"/', $html, $tombol);

    expect($tombol)->not->toBeEmpty();

    $idModal = $tombol[1];

    expect($html)->toContain('id="'.$idModal.'"');
});

test('modal tambah dan modal hapus ikut terender di halaman pengguna', function () {
    // `{{--` yang tidak ditutup akan menelan markup berikutnya sebagai isi
    // komentar, sehingga bagian halaman hilang tanpa error apa pun. Itulah yang
    // membuat modal tambah diam-diam tidak pernah muncul di DOM.
    $html = $this->get(route('cms.users'))->assertOk()->getContent();

    expect($html)->toContain('id="modal-tambah"')
        ->and($html)->toContain('id="modal-hapus"')
        ->and($html)->toContain('id="tambah-user-title"');
});

test('form pada modal tambah memuat seluruh kolom yang diminta endpoint simpan', function () {
    // Modal dan form /tambahuser harus mengirim kolom yang sama. Kalau modal
    // kehilangan satu kolom yang menjadi wajib, setiap penyimpanan dari /users
    // akan gagal saat validasi.
    $html = $this->get(route('cms.users'))->assertOk()->getContent();

    preg_match('/id="modal-tambah".*?<\/form>/s', $html, $modal);
    $form = $modal[0] ?? '';

    expect($form)->not->toBe('');

    foreach (['name', 'email', 'role', 'status', 'password', 'password_confirmation'] as $kolom) {
        expect($form)->toContain('name="'.$kolom.'"');
    }
});

test('data yang dikirim form modal bisa disimpan tanpa error validasi', function () {
    // Kirim persis kolom yang ada di modal, supaya kolom yang diwajibkan di
    // backend tidak luput dari view.
    $this->post(route('cms.users.store'), [
        'name' => 'Dewi Lestari',
        'email' => 'dewi.lestari@sekolah.sch.id',
        'role' => 'Guru',
        'status' => 'Aktif',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success', 'Pengguna berhasil ditambahkan.');

    $this->assertDatabaseHas('users', [
        'email' => 'dewi.lestari@sekolah.sch.id',
        'status' => 'Aktif',
    ]);
});

test('pagination pengguna mempertahankan filter yang aktif', function () {
    User::factory()->count(12)->create(['role' => 'Guru', 'status' => 'Aktif']);
    User::factory()->count(3)->create(['role' => 'Siswa', 'status' => 'Aktif']);

    $this->get(route('cms.users', ['role' => 'Guru', 'status' => 'Aktif']))
        ->assertOk()
        ->assertSee('role=Guru', false)
        ->assertSee('status=Aktif', false);
});

test('pengguna yang baru ditambahkan muncul di tabel setelah redirect', function () {
    $this->post(route('cms.users.store'), [
        'name' => 'Rina Wijaya',
        'email' => 'rina.wijaya@sekolah.sch.id',
        'role' => 'Siswa',
        'status' => 'Aktif',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ]);

    $this->get(route('cms.users'))
        ->assertOk()
        ->assertSee('Rina Wijaya')
        ->assertSee('rina.wijaya@sekolah.sch.id')
        ->assertSee('Pengguna berhasil ditambahkan.');
});
