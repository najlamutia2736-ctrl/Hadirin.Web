<?php

test('halaman depan mengembalikan respons berhasil', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
