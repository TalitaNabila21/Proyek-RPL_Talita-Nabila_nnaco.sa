<div class="row justify-content-center my-4">
    <div class="col-lg-9 col-md-11">
        
        <!-- Header Pengaturan -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 pb-2 border-bottom" style="border-color: var(--border-color) !important;">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--text-heading-blue);">
                    <i class="bi bi-gear-fill me-2"></i> Pengaturan Tampilan
                </h2>
                <p class="text-secondary small mb-0">
                    Sesuaikan preferensi tema gelap/terang dan kenyamanan visual Anda saat menggunakan aplikasi Nnaco_SA.
                </p>
            </div>
            <div>
                <span class="badge rounded-pill px-3 py-2" style="background: var(--bg-card-subtle); color: var(--text-heading-blue); border: 1px solid var(--border-color); font-size: 0.85rem;">
                    <i class="bi bi-eye-fill me-1"></i> Mode Kenyamanan Visual
                </span>
            </div>
        </div>

        <!-- Section 1: Pilihan Tema Gelap / Terang -->
        <div class="card card-cute mb-4 p-4 p-sm-5 shadow-sm">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: var(--bg-card-subtle); color: var(--text-heading-blue); font-size: 1.5rem; border: 1px solid var(--border-color);">
                    <i class="bi bi-palette2"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0" style="color: var(--text-primary);">Pilih Tema Aplikasi</h4>
                    <p class="text-secondary small mb-0">Pilih tema yang paling nyaman untuk mata Anda saat berbelanja atau mengelola toko</p>
                </div>
            </div>

            <!-- Preview & Selection Cards -->
            <div class="row g-4 mt-1" id="themeSelectorContainer">
                
                <!-- 1. Mode Terang (Light Mode) -->
                <div class="col-md-4">
                    <div class="theme-choice-card h-100 p-3 rounded-4 cursor-pointer" id="cardThemeLight" onclick="selectAppTheme('light')">
                        <div class="theme-preview-box light-preview rounded-3 p-3 mb-3 position-relative overflow-hidden">
                            <!-- Mini mockup UI light -->
                            <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom" style="border-color: #e2e8f0 !important;">
                                <span style="font-size: 0.65rem; font-weight: 700; color: #0288d1;">✨ Nnaco_SA</span>
                                <span class="badge bg-danger rounded-pill" style="font-size: 0.5rem;">Baru</span>
                            </div>
                            <div class="p-2 rounded bg-white shadow-xs mb-2">
                                <div style="height: 6px; width: 60%; background: #0288d1; border-radius: 4px; margin-bottom: 4px;"></div>
                                <div style="height: 5px; width: 40%; background: #f48fb1; border-radius: 4px;"></div>
                            </div>
                            <div class="d-flex gap-1">
                                <div class="rounded-pill px-2 py-0" style="background: #f48fb1; color: white; font-size: 0.55rem; font-weight: bold;">Beli</div>
                                <div class="rounded-pill px-2 py-0" style="border: 1px solid #4fc3f7; color: #0288d1; font-size: 0.55rem;">Detail</div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold mb-0" style="color: var(--text-primary);">
                                <i class="bi bi-sun-fill text-warning me-1"></i> Mode Terang
                            </h6>
                            <span class="active-indicator" id="indicatorLight">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            </span>
                        </div>
                        <p class="text-secondary mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            Nuansa pastel cerah khas cotton candy. Segar dan cocok untuk penggunaan di siang hari.
                        </p>
                    </div>
                </div>

                <!-- 2. Mode Gelap (Dark Mode) -->
                <div class="col-md-4">
                    <div class="theme-choice-card h-100 p-3 rounded-4 cursor-pointer" id="cardThemeDark" onclick="selectAppTheme('dark')">
                        <div class="theme-preview-box dark-preview rounded-3 p-3 mb-3 position-relative overflow-hidden">
                            <!-- Mini mockup UI dark -->
                            <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom" style="border-color: #334155 !important;">
                                <span style="font-size: 0.65rem; font-weight: 700; color: #38bdf8;">✨ Nnaco_SA</span>
                                <span class="badge bg-danger rounded-pill" style="font-size: 0.5rem;">Baru</span>
                            </div>
                            <div class="p-2 rounded mb-2" style="background: #1e293b; border: 1px solid #334155;">
                                <div style="height: 6px; width: 60%; background: #38bdf8; border-radius: 4px; margin-bottom: 4px;"></div>
                                <div style="height: 5px; width: 40%; background: #f472b6; border-radius: 4px;"></div>
                            </div>
                            <div class="d-flex gap-1">
                                <div class="rounded-pill px-2 py-0" style="background: #ec4899; color: white; font-size: 0.55rem; font-weight: bold;">Beli</div>
                                <div class="rounded-pill px-2 py-0" style="border: 1px solid #38bdf8; color: #38bdf8; font-size: 0.55rem;">Detail</div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold mb-0" style="color: var(--text-primary);">
                                <i class="bi bi-moon-stars-fill text-info me-1"></i> Mode Gelap
                            </h6>
                            <span class="active-indicator" id="indicatorDark">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            </span>
                        </div>
                        <p class="text-secondary mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            Nuansa elegan dan redup. Mengurangi ketegangan mata dan sangat nyaman digunakan di malam hari.
                        </p>
                    </div>
                </div>

                <!-- 3. Mode Otomatis / Sistem -->
                <div class="col-md-4">
                    <div class="theme-choice-card h-100 p-3 rounded-4 cursor-pointer" id="cardThemeSystem" onclick="selectAppTheme('system')">
                        <div class="theme-preview-box system-preview rounded-3 p-3 mb-3 position-relative overflow-hidden d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #e0f2fe 50%, #0f172a 50%);">
                            <div class="text-center bg-white dark-bg-adapt px-3 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-circle-half fs-4 text-primary"></i>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold mb-0" style="color: var(--text-primary);">
                                <i class="bi bi-laptop me-1 text-primary"></i> Ikuti Sistem
                            </h6>
                            <span class="active-indicator" id="indicatorSystem">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            </span>
                        </div>
                        <p class="text-secondary mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            Menyesuaikan tema secara otomatis mengikuti pengaturan gelap/terang di laptop, HP, atau tablet Anda.
                        </p>
                    </div>
                </div>

            </div>

            <!-- Manfaat Kenyamanan Visual Box -->
            <div class="mt-4 pt-3 border-top" style="border-color: var(--border-color) !important;">
                <h6 class="fw-bold mb-3" style="color: var(--text-heading-blue);">
                    <i class="bi bi-shield-check me-1"></i> Manfaat Mengatur Tema Sesuai Kenyamanan:
                </h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 rounded-4 h-100" style="background: var(--bg-card-subtle); border: 1px solid var(--border-color);">
                            <div class="fw-bold mb-1" style="font-size: 0.88rem; color: var(--text-primary);">
                                <i class="bi bi-brightness-high text-warning me-1"></i> Mengurangi Silau
                            </div>
                            <div class="text-secondary small">Mode gelap mengurangi paparan cahaya langsung saat membuka aplikasi di ruangan redup.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-4 h-100" style="background: var(--bg-card-subtle); border: 1px solid var(--border-color);">
                            <div class="fw-bold mb-1" style="font-size: 0.88rem; color: var(--text-primary);">
                                <i class="bi bi-heart-pulse text-danger me-1"></i> Mencegah Mata Lelah
                            </div>
                            <div class="text-secondary small">Kontras yang seimbang mempermudah membaca detail produk dan informasi harga.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-4 h-100" style="background: var(--bg-card-subtle); border: 1px solid var(--border-color);">
                            <div class="fw-bold mb-1" style="font-size: 0.88rem; color: var(--text-primary);">
                                <i class="bi bi-battery-charging text-success me-1"></i> Efisiensi Daya
                            </div>
                            <div class="text-secondary small">Lebih hemat konsumsi baterai pada perangkat berlayar OLED atau AMOLED.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Preferensi Ukuran Tampilan & Animasi -->
        <div class="card card-cute mb-4 p-4 p-sm-5 shadow-sm">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: var(--bg-card-subtle); color: var(--text-heading-pink); font-size: 1.5rem; border: 1px solid var(--border-color);">
                    <i class="bi bi-sliders"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0" style="color: var(--text-primary);">Kenyamanan Tambahan</h4>
                    <p class="text-secondary small mb-0">Atur preferensi ukuran font dan efek animasi sesuai kenyamanan Anda</p>
                </div>
            </div>

            <div class="row g-4 mt-1">
                <!-- Ukuran Teks -->
                <div class="col-md-6">
                    <div class="p-3 rounded-4 h-100" style="background: var(--bg-card-subtle); border: 1px solid var(--border-color);">
                        <label class="form-label fw-bold mb-2 text-primary" style="font-size: 0.9rem;">
                            <i class="bi bi-fonts me-1"></i> Ukuran Teks Tampilan:
                        </label>
                        <select class="form-select" id="fontSizeSelector" onchange="changeAppFontSize(this.value)">
                            <option value="normal">Normal (Standar 100%)</option>
                            <option value="medium">Sedang (Lebih Mudah Dibaca 110%)</option>
                            <option value="large">Besar (Ekstra Jelas 120%)</option>
                        </select>
                        <small class="text-muted d-block mt-2" style="font-size: 0.78rem;">
                            Menyesuaikan kenyamanan ukuran teks di seluruh halaman toko.
                        </small>
                    </div>
                </div>

                <!-- Efek Animasi Melayang -->
                <div class="col-md-6">
                    <div class="p-3 rounded-4 h-100" style="background: var(--bg-card-subtle); border: 1px solid var(--border-color);">
                        <label class="form-label fw-bold mb-2 text-primary" style="font-size: 0.9rem;">
                            <i class="bi bi-magic me-1"></i> Efek Animasi & Transisi:
                        </label>
                        <div class="d-flex align-items-center justify-content-between pt-1">
                            <span class="text-secondary small">Animasi gelembung & kartu melayang</span>
                            <div class="form-check form-switch fs-5 mb-0">
                                <input class="form-check-input" type="checkbox" id="animationToggle" checked onchange="toggleAppAnimations(this.checked)">
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2" style="font-size: 0.78rem;">
                            Nonaktifkan jika Anda menginginkan performa maksimal tanpa animasi gerakan.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Tombol Reset -->
            <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-color: var(--border-color) !important;">
                <span class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i> Preferensi ini otomatis disimpan di browser Anda.
                </span>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="resetSettingsToDefault()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset ke Standar Pabrik
                </button>
            </div>
        </div>

        <?php if(isset($_SESSION['user_id'])): ?>
        <!-- Section 3: Ubah Kata Sandi Akun (Password) -->
        <div class="card card-cute mb-4 p-4 p-sm-5 shadow-sm" id="password-section">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="icon-pod icon-pod-md icon-pod-circle icon-pod-amber shadow-xs fs-4">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0" style="color: var(--text-primary);">Keamanan & Ubah Kata Sandi</h4>
                    <p class="text-secondary small mb-0">Perbarui kata sandi akun <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Anda'); ?></strong> untuk menjaga keamanan akun Anda.</p>
                </div>
            </div>

            <!-- Flash Message in Password Section -->
            <?php if(isset($_SESSION['flash_msg'])): ?>
                <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info'; ?> alert-dismissible fade show rounded-4 my-3" role="alert">
                    <?= $_SESSION['flash_msg']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
            <?php endif; ?>

            <form method="POST" action="<?= BASEURL; ?>/settings/changePassword" class="mt-3">
                <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?? $data['data']['csrf_token'] ?? ''; ?>">

                <div class="row g-3">
                    <!-- Password Lama -->
                    <div class="col-12 col-md-4">
                        <label for="current_password" class="form-label fw-semibold small">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Kata sandi saat ini" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('current_password', this)" title="Lihat/Sembunyikan">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Password Baru -->
                    <div class="col-12 col-md-4">
                        <label for="new_password" class="form-label fw-semibold small">Kata Sandi Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Minimal 6 karakter" minlength="6" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('new_password', this)" title="Lihat/Sembunyikan">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Konfirmasi Password Baru -->
                    <div class="col-12 col-md-4">
                        <label for="confirm_password" class="form-label fw-semibold small">Ulangi Kata Sandi Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Ulangi kata sandi baru" minlength="6" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('confirm_password', this)" title="Lihat/Sembunyikan">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-color: var(--border-color) !important;">
                    <span class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Minimal 6 karakter. Kombinasikan huruf dan angka untuk keamanan terbaik.
                    </span>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Perbarui Kata Sandi Saya
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

    </div>
