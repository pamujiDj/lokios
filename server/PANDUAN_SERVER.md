# Panduan: Website Ujian Hanya Bisa Dibuka Lewat ILOK

Ya — agar siswa **tidak bisa** mengerjakan di luar aplikasi, website CBT harus ikut memeriksanya.
Aplikasi ILOK (Android maupun iOS) menempelkan tanda pengenal rahasia pada setiap permintaan ke server; server menolak yang tidak punya.

## Cara kerja
Setiap permintaan dari ILOK membawa tanda di User-Agent:
`ILOK/1.1 t=<jendela waktu> k=<HMAC> d=<ID perangkat> v=<jumlah pelanggaran>`
- `k` dihitung dari **kunci rahasia + waktu + ID perangkat**, berganti tiap 10 menit → tidak bisa disalin ke browser lain.
- `d` = ID perangkat → guru dapat melihat satu HP dipakai berapa siswa.
- `v` = jumlah pelanggaran siswa saat itu → tercatat di server.

## Langkah (website PHP)
1. Ubah `Config.SECRET` di aplikasi (`app/.../Config.java`) menjadi teks acak panjang.
2. Isi `ILOK_SECRET` di `ilok_guard.php` dengan teks yang **sama persis**.
3. Upload `ilok_guard.php` ke folder utama website (sejajar `index.php`).
4. Di baris pertama `index.php` (setelah `<?php`) tambahkan: `require __DIR__ . '/ilok_guard.php';`
5. **Uji coba dulu** dengan `ILOK_ENFORCE = false` (mode pantau): lihat `ilok_log.txt`, pastikan akses dari ILOK tidak tercatat sebagai TANPA-APLIKASI.
6. Setelah yakin, ubah `ILOK_ENFORCE = true`.
7. Isi `ILOK_ALLOW_PATHS` / `ILOK_ALLOW_IPS` agar halaman **admin/guru** tetap bisa dibuka dengan browser biasa.
   Periksa dulu alamat panel admin CBT-SPENNEMA Anda (mis. `/admin`).

## Mendeteksi siswa yang tidak memakai aplikasi
- Mode pantau / blokir: semua percobaan akses tanpa ILOK dicatat di `ilok_log.txt` (IP, waktu, jenis browser).
- Pelanggaran siswa dicatat dengan baris `PELANGGARAN ... device=... v=...`.
- Variabel `$GLOBALS['ILOK_INFO']` (device & violations) bisa disimpan ke database saat siswa login
  bila Anda ingin laporan per siswa — hubungkan dengan nomor ujian/NIS sesuai struktur CBT Anda.

## Penting / batasan
- Jam HP harus akurat (otomatis). Jika jam HP salah >10–20 menit, token dianggap kedaluwarsa.
- Jika `Config.SECRET` bocor, siswa mahir dapat memalsukan tanda pengenal. Ganti kunci (di aplikasi **dan** server)
  tiap periode ujian, lalu minta siswa memperbarui aplikasi.
- Jika server bukan PHP (Node, Python, Nginx saja), logika yang sama dapat dibuat: baca User-Agent,
  hitung `HMAC-SHA256(SECRET, t + "|" + d)` lalu bandingkan 16 karakter pertama dengan `k`.
- Tidak ada sistem anti-curang yang 100% (siswa bisa memakai HP kedua/orang lain). ILOK menutup celah umum
  di HP yang dipakai ujian; pengawasan di ruangan tetap diperlukan.
