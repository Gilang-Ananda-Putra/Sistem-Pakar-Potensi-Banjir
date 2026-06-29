# Sistem Pakar Peringatan Potensi Banjir

Aplikasi PHP Native + MySQL untuk Sistem Pakar Peringatan Potensi Banjir dengan metode Forward Chaining. Project ini mempertahankan knowledge base akademik: 7 variabel, 22 gejala, 34 rule, dan output Potensi Rendah, Sedang, Tinggi, serta Sangat Tinggi.

## Fitur
- Login admin/user dengan role-based access.
- Dashboard statistik dan grafik Chart.js.
- CRUD variabel, knowledge base/gejala, rule base, dan pengguna.
- Konsultasi berbasis 7 variabel dan 22 gejala.
- Mesin inferensi Forward Chaining: working memory, active rules, failed rules, inference trace, explanation, dan recommendation.
- Riwayat konsultasi, filter, print, dan export CSV.
- Proteksi prepared statement, password hash, output escaping, CSRF token, session login, dan pembatasan akses hasil per pemilik data.
- Dokumentasi audit/refactor dan diagram di folder `docs/`.

## Instalasi Laragon/XAMPP
1. Salin folder proyek ke `htdocs` atau `www` dengan nama `Sistem-Pakar-Potensi-Banjir`.
2. Buat database MySQL dan import `database/sistem_pakar_banjir.sql`.
3. Untuk upgrade database lama, jalankan juga `database/2026_06_29_hardening_indexes.sql`.
4. Sesuaikan koneksi lewat environment `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, atau ubah `config/database.php`.
5. Jalankan melalui browser: `http://localhost/Sistem-Pakar-Potensi-Banjir`.

## Akun Awal
- Admin: `admin@banjir.local` / `password`
- User: `user@banjir.local` / `password`

## Struktur Penting
- `config/`: konfigurasi database.
- `includes/`: layout, helper, keamanan, dan engine Forward Chaining.
- `admin/`: dashboard dan CRUD master data.
- `konsultasi/`: form dan proses konsultasi.
- `hasil/`: tampilan hasil diagnosis dan inference trace.
- `laporan/`: riwayat, filter, print, dan export CSV.
- `database/`: schema seed dan migration tambahan.
- `docs/`: dokumentasi audit, flow, ERD/UML/diagram.

## Menambah Rule atau Gejala
Lihat panduan lengkap di `docs/audit-refactor.md` agar penambahan tetap konsisten dengan struktur knowledge base dan rule base akademik.

## Panduan Finalisasi & Demo

Dokumentasi tahap akhir, checklist deployment, skenario demo, dan panduan presentasi tersedia di `docs/finalization-guide.md`.