</div>

<style>
.cursor-pointer {
    cursor: pointer;
}

.theme-choice-card {
    background: var(--bg-card);
    border: 2px solid var(--border-color);
    transition: all 0.25s ease;
    position: relative;
}

.theme-choice-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

.theme-choice-card.active-theme {
    border-color: #f48fb1 !important;
    background: var(--bg-card-subtle);
    box-shadow: 0 8px 25px rgba(244, 143, 177, 0.3) !important;
}

[data-theme="dark"] .theme-choice-card.active-theme {
    border-color: #38bdf8 !important;
    box-shadow: 0 8px 25px rgba(56, 189, 248, 0.3) !important;
}

.theme-preview-box {
    height: 100px;
    border: 1px solid var(--border-color);
}

.light-preview {
    background: linear-gradient(135deg, #e1f5fe 0%, #fce4ec 100%);
}

.dark-preview {
    background: linear-gradient(135deg, #0b0f19 0%, #1e1b4b 100%);
}

.active-indicator {
    display: none;
}

.theme-choice-card.active-theme .active-indicator {
    display: inline-block;
}
</style>

<script>
// Fungsi Pemilihan Tema di Pengaturan
function selectAppTheme(themeChoice) {
    if (typeof window.applyAppTheme === 'function') {
        window.applyAppTheme(themeChoice, true);
    } else {
        let effectiveTheme = themeChoice;
        if (themeChoice === 'system') {
            effectiveTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            localStorage.setItem('nnaco_theme_mode', 'system');
        } else {
            localStorage.setItem('nnaco_theme_mode', themeChoice);
        }
        document.documentElement.setAttribute('data-theme', effectiveTheme);
        document.documentElement.setAttribute('data-bs-theme', effectiveTheme);
        localStorage.setItem('nnaco_theme', effectiveTheme);
        updateSettingsUI();
    }
}

function updateSettingsUI() {
    const savedMode = localStorage.getItem('nnaco_theme_mode') || localStorage.getItem('nnaco_theme') || 'light';
    
    const cardLight = document.getElementById('cardThemeLight');
    const cardDark = document.getElementById('cardThemeDark');
    const cardSystem = document.getElementById('cardThemeSystem');
    
    if (cardLight) cardLight.classList.remove('active-theme');
    if (cardDark) cardDark.classList.remove('active-theme');
    if (cardSystem) cardSystem.classList.remove('active-theme');
    
    if (savedMode === 'system') {
        if (cardSystem) cardSystem.classList.add('active-theme');
    } else if (savedMode === 'dark') {
        if (cardDark) cardDark.classList.add('active-theme');
    } else {
        if (cardLight) cardLight.classList.add('active-theme');
    }

    // Ukuran Font
    const savedFont = localStorage.getItem('nnaco_font_size') || 'normal';
    const fontSelector = document.getElementById('fontSizeSelector');
    if (fontSelector) fontSelector.value = savedFont;

    // Animasi
    const savedAnim = localStorage.getItem('nnaco_animations') !== 'false';
    const animToggle = document.getElementById('animationToggle');
    if (animToggle) animToggle.checked = savedAnim;
}

function changeAppFontSize(size) {
    localStorage.setItem('nnaco_font_size', size);
    if (size === 'medium') {
        document.documentElement.style.fontSize = '17px';
    } else if (size === 'large') {
        document.documentElement.style.fontSize = '18.5px';
    } else {
        document.documentElement.style.fontSize = '';
    }
    showSettingsToast('Ukuran teks berhasil disesuaikan!');
}

function toggleAppAnimations(enabled) {
    localStorage.setItem('nnaco_animations', enabled ? 'true' : 'false');
    if (!enabled) {
        document.body.classList.add('reduce-motion');
    } else {
        document.body.classList.remove('reduce-motion');
    }
    showSettingsToast(enabled ? 'Animasi diaktifkan' : 'Animasi dinonaktifkan untuk kenyamanan');
}

function resetSettingsToDefault() {
    localStorage.removeItem('nnaco_theme_mode');
    localStorage.removeItem('nnaco_theme');
    localStorage.removeItem('nnaco_font_size');
    localStorage.removeItem('nnaco_animations');
    
    document.documentElement.setAttribute('data-theme', 'light');
    document.documentElement.setAttribute('data-bs-theme', 'light');
    document.documentElement.style.fontSize = '';
    document.body.classList.remove('reduce-motion');
    
    updateSettingsUI();
    showSettingsToast('Pengaturan telah direset ke standar!');
}

function showSettingsToast(msg) {
    // Buat alert toast mini melayang sementara
    let toast = document.getElementById('settingsToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'settingsToast';
        toast.style.position = 'fixed';
        toast.style.bottom = '30px';
        toast.style.left = '50%';
        toast.style.transform = 'translateX(-50%)';
        toast.style.padding = '12px 24px';
        toast.style.background = 'var(--text-primary)';
        toast.style.color = 'var(--bg-body)';
        toast.style.borderRadius = '50px';
        toast.style.boxShadow = '0 10px 30px rgba(0,0,0,0.25)';
        toast.style.zIndex = '9999';
        toast.style.fontWeight = 'bold';
        toast.style.fontSize = '0.9rem';
        toast.style.transition = 'all 0.3s ease';
        document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.style.opacity = '1';
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 300);
    }, 2500);
}

function togglePasswordVisibility(fieldId, btn) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    const isPassword = field.type === 'password';
    field.type = isPassword ? 'text' : 'password';
    const icon = btn.querySelector('i');
    if (icon) {
        icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
    }
}

// Inisialisasi tampilan saat halaman siap
document.addEventListener('DOMContentLoaded', updateSettingsUI);
</script>
