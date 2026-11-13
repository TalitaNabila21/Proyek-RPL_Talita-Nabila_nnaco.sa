<?php
if (isset($data['data']['data']) && is_array($data['data']['data'])) {
    $data = $data['data']['data'];
} elseif (isset($data['data']) && is_array($data['data'])) {
    $data = $data['data'];
}
$ai = $data['ai'] ?? [];
$otp = $data['otp'] ?? [];
$wa = $data['whatsapp'] ?? [];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
    <div>
        <h6 class="card-category text-muted mb-1">KONFIGURASI SISTEM</h6>
        <h1 class="h2 fw-bold text-cute-dark mb-0 d-flex align-items-center gap-2">
            <span class="icon-pod icon-pod-md icon-pod-blue shadow-xs"><i class="bi bi-sliders2-vertical"></i></span>
            <span>Pengaturan Sistem</span>
        </h1>
        <p class="text-muted small mb-0 mt-1">Kelola konfigurasi model kecerdasan buatan (AI), autentikasi OTP WhatsApp, dan gateway notifikasi toko.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="<?= BASEURL; ?>/NotificationController" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-2">
            <span class="icon-pod icon-pod-xs icon-pod-emerald"><i class="bi bi-journal-text"></i></span>
            <span>Log Notifikasi WA</span>
        </a>
        <a href="<?= BASEURL; ?>/settings" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-2">
            <span class="icon-pod icon-pod-xs icon-pod-purple"><i class="bi bi-palette"></i></span>
            <span>Pengaturan Tema</span>
        </a>
    </div>
</div>

