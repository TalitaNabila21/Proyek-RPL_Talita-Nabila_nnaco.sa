<div class="row justify-content-center py-4">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow border-0 rounded-4 overflow-hidden" style="background: var(--bg-card);">
            <!-- Card Header dengan Gradien Cantik -->
            <div class="card-header border-0 text-center text-white py-4" style="background: linear-gradient(135deg, #d81b60, #ff4081);">
                <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle mb-2 shadow-sm" style="background: rgba(255, 255, 255, 0.2);">
                    <i class="bi bi-person-circle fs-1 text-white"></i>
                </div>
                <h3 class="fw-bold mb-1">Masuk ke Akun</h3>
                <p class="small mb-0 opacity-75">Toko Aksesori nnaco_sa</p>
            </div>

            <div class="card-body p-4">
                <!-- Nav Tabs Pilihan Metode Login -->
                <div class="d-flex p-1 mb-4 rounded-pill border" style="background: var(--bg-card-subtle);">
                    <button type="button" class="btn btn-sm w-50 rounded-pill fw-bold active transition-all" id="tab-password" onclick="switchLoginTab('password')" style="background-color: #ffffff; color: #d81b60; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);">
                        <i class="bi bi-key-fill me-1"></i> Password
                    </button>
                    <button type="button" class="btn btn-sm w-50 rounded-pill fw-bold transition-all" id="tab-otp" onclick="switchLoginTab('otp')" style="background-color: transparent; color: var(--text-muted);">
                        <i class="bi bi-whatsapp text-success me-1"></i> OTP WA
                    </button>
                </div>

                <!-- Alert Box Global untuk Pesan Flash Server -->
                <?php if (isset($_SESSION['flash_msg'])): ?>
                    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info'; ?> alert-dismissible fade show small py-2 mb-3 shadow-sm rounded-3" role="alert">
                        <?= $_SESSION['flash_msg']; ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
                <?php endif; ?>

                <!-- ========================================== -->
                <!-- TAB 1: LOGIN METODE EMAIL & PASSWORD       -->
                <!-- ========================================== -->
                <div id="content-password">
                    <form action="<?= BASEURL; ?>/auth/processLogin" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token'] ?>">

                        <div class="mb-3 text-start">
                            <label for="email" class="form-label text-cute-dark fw-semibold w-100">
                                <i class="bi bi-envelope-fill text-primary me-1"></i> Email / Akun
                            </label>
                            <input type="text" class="form-control" id="email" name="email" placeholder="nama@email.com" required autocomplete="username">
                        </div>

                        <div class="mb-3 text-start">
                            <label for="password" class="form-label text-cute-dark fw-semibold w-100">
                                <i class="bi bi-shield-lock-fill text-primary me-1"></i> Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', this)" title="Lihat password">
                                    <i class="bi bi-eye-fill"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-cute py-2 shadow-sm fw-bold">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ========================================== -->
                <!-- TAB 2: LOGIN METODE OTP WHATSAPP (FONNTE)  -->
                <!-- ========================================== -->
                <?php $isOtpActive = ($data['data']['otp_enabled'] ?? true); ?>
                <div id="content-otp" style="display: none;">
                    <?php if (!$isOtpActive): ?>
                        <div class="alert alert-warning border-0 py-3 rounded-4 text-center my-3" style="background: rgba(255, 193, 7, 0.15);">
                            <i class="bi bi-shield-slash fs-2 text-warning d-block mb-2"></i>
                            <h6 class="fw-bold mb-1 text-dark">Login OTP Dinonaktifkan</h6>
                            <p class="small text-muted mb-0">Fitur autentikasi WhatsApp OTP sedang dinonaktifkan sementara oleh Administrator. Silakan masuk menggunakan Email & Password.</p>
                        </div>
                    <?php else: ?>
                    <!-- Alert Box Interaktif untuk Status OTP -->
                    <div id="otpAlertBox" class="d-none alert mb-3" role="alert"></div>

                    <!-- Langkah 1: Input Nomor WhatsApp -->
                    <div class="mb-3 text-start">
                        <label for="otpPhone" class="form-label text-cute-dark fw-semibold w-100">
                            <i class="bi bi-whatsapp text-success me-1"></i> Nomor WhatsApp Terdaftar
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0" style="background: var(--bg-card-subtle) !important;">
                                <i class="bi bi-phone text-success"></i>
                            </span>
                            <input type="tel" class="form-control" id="otpPhone" name="phone" placeholder="Contoh: 081234567890" autocomplete="tel">
                            <button class="btn btn-cute px-3 fw-bold" type="button" id="btnRequestOtp" onclick="handleSendOtp(event)" style="cursor: pointer; position: relative; z-index: 2;">
                                <span class="spinner-border spinner-border-sm me-1 d-none" id="spinnerRequest" role="status" aria-hidden="true"></span>
                                <span id="btnRequestText"><i class="bi bi-send-fill me-1"></i> Kirim OTP</span>
                            </button>
                        </div>
                        <div class="form-text text-muted mt-1" style="font-size: 0.78rem;">
                            Kode OTP 6 digit akan dikirimkan langsung ke nomor WhatsApp Anda.
                        </div>
                    </div>

                    <!-- Langkah 2: Input & Verifikasi Kode OTP -->
                    <div id="otpVerifySection" class="p-3 rounded-4 mb-3 text-start" style="background: var(--bg-card-subtle); border: 1.5px solid var(--border-color);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold small text-cute-dark">
                                <i class="bi bi-shield-check text-success me-1"></i> Masukkan 6 Digit Kode OTP:
                            </span>
                            <span class="badge bg-warning text-dark px-2 py-1" id="badgeTimer" title="Masa berlaku kode OTP">
                                <i class="bi bi-stopwatch-fill me-1"></i> <span id="expiryCountdown">05:00</span>
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" class="form-control form-control-lg text-center fw-bold shadow-sm" id="otpCode" placeholder="● ● ● ● ● ●" style="font-size: 1.6rem; letter-spacing: 0.4rem; background: var(--bg-card);" autocomplete="one-time-code">
                            <div class="form-text text-center text-muted" style="font-size: 0.75rem;">
                                Masukkan 6 digit kode yang diterima di aplikasi WhatsApp Anda.
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success py-2 fw-bold shadow-sm" id="btnVerifyOtp" onclick="handleVerifyOtp(event)" style="cursor: pointer;">
                                <span class="spinner-border spinner-border-sm me-1 d-none" id="spinnerVerify" role="status" aria-hidden="true"></span>
                                <span id="btnVerifyText"><i class="bi bi-shield-lock-fill me-1"></i> Verifikasi & Masuk</span>
                            </button>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top" style="border-color: var(--border-color) !important;">
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-muted" id="btnChangeNumber" onclick="handleChangeNumber(event)" style="cursor: pointer;">
                                <i class="bi bi-arrow-left"></i> Ganti Nomor
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="btnResendOtp" onclick="handleResendOtp(event)" disabled style="cursor: pointer;">
                                <span class="spinner-border spinner-border-sm me-1 d-none" id="spinnerResend" role="status" aria-hidden="true"></span>
                                <i class="bi bi-arrow-repeat me-1"></i> <span id="btnResendText">Kirim Ulang</span>
                            </button>
                        </div>
                    </div>

                    <!-- Info Keamanan -->
                    <div class="text-start p-2 rounded text-muted mb-2" style="background: var(--bg-card-subtle); font-size: 0.78rem; border-left: 3px solid #25d366; border: 1px solid var(--border-color);">
                        <strong><i class="bi bi-shield-fill-check text-success me-1"></i> Standar Keamanan OTP:</strong><br>
                        • Kode OTP dilindungi secara kriptografis dan berlaku terbatas.<br>
                        • OTP hanya dapat digunakan satu kali untuk akun Anda yang sah.
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Footer Card: Link Pendaftaran Akun Customer -->
                <div class="mt-4 pt-2 border-top text-center" style="border-color: var(--border-color) !important;">
                    <p class="small mb-0 text-muted">Belum punya akun? <a href="<?= BASEURL; ?>/auth/register" class="fw-bold text-decoration-none" style="color: #d81b60;">Daftar sekarang</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const CSRF_TOKEN = '<?= $data['data']['csrf_token'] ?>';
