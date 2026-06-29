<?php require_once __DIR__.'/includes/header.php'; ?>
<div class="container px-3 px-lg-4">
    <section class="hero p-4 p-lg-5 my-2">
        <div class="row align-items-center g-4 position-relative">
            <div class="col-lg-8">
                <span class="badge rounded-pill text-bg-light text-primary mb-3"><i class="bi bi-shield-check me-1"></i> Sistem Pakar Forward Chaining</span>
                <h1 class="display-5 fw-bold mb-3">Peringatan Potensi Banjir yang modern, transparan, dan mudah digunakan.</h1>
                <p class="lead mb-4">Aplikasi akademik berbasis PHP Native + MySQL untuk menganalisis 7 variabel, 22 gejala, dan 34 rule hingga menghasilkan diagnosis RENDAH, SEDANG, TINGGI, atau SANGAT TINGGI.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-light btn-lg" href="<?=app_url('konsultasi/index.php')?>"><i class="bi bi-play-circle me-2"></i>Mulai Konsultasi</a>
                    <a class="btn btn-outline-light btn-lg" href="#fitur"><i class="bi bi-diagram-3 me-2"></i>Lihat Alur</a>
                </div>
            </div>
            <div class="col-lg-4 text-center"><div class="hero-visual"><i class="bi bi-cloud-lightning-rain"></i></div></div>
        </div>
    </section>
    <section id="fitur" class="row g-3 mt-2">
        <?php $features=[['bi-cpu','Forward Chaining','Mencocokkan fakta pengguna dengan rule aktif secara berurutan dan terukur.'],['bi-database-check','Transparan','Menampilkan working memory, active rules, failed rules, dan inference trace.'],['bi-phone','Responsif','Antarmuka bersih dengan card, badge risiko, tabel modern, dan dark mode.']]; foreach($features as $f): ?>
        <div class="col-md-4"><div class="card stat-card h-100"><div class="stat-icon mb-3"><i class="bi <?=$f[0]?>"></i></div><h5 class="fw-bold"><?=$f[1]?></h5><p class="text-muted mb-0"><?=$f[2]?></p></div></div>
        <?php endforeach; ?>
    </section>
</div>
<?php require_once __DIR__.'/includes/footer.php'; ?>
