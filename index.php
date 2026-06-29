<?php require_once __DIR__.'/includes/header.php'; ?>
<div class="container px-3 px-lg-4">
    <section class="hero p-4 p-lg-5 my-2">
        <div class="row align-items-center g-4 position-relative">
            <div class="col-lg-8">
                <span class="badge rounded-pill text-bg-light text-primary mb-3"><i class="bi bi-shield-check me-1"></i> Sistem Pakar Forward Chaining</span>
                <h1 class="display-5 fw-bold mb-3">Pantau potensi banjir dengan dashboard yang lebih visual.</h1>
                <p class="lead mb-4">Antarmuka baru mengutamakan tampilan glassmorphism, navigasi ringkas, dan informasi risiko yang cepat dibaca tanpa mengubah alur PHP Native + MySQL.</p>
                <div class="metric-ribbon mb-4">
                    <div class="metric-chip"><strong>7</strong><span>Variabel</span></div>
                    <div class="metric-chip"><strong>22</strong><span>Gejala</span></div>
                    <div class="metric-chip"><strong>34</strong><span>Rule</span></div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-light btn-lg" href="<?=app_url('konsultasi/index.php')?>"><i class="bi bi-play-circle me-2"></i>Mulai Konsultasi</a>
                    <a class="btn btn-outline-light btn-lg" href="#alur"><i class="bi bi-diagram-3 me-2"></i>Lihat Alur</a>
                </div>
            </div>
            <div class="col-lg-4 text-center"><div class="hero-visual"><i class="bi bi-cloud-lightning-rain"></i></div><p class="small mt-3 mb-0 opacity-75">Risk intelligence • Trace transparan • Responsif</p></div>
        </div>
    </section>
    <section id="fitur" class="row g-3 mt-2">
        <?php $features=[['bi-cpu','Forward Chaining','Mencocokkan fakta pengguna dengan rule aktif secara berurutan dan terukur.'],['bi-database-check','Transparan','Menampilkan working memory, active rules, failed rules, dan inference trace.'],['bi-stars','UI Baru','Visual glass card, gradient air, tabel modern, dan dark mode yang tetap nyaman.']]; foreach($features as $f): ?>
        <div class="col-md-4"><div class="card stat-card h-100"><div class="stat-icon mb-3"><i class="bi <?=$f[0]?>"></i></div><h5 class="fw-bold"><?=$f[1]?></h5><p class="text-muted mb-0"><?=$f[2]?></p></div></div>
        <?php endforeach; ?>
    </section>

    <section id="alur" class="card flow-section mt-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-4">
                <span class="badge rounded-pill text-bg-primary-subtle text-primary mb-3"><i class="bi bi-signpost-2 me-1"></i> Alur Aplikasi</span>
                <h2 class="section-title mb-3">Lihat alur konsultasi dari awal sampai hasil.</h2>
                <p class="text-muted mb-4">Ikuti tahapan sistem pakar: pengguna masuk, memilih gejala pada setiap variabel, mesin forward chaining mencocokkan rule, lalu hasil diagnosis dan rekomendasi ditampilkan.</p>
                <a class="btn btn-primary" href="<?=app_url('konsultasi/index.php')?>"><i class="bi bi-play-circle me-2"></i>Coba Sekarang</a>
            </div>
            <div class="col-lg-8">
                <?php $flowSteps=[
                    ['bi-box-arrow-in-right','1. Login / Daftar','Masuk sebagai pengguna agar konsultasi dan riwayat tersimpan.'],
                    ['bi-ui-checks-grid','2. Pilih Gejala','Isi 7 variabel kondisi lingkungan sesuai lokasi yang dianalisis.'],
                    ['bi-diagram-3','3. Inferensi Rule','Forward chaining membentuk working memory, mengecek 34 rule, dan mencatat trace.'],
                    ['bi-clipboard2-pulse','4. Hasil Diagnosis','Sistem menampilkan potensi banjir, penjelasan, rekomendasi, serta laporan.']
                ]; foreach($flowSteps as $i=>$step): ?>
                <div class="flow-step">
                    <div class="flow-icon"><i class="bi <?=$step[0]?>"></i></div>
                    <div>
                        <h5 class="fw-bold mb-1"><?=$step[1]?></h5>
                        <p class="text-muted mb-0"><?=$step[2]?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>
<?php require_once __DIR__.'/includes/footer.php'; ?>