const BASE_URL = '<?= BASEURL ?>';

// Switch Tab Login
function switchLoginTab(type) {
    const tabPassword = document.getElementById('tab-password');
    const tabOtp = document.getElementById('tab-otp');
    const contentPassword = document.getElementById('content-password');
    const contentOtp = document.getElementById('content-otp');

    if (type === 'otp') {
        tabPassword.classList.remove('active');
        tabPassword.style.backgroundColor = 'transparent';
        tabPassword.style.color = 'var(--text-muted)';
        
        tabOtp.classList.add('active');
        tabOtp.style.backgroundColor = '#ffffff';
        tabOtp.style.color = '#198754';

        contentPassword.style.display = 'none';
        contentOtp.style.display = 'block';

        const otpPhone = document.getElementById('otpPhone');
        if (otpPhone) otpPhone.focus();
    } else {
        tabOtp.classList.remove('active');
        tabOtp.style.backgroundColor = 'transparent';
        tabOtp.style.color = 'var(--text-muted)';

        tabPassword.classList.add('active');
        tabPassword.style.backgroundColor = '#ffffff';
        tabPassword.style.color = '#d81b60';

        contentOtp.style.display = 'none';
        contentPassword.style.display = 'block';

        const emailInput = document.getElementById('email');
        if (emailInput) emailInput.focus();
    }
}

