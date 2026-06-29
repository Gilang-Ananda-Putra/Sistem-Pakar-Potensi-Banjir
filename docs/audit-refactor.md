# Audit & Refactor Sistem Pakar Potensi Banjir

## Ringkasan Perubahan
- Helper aplikasi dipusatkan di `includes/functions.php` untuk base URL, CSRF, otorisasi pemilik data, escaping, dan mesin Forward Chaining.
- Proses konsultasi dibuat transaksional agar konsultasi, detail fakta, hasil diagnosis, working memory, active rules, failed rules, dan inference trace tersimpan konsisten.
- Akses halaman hasil dibatasi: admin dapat melihat semua data, user hanya dapat melihat hasil konsultasinya sendiri.
- Migration indeks ditambahkan untuk mempercepat riwayat, dashboard, evaluasi rule aktif, dan relasi konsultasi.

## Validasi Knowledge Base
Dataset seed utama tetap memakai:
- 7 variabel: CH, DH, KD, KW, KS, RB, PD.
- 22 gejala.
- 34 rule aktif.
- Rule dasar R01-R25 memakai skor inti CH + DH + KD sesuai laporan.
- Rule modifikasi R26-R34 memakai `rule_conditions`, `adjustment`, floor Rendah, dan ceiling Sangat Tinggi.

## Alur Forward Chaining
1. Membaca fakta terpilih dari form konsultasi.
2. Membentuk working memory berupa ID gejala unik.
3. Mengambil fakta detail dan mengelompokkannya per kode variabel.
4. Mengevaluasi rule dasar berdasarkan skor inti CH + DH + KD.
5. Mengevaluasi rule modifikasi berdasarkan kecocokan `rule_conditions`.
6. Menerapkan floor pada level Rendah.
7. Menerapkan ceiling pada level Sangat Tinggi.
8. Menghasilkan diagnosis akhir.
9. Menghasilkan explanation dan recommendation.
10. Menyimpan inference trace dalam JSON.

## Cara Install
1. Salin project ke folder web server, contoh `htdocs/Sistem-Pakar-Potensi-Banjir`.
2. Import `database/sistem_pakar_banjir.sql` ke MySQL.
3. Jika meng-upgrade database lama, jalankan `database/2026_06_29_hardening_indexes.sql`.
4. Sesuaikan credential database melalui environment `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` atau file `config/database.php`.
5. Buka `http://localhost/Sistem-Pakar-Potensi-Banjir`.

## Cara Menambah Gejala Baru
1. Masuk sebagai admin.
2. Buka Admin → Gejala.
3. Pilih variabel, isi kode, nama, kategori, dan deskripsi.
4. Pastikan kode gejala mengikuti pola variabel dan level, misalnya `CH4`.

## Cara Menambah Rule Baru
1. Masuk sebagai admin.
2. Buka Admin → Rule.
3. Untuk rule dasar, pilih `base`, isi diagnosis, prioritas, `min_score`, dan `max_score`.
4. Untuk rule modifikasi, pilih `modifier`, pilih gejala IF, dan isi `adjustment` positif atau negatif.
5. Pastikan prioritas tidak mengganggu urutan 34 rule akademik jika rule laporan harus tetap persis.

## Catatan Kompatibilitas
- Aplikasi tetap PHP Native + MySQL dan mempertahankan struktur URL `/Sistem-Pakar-Potensi-Banjir`.
- Migration tambahan hanya menambah indeks dan unique key; tidak mengubah hasil inferensi.
