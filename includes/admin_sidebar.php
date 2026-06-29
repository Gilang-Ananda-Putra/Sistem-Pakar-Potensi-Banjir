<aside class="col-lg-3 col-xl-2 sidebar">
    <div class="sidebar-card panel">
        <a class="<?=nav_active('/dashboard/')?>" href="<?=app_url('admin/dashboard/index.php')?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a class="<?=nav_active('variables.php')?>" href="<?=app_url('admin/master/variables.php')?>"><i class="bi bi-sliders"></i> Variabel</a>
        <a class="<?=nav_active('symptoms.php')?>" href="<?=app_url('admin/master/symptoms.php')?>"><i class="bi bi-list-check"></i> Gejala</a>
        <a class="<?=nav_active('rules.php')?>" href="<?=app_url('admin/master/rules.php')?>"><i class="bi bi-diagram-3"></i> Rule</a>
        <a class="<?=nav_active('users.php')?>" href="<?=app_url('admin/master/users.php')?>"><i class="bi bi-people"></i> User</a>
        <a href="<?=app_url('laporan/index.php')?>"><i class="bi bi-file-earmark-text"></i> Laporan</a>
    </div>
</aside>
