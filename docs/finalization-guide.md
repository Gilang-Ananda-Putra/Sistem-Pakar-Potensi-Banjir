# Panduan Finalisasi, Deployment, dan Demo

Dokumen ini menjadi pegangan singkat untuk menjalankan, menguji, mendemokan, dan mengembangkan terbatas Sistem Pakar Peringatan Potensi Banjir tanpa mengubah inti logika akademik.

## 1. Kesiapan Deployment

- Aplikasi berjalan sebagai PHP Native + MySQL dan tidak membutuhkan framework tambahan.
- Konfigurasi database berada di `config/database.php` dan dapat dioverride melalui environment variable `DB_HOST`, `DB_NAME`, `DB_USER`, dan `DB_PASS`.
- Base path aplikasi dihitung otomatis dari `SCRIPT_NAME`. Jika dipasang pada path khusus hosting, set environment `APP_BASE_PATH`, contoh `/Sistem-Pakar-Potensi-Banjir`.
- Asset utama berada di `assets/css/style.css` dan `assets/js/app.js`.
- CDN yang digunakan: Bootstrap, Bootstrap Icons, dan Chart.js. Untuk hosting offline, unduh asset CDN tersebut dan sesuaikan link di `includes/header.php` dan `includes/footer.php`.
- Session login memakai role `admin` dan `user`; admin diarahkan ke dashboard, user diarahkan ke konsultasi.

## 2. Instalasi Singkat

1. Salin folder project ke web root, misalnya `htdocs/Sistem-Pakar-Potensi-Banjir`.
2. Buat database MySQL bernama `sistem_pakar_banjir`.
3. Import `database/sistem_pakar_banjir.sql`.
4. Jika memakai database lama, jalankan `database/2026_06_29_hardening_indexes.sql`.
5. Sesuaikan koneksi database lewat environment atau `config/database.php`.
6. Buka `http://localhost/Sistem-Pakar-Potensi-Banjir`.

## 3. Akun Demo

- Admin: `admin@banjir.local` / `password`
- User: `user@banjir.local` / `password`

## 4. Alur Penggunaan

1. Login atau register sebagai user.
2. Buka menu Konsultasi.
3. Isi lokasi pengamatan.
4. Pilih satu gejala untuk setiap variabel.
5. Klik **Proses Forward Chaining**.
6. Baca hasil diagnosa, working memory, rule aktif, rule gagal, dan timeline inferensi.
7. Gunakan tombol print untuk mencetak hasil.
8. Buka Riwayat untuk melihat, memfilter, mencetak, atau mengekspor data CSV.

## 5. Menambah Data Master

### Menambah Gejala
1. Login sebagai admin.
2. Buka Admin → Knowledge Base.
3. Pilih variabel, isi kode, nama, kategori, dan keterangan.
4. Gunakan pola kode konsisten, contoh `CH1`, `CH2`, atau kode sesuai variabelnya.

### Menambah Rule
1. Login sebagai admin.
2. Buka Admin → Rule Base.
3. Pilih jenis `base` untuk rule dasar atau `modifier` untuk rule penyesuaian.
4. Rule dasar memakai `min_score` dan `max_score` skor inti CH + DH + KD.
5. Rule modifier memakai pilihan gejala IF dan nilai `adjustment`.
6. Jaga prioritas agar 34 rule akademik tetap tidak berubah bila sedang presentasi laporan.

### Menambah Variabel
1. Login sebagai admin.
2. Buka Admin → Variabel.
3. Isi kode, nama, dan deskripsi.
4. Tambahkan minimal satu gejala untuk variabel baru agar form konsultasi tetap valid.
5. Evaluasi ulang rule base jika variabel baru memengaruhi diagnosis.

## 6. Ringkasan Knowledge Base dan Rule Base

- 7 variabel: CH, DH, KD, KW, KS, RB, PD.
- 22 gejala sebagai fakta masukan pengguna.
- 34 rule aktif.
- Output diagnosis: Rendah, Sedang, Tinggi, dan Sangat Tinggi.
- Rule dasar mengevaluasi skor inti CH + DH + KD.
- Rule modifier menaikkan atau menurunkan level risiko dengan floor Rendah dan ceiling Sangat Tinggi.

## 7. Penjelasan Forward Chaining untuk Presentasi

Forward chaining bekerja dari fakta menuju kesimpulan. Sistem menerima fakta gejala yang dipilih pengguna, menyimpannya sebagai working memory, mengevaluasi rule dasar untuk menentukan risiko awal, lalu mengevaluasi rule modifier untuk menyesuaikan risiko akhir. Semua proses disimpan sebagai inference trace agar keputusan sistem dapat dijelaskan ulang kepada penguji.

## 8. Skenario Demo Simulasi

### Skenario A: Risiko Rendah
- Pilih gejala level rendah pada CH, DH, dan KD.
- Pilih kondisi lingkungan yang tidak memperburuk risiko.
- Hasil yang diharapkan: Potensi Rendah atau tetap rendah setelah modifier.
- Narasi demo: sistem menunjukkan lingkungan relatif aman tetapi tetap perlu pemantauan.

### Skenario B: Risiko Sedang/Tinggi
- Pilih curah hujan sedang/tinggi, drainase kurang baik, dan kedekatan sungai menengah.
- Tambahkan kondisi wilayah atau riwayat banjir yang memperkuat risiko.
- Hasil yang diharapkan: Potensi Sedang atau Tinggi sesuai rule yang aktif.
- Narasi demo: sistem menampilkan rule aktif dan rekomendasi kesiapsiagaan.

### Skenario C: Risiko Sangat Tinggi
- Pilih gejala terburuk pada CH, DH, KD, dan kondisi pendukung berisiko.
- Hasil yang diharapkan: Potensi Sangat Tinggi.
- Narasi demo: sistem menyarankan koordinasi dan kesiapan evakuasi.

## 9. Keunggulan Sistem

- Transparan karena menampilkan working memory dan inference trace.
- Mudah dipasang karena berbasis PHP Native + MySQL.
- Role admin/user jelas.
- CRUD knowledge base dan rule base tersedia.
- Riwayat konsultasi dapat dicetak dan diekspor.
- Output dan rekomendasi mudah dipahami saat demo.

## 10. Keterbatasan dan Pengembangan

- Diagnosis mengikuti rule akademik, belum memanfaatkan data sensor real-time.
- Bobot rule masih deterministik, belum probabilistik.
- Akurasi bergantung pada input pengguna.
- Pengembangan berikutnya dapat menambahkan integrasi API cuaca, peta rawan banjir, notifikasi, dan validasi pakar lapangan.

## 11. Checklist Demo

- [ ] Database berhasil diimport.
- [ ] Halaman utama terbuka.
- [ ] Register user berhasil.
- [ ] Login user berhasil.
- [ ] Konsultasi berhasil diproses.
- [ ] Hasil diagnosis tampil.
- [ ] Print hasil berfungsi.
- [ ] Riwayat tersimpan.
- [ ] Login admin berhasil.
- [ ] Dashboard admin terbuka.
- [ ] CRUD variabel, gejala, rule, dan user dapat diakses.
- [ ] Tampilan desktop dan mobile terbaca rapi.
