{{--
    Pustaka + helper pembuat barcode QR untuk seluruh aplikasi.

    Digambar di browser memakai qrcodejs dari CDN, mengikuti pola halaman scan-qr
    yang juga memuat jsQR dari CDN. Tidak ada dependensi npm/composer baru, jadi
    deploy lewat FTP tidak butuh langkah build tambahan.

    @once dipakai supaya walau partial ini disertakan beberapa kali dalam satu
    halaman (mis. tiap baris tabel), skripnya hanya sekali dan tidak menumpuk.
--}}
@once
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>
            /**
             * Gambar barcode QR (berisi NIM mahasiswa) ke dalam sebuah wadah.
             *
             * Wadah yang lama dikosongkan dulu supaya pemanggilan berulang —
             * misalnya saat petugas membuka barcode mahasiswa lain — tidak
             * menumpuk beberapa gambar. Kalau pustaka gagal dimuat dari CDN,
             * NIM tetap ditampilkan sebagai teks supaya barcode tidak lenyap
             * diam-diam.
             *
             * @param {HTMLElement} wadah tempat QR digambar
             * @param {string} teks isi barcode, berupa NIM mahasiswa
             * @param {number} ukuran sisi QR dalam piksel
             * @returns {boolean} true kalau QR berhasil digambar
             */
            window.gambarBarcodeMahasiswa = function (wadah, teks, ukuran) {
                if (!wadah) {
                    return false;
                }

                wadah.innerHTML = '';

                if (!teks) {
                    return false;
                }

                if (typeof window.QRCode === 'undefined') {
                    wadah.textContent = teks;
                    wadah.classList.add('text-sm', 'font-mono', 'font-semibold', 'text-slate-700');

                    return false;
                }

                new window.QRCode(wadah, {
                    text: teks,
                    width: ukuran,
                    height: ukuran,
                    colorDark: '#1e1b4b',
                    colorLight: '#ffffff',
                    correctLevel: window.QRCode.CorrectLevel.M
                });

                return true;
            };

            // Barcode yang sudah ditulis langsung di HTML (mis. milik mahasiswa
            // yang baru ditambahkan) digambar begitu halaman siap.
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-barcode-mahasiswa]').forEach(function (wadah) {
                    window.gambarBarcodeMahasiswa(
                        wadah,
                        wadah.dataset.barcodeTeks || '',
                        parseInt(wadah.dataset.barcodeUkuran, 10) || 240
                    );
                });
            });
        </script>
    @endpush
@endonce
