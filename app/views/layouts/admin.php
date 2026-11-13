<!DOCTYPE html>
<html lang="id" data-theme="light" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['data']['judul'] ?? 'Admin Panel' ?> | nnaco_sa</title>

    <!-- Google Fonts: Poppins & Plus Jakarta Sans (Signature White Dashboard Typography) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom Style, White Dashboard Theme & Chat CSS -->
    <link rel="stylesheet" href="<?= BASEURL; ?>/css/style.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASEURL; ?>/css/white-dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASEURL; ?>/css/chat.css?v=<?= time(); ?>">

    <!-- Early Theme Initializer -->
    <script>
        (function() {
            const savedMode = localStorage.getItem('nnaco_theme_mode');
            let theme = 'light';
            if (savedMode === 'system') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            } else if (savedMode) {
                theme = savedMode;
            } else {
                theme = localStorage.getItem('nnaco_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            }
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.setAttribute('data-bs-theme', theme);
            document.documentElement.classList.toggle('white-content', theme === 'light');
            document.documentElement.classList.toggle('theme-dark', theme === 'dark');

            const savedFontSize = localStorage.getItem('nnaco_font_size');
            if (savedFontSize === 'medium') {
                document.documentElement.style.fontSize = '17px';
            } else if (savedFontSize === 'large') {
                document.documentElement.style.fontSize = '18.5px';
            }
        })();
    </script>

    <style>
        body { font-size: .875rem; }
        .sidebar { min-height: 100vh; }
        .sidebar-sticky { position: sticky; top: 0; height: calc(100vh - 48px); padding-top: .5rem; overflow-x: hidden; overflow-y: auto; }
    </style>
</head>
<body class="white-dashboard-layout">
    
<?php 
    $dashUrl = BASEURL . '/admin';
    if(isset($_SESSION['user_role'])) {
        if($_SESSION['user_role'] == 'owner') {
            $dashUrl = BASEURL . '/owner';
        } elseif($_SESSION['user_role'] == 'operator') {
            $dashUrl = BASEURL . '/operator';
        }
    }
?>
<header class="navbar navbar-custom sticky-top flex-md-nowrap p-2 px-3 shadow-sm border-bottom">
  <div class="d-flex align-items-center gap-2">
    <button class="navbar-toggler d-md-none collapsed border-0 p-1" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand-custom fw-bold fs-5 me-0" href="<?= $dashUrl; ?>">
      <span class="icon-pod icon-pod-xs icon-pod-pink me-2 shadow-xs"><i class="bi bi-grid-1x2-fill"></i></span>
      <span>NNACO_SA PANEL</span>
    </a>
  </div>
  
  <div class="navbar-nav d-flex flex-row justify-content-end align-items-center gap-2 px-2 ms-auto">
    <!-- Notification Bell with Quick Badge -->
    <a href="<?= BASEURL; ?>/NotificationController" class="btn btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center text-muted border-0 position-relative" title="Log WhatsApp & Notifikasi" style="width: 36px; height: 36px;">
      <i class="bi bi-bell fs-5"></i>
      <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="transform: translate(-10px, 8px) !important;"></span>
    </a>

    <!-- Theme Switcher Button in Header -->
    <button class="theme-toggle-btn me-1" id="themeToggleBtn" type="button" title="Ganti Tema (Gelap / Terang)">
      <i class="bi bi-moon-stars-fill"></i>
      <i class="bi bi-sun-fill"></i>
    </button>

    <!-- User Profile Dropdown Pill -->
    <div class="dropdown me-1">
      <a class="nav-link dropdown-toggle d-inline-flex align-items-center gap-2 py-1 px-2 rounded-pill border" style="font-size: 0.82rem; background: var(--wd-surface);" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="icon-pod icon-pod-xs icon-pod-circle text-white fw-bold shadow-xs" style="background: var(--wd-primary-grad); width: 24px; height: 24px; font-size: 0.65rem;">
          <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
        </span>
        <span class="fw-semibold d-none d-sm-inline"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-4 border mt-2">
        <li class="px-3 py-2 border-bottom">
          <p class="mb-0 small fw-bold"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></p>
          <small class="text-muted text-uppercase" style="font-size: 0.7rem;"><?= $_SESSION['user_role'] ?? 'Admin'; ?> Panel</small>
        </li>
        <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASEURL; ?>/settings"><i class="bi bi-palette"></i> Pengaturan Tema</a></li>
        <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASEURL; ?>/settings#password-section"><i class="bi bi-shield-lock"></i> Ubah Password</a></li>
        <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASEURL; ?>"><i class="bi bi-shop"></i> Ke Halaman Toko</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger py-2 d-flex align-items-center gap-2" href="<?= BASEURL; ?>/auth/logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>

    <!-- Prominent Logout Button with Icon -->
    <div class="nav-item text-nowrap d-none d-md-block">
      <a class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1 d-inline-flex align-items-center gap-2 fw-bold shadow-sm" style="font-size: 0.78rem;" href="<?= BASEURL; ?>/auth/logout" title="Keluar dari sistem">
        <i class="bi bi-box-arrow-right"></i> <span>Logout</span>
      </a>
    </div>
  </div>
</header>

<div class="container-fluid">
  <div class="row">
    <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
      <div class="position-sticky sidebar-sticky mt-2">
        <!-- White Dashboard Mini Brand Badge in Sidebar -->
        <div class="p-3 text-center border-bottom mb-2 d-none d-md-block" style="border-color: var(--wd-sidebar-border) !important;">
          <div class="icon-pod icon-pod-md icon-pod-circle mx-auto mb-2 shadow-xs" style="background: var(--wd-primary-grad); color: #fff;">
            <i class="bi bi-stars fs-5"></i>
          </div>
          <h6 class="fw-bold mb-0" style="letter-spacing: 0.5px; font-size: 0.85rem;">WHITE DASHBOARD</h6>
          <small class="text-muted text-uppercase" style="font-size: 0.68rem; letter-spacing: 1px;"><?= ucfirst($_SESSION['user_role'] ?? 'Admin'); ?> PORTAL</small>
        </div>
        <ul class="nav flex-column">
          <li class="nav-item">
            <?php 
                $dashboardUrl = BASEURL . '/admin';
                if(isset($_SESSION['user_role'])) {
                    if($_SESSION['user_role'] == 'owner') {
                        $dashboardUrl = BASEURL . '/owner';
                    } elseif($_SESSION['user_role'] == 'operator') {
                        $dashboardUrl = BASEURL . '/operator';
                    }
                }
            ?>
            <a class="nav-link <?= (!isset($_GET['status']) && strpos($_SERVER['REQUEST_URI'], 'adminSettings') === false && strpos($_SERVER['REQUEST_URI'], 'AdminProduct') === false && strpos($_SERVER['REQUEST_URI'], 'AdminCategory') === false && strpos($_SERVER['REQUEST_URI'], 'AdminOrder') === false && strpos($_SERVER['REQUEST_URI'], 'AdminUser') === false && strpos($_SERVER['REQUEST_URI'], 'OwnerAdmin') === false && strpos($_SERVER['REQUEST_URI'], 'Notification') === false && strpos($_SERVER['REQUEST_URI'], 'settings') === false) ? 'active' : ''; ?> d-flex align-items-center gap-2" aria-current="page" href="<?= $dashboardUrl; ?>">
              <span class="sidebar-icon-pod icon-pod-blue"><i class="bi bi-speedometer2"></i></span>
              <span class="fw-semibold">Dashboard</span>
            </a>
          </li>
            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'owner') : ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?= (strpos($_SERVER['REQUEST_URI'], 'AdminUser') !== false) ? 'active' : ''; ?>" href="<?= BASEURL; ?>/AdminUserController">
                      <span class="sidebar-icon-pod icon-pod-indigo"><i class="bi bi-people-fill"></i></span>
                      <span class="fw-semibold">Manajemen Pengguna</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?= (strpos($_SERVER['REQUEST_URI'], 'OwnerAdmin') !== false) ? 'active' : ''; ?>" href="<?= BASEURL; ?>/OwnerAdminController">
                      <span class="sidebar-icon-pod icon-pod-purple"><i class="bi bi-person-badge-fill"></i></span>
                      <span class="fw-semibold">Kelola Admin</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') : ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?= (strpos($_SERVER['REQUEST_URI'], 'AdminUser') !== false) ? 'active' : ''; ?>" href="<?= BASEURL; ?>/AdminUserController">
                      <span class="sidebar-icon-pod icon-pod-indigo"><i class="bi bi-people-fill"></i></span>
                      <span class="fw-semibold">Manajemen Pengguna</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?= (strpos($_SERVER['REQUEST_URI'], 'AdminCategory') !== false) ? 'active' : ''; ?>" href="<?= BASEURL; ?>/AdminCategoryController">
                      <span class="sidebar-icon-pod icon-pod-amber"><i class="bi bi-tags-fill"></i></span>
                      <span class="fw-semibold">Kategori Produk</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?= (strpos($_SERVER['REQUEST_URI'], 'AdminProduct') !== false) ? 'active' : ''; ?>" href="<?= BASEURL; ?>/AdminProductController">
                      <span class="sidebar-icon-pod icon-pod-pink"><i class="bi bi-bag-fill"></i></span>
                      <span class="fw-semibold">Manajemen Produk</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?= (strpos($_SERVER['REQUEST_URI'], 'AdminOrder') !== false) ? 'active' : ''; ?>" href="<?= BASEURL; ?>/AdminOrderController">
                      <span class="sidebar-icon-pod icon-pod-indigo"><i class="bi bi-receipt"></i></span>
                      <span class="fw-semibold">Semua Pesanan</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'operator') : ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?= (strpos($_SERVER['REQUEST_URI'], 'OperatorOrder') !== false) ? 'active' : ''; ?>" href="<?= BASEURL; ?>/OperatorOrderController">
                      <span class="sidebar-icon-pod icon-pod-emerald"><i class="bi bi-inbox-fill"></i></span>
                      <span class="fw-semibold">Kelola Pesanan Masuk</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if(isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'owner'])) : ?>
                <?php 
                    $isSettingsUri = (strpos($_SERVER['REQUEST_URI'], 'adminSettings') !== false || strpos($_SERVER['REQUEST_URI'], 'AdminSettingsController') !== false);
                    $isAiActive = (strpos($_SERVER['REQUEST_URI'], '/ai') !== false);
                    $isOtpActive = (strpos($_SERVER['REQUEST_URI'], '/otp') !== false);
                    $isWaActive = (strpos($_SERVER['REQUEST_URI'], '/whatsapp') !== false);
                ?>
                <!-- Menu Pengaturan Sistem dengan Submenu -->
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center justify-content-between <?= $isSettingsUri ? 'active text-primary fw-bold' : ''; ?>" 
                       data-bs-toggle="collapse" href="#menuPengaturanSistem" role="button" 
                       aria-expanded="<?= $isSettingsUri ? 'true' : 'false'; ?>" aria-controls="menuPengaturanSistem">
                      <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-icon-pod icon-pod-blue"><i class="bi bi-sliders2-vertical"></i></span>
                        <span class="fw-semibold">Pengaturan Sistem</span>
                      </span>
                      <i class="bi bi-chevron-down small transition-all"></i>
                    </a>
                    <div class="collapse <?= $isSettingsUri ? 'show' : ''; ?>" id="menuPengaturanSistem">
                      <ul class="nav flex-column ms-3 mt-1 pb-1 border-start ps-2">
                        <li class="nav-item">
                          <a class="nav-link py-1 d-flex align-items-center gap-2 <?= $isAiActive ? 'text-primary fw-bold' : 'text-muted'; ?>" href="<?= BASEURL; ?>/adminSettings/ai" style="font-size: 0.84rem;">
                            <span class="icon-pod icon-pod-xs icon-pod-indigo"><i class="bi bi-robot"></i></span>
                            <span>Konfigurasi AI Model</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link py-1 d-flex align-items-center gap-2 <?= $isOtpActive ? 'text-success fw-bold' : 'text-muted'; ?>" href="<?= BASEURL; ?>/adminSettings/otp" style="font-size: 0.84rem;">
                            <span class="icon-pod icon-pod-xs icon-pod-emerald"><i class="bi bi-shield-lock-fill"></i></span>
                            <span>Authentication OTP WA</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link py-1 d-flex align-items-center gap-2 <?= $isWaActive ? 'text-success fw-bold' : 'text-muted'; ?>" href="<?= BASEURL; ?>/adminSettings/whatsapp" style="font-size: 0.84rem;">
                            <span class="icon-pod icon-pod-xs icon-pod-emerald"><i class="bi bi-whatsapp"></i></span>
                            <span>Pengaturan Notifikasi WA</span>
                          </a>
                        </li>
                      </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?= (strpos($_SERVER['REQUEST_URI'], 'NotificationController') !== false) ? 'active' : ''; ?>" href="<?= BASEURL; ?>/NotificationController">
                      <span class="sidebar-icon-pod icon-pod-emerald"><i class="bi bi-journal-text"></i></span>
                      <span class="fw-semibold">Log Riwayat WhatsApp</span>
                    </a>
                </li>
            <?php endif; ?>

          <li class="nav-item mt-3 pt-3 border-top">
            <a class="nav-link d-flex align-items-center gap-2" href="<?= BASEURL; ?>/settings">
              <span class="sidebar-icon-pod icon-pod-purple"><i class="bi bi-palette-fill"></i></span>
              <span class="fw-semibold">Pengaturan Tema</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-primary d-flex align-items-center gap-2" href="<?= BASEURL; ?>">
              <span class="sidebar-icon-pod icon-pod-blue"><i class="bi bi-house-door-fill"></i></span>
              <span class="fw-semibold">Ke Halaman Utama</span>
            </a>
          </li>
          <li class="nav-item mt-2">
            <a class="nav-link text-danger fw-bold d-flex align-items-center gap-2" href="<?= BASEURL; ?>/auth/logout">
              <span class="sidebar-icon-pod icon-pod-rose"><i class="bi bi-box-arrow-right"></i></span>
              <span>Logout (Keluar)</span>
            </a>
          </li>
        </ul>
      </div>
    </nav>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 mt-4">
      
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= $dashUrl; ?>">Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page"><?= $data['data']['judul'] ?? ''; ?></li>
        </ol>
      </nav>

      <!-- Flash Message -->
      <?php if(isset($_SESSION['flash_msg'])): ?>
          <div class="alert alert-<?= $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
              <?= $_SESSION['flash_msg']; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          <?php 
              unset($_SESSION['flash_msg']); 
              unset($_SESSION['flash_type']);
          ?>
      <?php endif; ?>

      <!-- Specific View -->
      <?php 
          if(isset($data['view'])) {
              require_once '../app/views/' . $data['view'] . '.php';
          }
      ?>
      
    </main>
  </div>
</div>

<!-- Memanggil Component AI Chatbot -->
<?php require '../app/views/components/chat.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= BASEURL; ?>/js/script.js?v=<?= time(); ?>"></script>
<script src="<?= BASEURL; ?>/js/chat.js?v=<?= time(); ?>"></script>
</body>
</html>