<!-- Alert Box Global -->
<?php if(isset($_SESSION['flash_msg'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info'; ?> alert-dismissible fade show shadow-sm rounded-4 mb-4" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill fs-5"></i>
            <div><?= $_SESSION['flash_msg']; ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<!-- 3 Main Configuration Cards -->
<div class="row g-4 mb-5">
    
    <!-- 1. Konfigurasi AI Model -->
    <div class="col-lg-4 col-md-6">
        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden transition-all card-hover-lift" style="background: var(--bg-card); border-top: 4px solid #6366f1 !important;">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-pod icon-pod-lg icon-pod-circle icon-pod-indigo shadow-sm">
                            <i class="bi bi-robot fs-2"></i>
                        </div>
                        <?php if ($ai['enabled']): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                <i class="bi bi-check-circle-fill me-1"></i> Aktif
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">
                                <i class="bi bi-pause-circle me-1"></i> Nonaktif
                            </span>
                        <?php endif; ?>
                    </div>
                    <h4 class="fw-bold text-cute-dark mb-1">Konfigurasi AI Model</h4>
                    <p class="text-muted small mb-3">Pengaturan AI Assistant shopping bot toko, pemilihan provider (Groq, Gemini, Nvidia), model LLM, dan API Key.</p>
                    
                    <ul class="list-unstyled small mb-4">
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Provider Aktif:</span>
                            <span class="fw-bold text-uppercase badge bg-primary-subtle text-primary"><?= htmlspecialchars($ai['provider']); ?></span>
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Model LLM:</span>
                            <span class="fw-semibold text-truncate" style="max-width: 140px;" title="<?= htmlspecialchars($ai['model']); ?>"><?= htmlspecialchars($ai['model']); ?></span>
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Status Kunci API:</span>
                            <?php if ($ai['has_key']): ?>
                                <span class="text-success fw-bold"><i class="bi bi-key-fill me-1"></i> Terkonfigurasi</span>
                            <?php else: ?>
                                <span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Belum Diisi</span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
                
                <a href="<?= BASEURL; ?>/adminSettings/ai" class="btn btn-primary rounded-pill py-2 w-100 fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm">
                    <i class="bi bi-gear-fill"></i> Kelola AI Model
                </a>
            </div>
        </div>
    </div>

    <!-- 2. Authentication OTP WhatsApp -->
    <div class="col-lg-4 col-md-6">
        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden transition-all card-hover-lift" style="background: var(--bg-card); border-top: 4px solid #10b981 !important;">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-pod icon-pod-lg icon-pod-circle icon-pod-emerald shadow-sm">
                            <i class="bi bi-shield-lock-fill fs-2"></i>
                        </div>
                        <?php if ($otp['enabled']): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                <i class="bi bi-check-circle-fill me-1"></i> Aktif
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1">
                                <i class="bi bi-slash-circle me-1"></i> Nonaktif
                            </span>
                        <?php endif; ?>
                    </div>
                    <h4 class="fw-bold text-cute-dark mb-1">Authentication OTP WhatsApp</h4>
                    <p class="text-muted small mb-3">Parameter keamanan login OTP, batas masa berlaku, jumlah digit, frekuensi kirim ulang, dan rate limit per IP/nomor.</p>
                    
                    <ul class="list-unstyled small mb-4">
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Masa Berlaku:</span>
                            <span class="fw-bold"><?= $otp['expiry']; ?> Menit</span>
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Panjang Kode:</span>
                            <span class="fw-bold"><?= $otp['length']; ?> Digit</span>
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Jeda Kirim Ulang:</span>
                            <span class="fw-bold"><?= $otp['cooldown']; ?> Detik</span>
                        </li>
                    </ul>
                </div>

                <a href="<?= BASEURL; ?>/adminSettings/otp" class="btn btn-success rounded-pill py-2 w-100 fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm">
                    <i class="bi bi-shield-check"></i> Kelola OTP WhatsApp
                </a>
            </div>
        </div>
    </div>

    <!-- 3. Pengaturan Notifikasi WhatsApp -->
    <div class="col-lg-4 col-md-6">
        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden transition-all card-hover-lift" style="background: var(--bg-card); border-top: 4px solid #25d366 !important;">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-pod icon-pod-lg icon-pod-circle icon-pod-success shadow-sm">
                            <i class="bi bi-whatsapp fs-2"></i>
                        </div>
                        <?php if ($wa['connected']): ?>
                            <span class="badge bg-success text-white rounded-pill px-3 py-1">
                                <i class="bi bi-wifi me-1"></i> Terhubung
                            </span>
                        <?php elseif ($wa['configured']): ?>
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1">
                                <i class="bi bi-wifi-off me-1"></i> Perangkat Terputus
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger text-white rounded-pill px-3 py-1">
                                <i class="bi bi-x-circle me-1"></i> Belum Dikonfigurasi
                            </span>
                        <?php endif; ?>
                    </div>
                    <h4 class="fw-bold text-cute-dark mb-1">Notifikasi WhatsApp</h4>
                    <p class="text-muted small mb-3">Integrasi gateway Fonnte API, pairing QR Code WhatsApp, nomor tujuan notifikasi pesanan, dan kuota pesan.</p>
                    
                    <ul class="list-unstyled small mb-4">
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Nomor Gateway:</span>
                            <span class="fw-semibold font-monospace"><?= htmlspecialchars($wa['device_phone']); ?></span>
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Nomor Owner:</span>
                            <span class="fw-semibold font-monospace"><?= !empty($wa['owner_phone']) ? htmlspecialchars($wa['owner_phone']) : '<span class="text-muted fst-italic">Belum diset</span>'; ?></span>
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Sisa Kuota:</span>
                            <span class="fw-bold text-success"><?= number_format((int)$wa['quota'], 0, ',', '.'); ?> pesan</span>
                        </li>
                    </ul>
                </div>

                <a href="<?= BASEURL; ?>/adminSettings/whatsapp" class="btn btn-outline-success rounded-pill py-2 w-100 fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm">
                    <i class="bi bi-sliders"></i> Kelola Gateway & Notifikasi
                </a>
            </div>
        </div>
    </div>

</div>

<!-- System Status & Security Information -->
<div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--bg-card);">
    <div class="card-header border-0 bg-transparent py-3 px-4">
        <h5 class="fw-bold mb-0 text-cute-dark d-flex align-items-center gap-2">
            <i class="bi bi-shield-check text-primary"></i> Standar Arsitektur & Keamanan Pengaturan
        </h5>
    </div>
    <div class="card-body px-4 pb-4 pt-1">
        <div class="row g-3 text-muted small">
            <div class="col-md-4">
                <div class="p-3 rounded-3" style="background: var(--bg-card-subtle); border: 1px solid var(--border-color);">
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-database-check text-success me-1"></i> Persistensi Database</div>
                    Semua pengaturan tersimpan secara atomik pada tabel <code>settings</code> dan langsung memengaruhi alur backend tanpa restart server.
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-3" style="background: var(--bg-card-subtle); border: 1px solid var(--border-color);">
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-eye-slash text-info me-1"></i> Proteksi Kunci Rahasia</div>
                    API Key AI dan Token Fonnte dimasking saat ditampilkan, dilindungi CSRF token, dan tidak pernah dibocorkan ke log publik.
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-3" style="background: var(--bg-card-subtle); border: 1px solid var(--border-color);">
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-lightning-charge text-warning me-1"></i> Uji Coba Real-Time (Live)</div>
                    Setiap modul pengaturan dilengkapi fitur uji coba koneksi langsung (AJAX) untuk memverifikasi respons API sebelum dipakai pelanggan.
                </div>
            </div>
        </div>
    </div>
</div>
