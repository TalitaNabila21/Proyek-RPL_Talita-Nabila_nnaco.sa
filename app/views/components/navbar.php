<?php
$navUserId = $_SESSION['user_id'] ?? null;
$navUserRole = !empty($_SESSION['user_role']) ? strtolower(trim($_SESSION['user_role'])) : '';

// Jika user_id ada tapi user_role belum ada di session, sinkronkan dari database
if (!empty($navUserId) && empty($navUserRole)) {
    try {
        $navDb = new Database();
        $navDb->query("SELECT role, name, status FROM users WHERE id = :id LIMIT 1");
        $navDb->bind('id', $navUserId);
        $userRow = $navDb->single();
        if ($userRow && ($userRow['status'] ?? 'active') === 'active') {
            $_SESSION['user_role'] = $userRow['role'];
            if (empty($_SESSION['user_name']) && !empty($userRow['name'])) {
                $_SESSION['user_name'] = $userRow['name'];
            }
            $navUserRole = strtolower(trim($userRow['role']));
        } else {
            unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name']);
            $navUserId = null;
            $navUserRole = '';
        }
    } catch (\Throwable $e) {
        $navUserRole = '';
    }
}

// Tentukan target URL dashboard yang valid dan aman
$navDashboardUrl = BASEURL . '/dashboard';
if (!empty($navUserRole) && in_array($navUserRole, ['admin', 'owner', 'operator', 'customer'])) {
    $navDashboardUrl = BASEURL . '/' . $navUserRole;
}
?>
<nav class="navbar navbar-expand-lg navbar-custom shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand-custom fw-bold" href="<?= BASEURL; ?>">
      <span class="icon-pod icon-pod-sm icon-pod-gradient-pink me-1 shadow-sm"><i class="bi bi-stars"></i></span>
      <span>Nnaco_SA</span>
    </a>
    
    <div class="d-flex align-items-center gap-2 d-lg-none">
      <!-- Mobile Quick Theme Toggle -->
      <button class="theme-toggle-btn" id="themeToggleBtnMobile" type="button" aria-label="Ganti Tema (Gelap / Terang)">
        <i class="bi bi-moon-stars-fill"></i>
        <i class="bi bi-sun-fill"></i>
      </button>

      <?php if (!empty($navUserId)) : ?>
        <!-- Mobile Direct Dashboard Icon -->
        <a href="<?= $navDashboardUrl; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1" title="Buka Dashboard <?= !empty($navUserRole) ? ucfirst($navUserRole) : ''; ?>">
          <i class="bi bi-speedometer2 fs-6"></i>
        </a>
        <!-- Mobile Direct Logout Icon -->
        <a href="<?= BASEURL; ?>/auth/logout" class="btn-logout-mobile" title="Logout (Keluar Akun)">
          <i class="bi bi-box-arrow-right fs-5"></i>
        </a>
      <?php else : ?>
        <!-- Mobile Direct Dashboard / Login Icon -->
        <a href="<?= BASEURL; ?>/dashboard" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1" title="Dashboard">
          <i class="bi bi-speedometer2 fs-6"></i>
        </a>
      <?php endif; ?>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center gap-1">
        <li class="nav-item">
          <a class="nav-link nav-link-custom" aria-current="page" href="<?= BASEURL; ?>">
            <span class="nav-icon-pod icon-pod-blue"><i class="bi bi-house-door-fill"></i></span>
            <span>Home</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-link-custom" href="<?= BASEURL; ?>/product">
            <span class="nav-icon-pod icon-pod-emerald"><i class="bi bi-gem"></i></span>
            <span>Produk</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-link-custom" href="<?= BASEURL; ?>/product">
            <span class="nav-icon-pod icon-pod-amber"><i class="bi bi-grid-fill"></i></span>
            <span>Kategori</span>
          </a>
        </li>

        <!-- Menu Dashboard: Permanen, jelas, dan tidak pernah hilang -->
        <li class="nav-item">
          <?php if (!empty($navUserId)) : ?>
            <a class="nav-link nav-link-custom fw-bold d-inline-flex align-items-center gap-2" href="<?= $navDashboardUrl; ?>" title="Buka Dashboard <?= !empty($navUserRole) ? ucfirst($navUserRole) : ''; ?>">
              <span class="nav-icon-pod icon-pod-blue"><i class="bi bi-speedometer2"></i></span>
              <span>Dashboard</span>
              <?php if (!empty($navUserRole)) : ?>
                <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.72rem;"><?= ucfirst($navUserRole); ?></span>
              <?php endif; ?>
            </a>
          <?php else : ?>
            <a class="nav-link nav-link-custom" href="<?= BASEURL; ?>/dashboard" title="Akses Dashboard">
              <span class="nav-icon-pod icon-pod-blue"><i class="bi bi-speedometer2"></i></span>
              <span>Dashboard</span>
            </a>
          <?php endif; ?>
        </li>

        <li class="nav-item">
          <a class="nav-link nav-link-custom" href="<?= BASEURL; ?>/settings">
            <span class="nav-icon-pod icon-pod-purple"><i class="bi bi-palette2"></i></span>
            <span>Pengaturan</span>
          </a>
        </li>
        <li class="nav-item">
            <a class="nav-link nav-link-custom fw-bold" style="color: #d81b60 !important;" href="<?= BASEURL; ?>/CartController">
                <span class="nav-icon-pod icon-pod-pink"><i class="bi bi-bag-heart-fill <?= isset($_SESSION['cart']) && count($_SESSION['cart']) > 0 ? 'icon-animate-heartbeat' : ''; ?>"></i></span>
                <span>Keranjang</span> 
                <?php 
                    $cart_count = 0;
                    if(isset($_SESSION['cart'])) {
                        foreach($_SESSION['cart'] as $item) {
                            $cart_count += $item['quantity'];
                        }
                    }
                ?>
                <span class="badge rounded-pill bg-danger shadow-sm ms-1"><?= $cart_count; ?></span>
            </a>
        </li>

        <!-- Desktop Direct 1-Click Theme Toggle Button -->
        <li class="nav-item mx-1">
          <button class="theme-toggle-btn" id="themeToggleBtn" type="button" title="Ganti Tema (Gelap / Terang)" aria-label="Ganti Tema">
            <i class="bi bi-moon-stars-fill"></i>
            <i class="bi bi-sun-fill"></i>
          </button>
        </li>
        
        <?php if (!empty($navUserId)) : ?>
            <li class="nav-item ms-lg-1">
                <a class="btn-logout-nav" href="<?= BASEURL; ?>/auth/logout" title="Keluar dari akun Anda">
                    <i class="bi bi-box-arrow-right"></i> <span>Logout</span>
                </a>
            </li>
        <?php else : ?>
            <li class="nav-item ms-lg-1">
                <a class="btn-auth-nav" href="<?= BASEURL; ?>/auth/login" title="Login atau Daftar Akun">
                    <i class="bi bi-person-heart"></i> <span>Login / Daftar</span>
                </a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