function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn ? btn.querySelector('i') : null;
    if (input.type === "password") {
        input.type = "text";
        if (icon) {
            icon.classList.remove('bi-eye-fill');
            icon.classList.add('bi-eye-slash-fill');
        }
    } else {
        input.type = "password";
        if (icon) {
            icon.classList.remove('bi-eye-slash-fill');
            icon.classList.add('bi-eye-fill');
        }
    }
}

// Alert Handler
function showOtpAlert(type, message, extraHtml = '') {
    const alertBox = document.getElementById('otpAlertBox');
    if (!alertBox) return;
    alertBox.className = `alert alert-${type} py-2 px-3 small shadow-sm text-start`;
    let icon = 'bi-info-circle-fill';
    if (type === 'success') icon = 'bi-check-circle-fill';
    if (type === 'warning') icon = 'bi-exclamation-triangle-fill';
    if (type === 'danger') icon = 'bi-x-circle-fill';

    alertBox.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi ${icon} me-2 fs-5"></i>
            <div>${message}</div>
        </div>
        ${extraHtml ? `<div class="mt-2">${extraHtml}</div>` : ''}
    `;
    alertBox.classList.remove('d-none');
}

function hideOtpAlert() {
    const alertBox = document.getElementById('otpAlertBox');
    if (alertBox) alertBox.classList.add('d-none');
}

// Timer dan Cooldown
let expiryInterval = null;
let resendInterval = null;

function startExpiryTimer(seconds = 300) {
    const expiryCountdown = document.getElementById('expiryCountdown');
    if (!expiryCountdown) return;
    clearInterval(expiryInterval);
    let remaining = seconds;

    function updateDisplay() {
        const m = Math.floor(remaining / 60);
        const s = remaining % 60;
        expiryCountdown.textContent = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        if (remaining <= 0) {
            clearInterval(expiryInterval);
            expiryCountdown.textContent = '00:00';
            showOtpAlert('warning', 'Kode OTP telah kedaluwarsa. Silakan klik tombol "Kirim Ulang" untuk mendapatkan kode baru.');
        }
        remaining--;
    }
    updateDisplay();
    expiryInterval = setInterval(updateDisplay, 1000);
}

function startResendCooldown(seconds = 60) {
    const btnResendOtp = document.getElementById('btnResendOtp');
    const btnResendText = document.getElementById('btnResendText');
    if (!btnResendOtp || !btnResendText) return;
    clearInterval(resendInterval);
    btnResendOtp.disabled = true;
    let remaining = seconds;

    function updateResendText() {
        if (remaining > 0) {
            btnResendText.textContent = `Kirim Ulang (${remaining}s)`;
            btnResendOtp.disabled = true;
            remaining--;
        } else {
            clearInterval(resendInterval);
            btnResendText.textContent = 'Kirim Ulang';
            btnResendOtp.disabled = false;
        }
    }
    updateResendText();
    resendInterval = setInterval(updateResendText, 1000);
}

// 1. Permintaan OTP
function handleSendOtp(e) {
    if (e) e.preventDefault();
    const otpPhone = document.getElementById('otpPhone');
    const btnRequestOtp = document.getElementById('btnRequestOtp');
    const spinner = document.getElementById('spinnerRequest');
    const btnText = document.getElementById('btnRequestText');
    const otpCode = document.getElementById('otpCode');

    const phone = otpPhone.value.trim();
    if (!phone) {
        showOtpAlert('warning', 'Silakan masukkan nomor WhatsApp Anda terlebih dahulu.');
        otpPhone.focus();
        return;
    }

    hideOtpAlert();
    btnRequestOtp.disabled = true;
    spinner.classList.remove('d-none');
    btnText.innerHTML = 'Mengirim...';

    const formData = new FormData();
    formData.append('phone', phone);
    formData.append('purpose', 'login');
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('is_ajax', '1');

    fetch(BASE_URL + '/auth/requestOtp', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btnRequestOtp.disabled = false;
        spinner.classList.add('d-none');
        btnText.innerHTML = '<i class="bi bi-send-fill me-1"></i> Kirim OTP';

        if (data.success) {
            showOtpAlert('success', data.message);
            if (otpCode) {
                otpCode.focus();
            }
            startExpiryTimer(data.expires_in || 300);
            startResendCooldown(data.cooldown || 60);
        } else {
            let extraHtml = '';
            if (data.not_registered) {
                extraHtml = `
                    <div class="mt-2 pt-2 border-top">
                        <a href="${data.register_url || BASE_URL + '/auth/register'}" class="btn btn-sm btn-outline-primary fw-bold rounded-pill">
                            <i class="bi bi-person-plus-fill me-1"></i> Daftar Akun Customer Baru
                        </a>
                    </div>
                `;
            }
            showOtpAlert('danger', data.message, extraHtml);
            if (data.cooldown) {
                startResendCooldown(data.cooldown);
            }
        }
    })
    .catch(err => {
        btnRequestOtp.disabled = false;
        spinner.classList.add('d-none');
        btnText.innerHTML = '<i class="bi bi-send-fill me-1"></i> Kirim OTP';
        showOtpAlert('danger', 'Terjadi gangguan jaringan saat menghubungi server. Silakan coba beberapa saat lagi.');
    });
}

// 2. Verifikasi OTP
function handleVerifyOtp(e) {
    if (e) e.preventDefault();
    const otpPhone = document.getElementById('otpPhone');
    const otpCode = document.getElementById('otpCode');
    const btnVerifyOtp = document.getElementById('btnVerifyOtp');
    const spinner = document.getElementById('spinnerVerify');
    const btnText = document.getElementById('btnVerifyText');

    const phone = otpPhone.value.trim();
    const code = otpCode.value.trim();

    if (!phone) {
        showOtpAlert('warning', 'Nomor WhatsApp tidak boleh kosong.');
        otpPhone.focus();
        return;
    }

    if (code.length !== 6) {
        showOtpAlert('warning', 'Harap masukkan 6 digit angka kode OTP secara lengkap.');
        otpCode.focus();
        return;
    }

    hideOtpAlert();
    btnVerifyOtp.disabled = true;
    spinner.classList.remove('d-none');
    btnText.innerHTML = 'Memverifikasi...';

    const formData = new FormData();
    formData.append('phone', phone);
    formData.append('otp', code);
    formData.append('purpose', 'login');
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('is_ajax', '1');

    fetch(BASE_URL + '/auth/verifyOtp', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showOtpAlert('success', '✅ ' + data.message);
            btnText.innerHTML = 'Berhasil! Mengalihkan...';
            setTimeout(() => {
                window.location.href = data.redirect || (BASE_URL + '/customer');
            }, 750);
        } else {
            btnVerifyOtp.disabled = false;
            spinner.classList.add('d-none');
            btnText.innerHTML = '<i class="bi bi-shield-lock-fill me-1"></i> Verifikasi & Masuk';
            showOtpAlert('danger', data.message);
            otpCode.select();
        }
    })
    .catch(err => {
        btnVerifyOtp.disabled = false;
        spinner.classList.add('d-none');
        btnText.innerHTML = '<i class="bi bi-shield-lock-fill me-1"></i> Verifikasi & Masuk';
        showOtpAlert('danger', 'Terjadi kesalahan sistem saat verifikasi. Silakan coba lagi.');
    });
}

// 3. Kirim Ulang OTP
function handleResendOtp(e) {
    if (e) e.preventDefault();
    const otpPhone = document.getElementById('otpPhone');
    const btnResendOtp = document.getElementById('btnResendOtp');
    const spinner = document.getElementById('spinnerResend');
    const phone = otpPhone.value.trim();
    if (!phone) return;

    hideOtpAlert();
    btnResendOtp.disabled = true;
    spinner.classList.remove('d-none');

    const formData = new FormData();
    formData.append('phone', phone);
    formData.append('purpose', 'login');
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('is_ajax', '1');

    fetch(BASE_URL + '/auth/requestOtp', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        spinner.classList.add('d-none');
        if (data.success) {
            showOtpAlert('success', data.message);
            const otpCode = document.getElementById('otpCode');
            if (otpCode) {
                otpCode.value = '';
                otpCode.focus();
            }
            startExpiryTimer(data.expires_in || 300);
            startResendCooldown(data.cooldown || 60);
        } else {
            showOtpAlert('danger', data.message);
            if (data.cooldown) {
                startResendCooldown(data.cooldown);
            } else {
                btnResendOtp.disabled = false;
            }
        }
    })
    .catch(err => {
        spinner.classList.add('d-none');
        btnResendOtp.disabled = false;
        showOtpAlert('danger', 'Gagal mengirim ulang OTP. Silakan periksa koneksi internet Anda.');
    });
}

function handleChangeNumber(e) {
    if (e) e.preventDefault();
    const otpPhone = document.getElementById('otpPhone');
    const otpCode = document.getElementById('otpCode');
    if (otpCode) otpCode.value = '';
    if (otpPhone) {
        otpPhone.value = '';
        otpPhone.focus();
    }
    hideOtpAlert();
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'otp') {
        switchLoginTab('otp');
    } else {
        switchLoginTab('password');
    }

    if (urlParams.get('phone')) {
        const phoneInput = document.getElementById('otpPhone');
        if (phoneInput) phoneInput.value = urlParams.get('phone');
    }

    const otpCode = document.getElementById('otpCode');
    if (otpCode) {
        otpCode.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 6) {
                const btnVerify = document.getElementById('btnVerifyOtp');
                if (btnVerify) btnVerify.focus();
            }
        });
        otpCode.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleVerifyOtp(e);
            }
        });
    }

    const otpPhone = document.getElementById('otpPhone');
    if (otpPhone) {
        otpPhone.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSendOtp(e);
            }
        });
    }
});
</script>
