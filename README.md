# Sistem Pakar Peringatan Potensi Banjir

Aplikasi PHP Native + MySQL untuk Sistem Pakar Peringatan Potensi Banjir dengan metode Forward Chaining.

## Fitur
- Login admin/user dengan role-based access.
- Dashboard statistik dan grafik Chart.js.
- CRUD variabel, knowledge base/gejala, rule base, dan pengguna.
- Konsultasi berbasis 7 variabel dan 22 gejala.
- Mesin inferensi Forward Chaining murni: working memory, rule aktif/gagal, urutan inferensi, dan hasil diagnosis.
- Riwayat konsultasi, filter, print, export CSV/Excel sederhana.
- Dokumentasi ERD, UML, flowchart, dan flow aplikasi di folder `docs/`.

## Instalasi Laragon/XAMPP
1. Salin folder proyek ke `htdocs` atau `www`.
2. Buat database MySQL dan import `database/sistem_pakar_banjir.sql`.
3. Sesuaikan koneksi di `config/database.php` jika diperlukan.
4. Jalankan melalui browser: `http://localhost/Sistem-Pakar-Potensi-Banjir`.

## Akun Awal
- Admin: `admin@banjir.local` / `password`
- User: `user@banjir.local` / `password`

> Catatan: dataset knowledge base dan 34 rule disediakan dalam SQL seed agar jumlah variabel, gejala, rule, dan output sesuai prompt.
