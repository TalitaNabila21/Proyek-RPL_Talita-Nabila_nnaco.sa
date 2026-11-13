<div class="row justify-content-center mt-5 mb-5">
    <div class="col-md-6 col-lg-5">
        <div class="card card-cute text-center shadow-sm" style="background: var(--bg-card);">
            <div class="card-header bg-transparent border-0 pt-4 pb-1">
                <h3 class="mb-0 text-cute-dark fw-bold">Daftar Akun Baru 🧸</h3>
                <p class="text-muted small mt-1 mb-0">Lengkapi data akun untuk mulai berbelanja di nnaco_sa</p>
            </div>
            <div class="card-body px-4 pt-3">
                <!-- Alert Box Interaktif untuk Status OTP Registrasi -->
                <div id="regAlertBox" class="d-none alert mb-3 text-start" role="alert"></div>

                <!-- Alert Box Global untuk Pesan Flash Server -->
                <?php if (isset($_SESSION['flash_msg'])): ?>
                    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info'; ?> alert-dismissible fade show small py-2 mb-3 shadow-sm rounded-3 text-start" role="alert">
                        <?= $_SESSION['flash_msg']; ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
                <?php endif; ?>

                <form action="<?= BASEURL; ?>/auth/processRegister" method="POST" id="registerForm">
                    <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token'] ?>">

                    <div class="mb-3 text-start">
                        <label for="name" class="form-label text-cute-dark fw-semibold w-100">
                            <i class="bi bi-person-fill text-primary me-1"></i> Nama Lengkap
                        </label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Nama lengkap Anda" required autocomplete="name">
                    </div>

                    <div class="mb-3 text-start">
                        <label for="email" class="form-label text-cute-dark fw-semibold w-100">
                            <i class="bi bi-envelope-heart-fill text-primary me-1"></i> Email address
                        </label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="nama@email.com" required autocomplete="email">
                    </div>

                    <!-- Nomor WhatsApp & Tombol Kirim OTP -->
                    <div class="mb-3 text-start">
                        <label for="regPhone" class="form-label text-cute-dark fw-semibold w-100">
                            <i class="bi bi-whatsapp text-success me-1"></i> Nomor WhatsApp <span class="badge bg-light text-muted border fw-normal ms-1">Untuk Login Cepat & Verifikasi OTP</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0" style="background: var(--bg-card-subtle) !important;">
                                <i class="bi bi-phone text-success"></i>
                            </span>
                            <input type="tel" class="form-control" id="regPhone" name="phone" placeholder="Contoh: 081234567890" required autocomplete="tel">
                            <button class="btn btn-cute px-3 fw-bold" type="button" id="btnSendRegOtp" onclick="sendRegisterOtpAction(event)" style="cursor: pointer; position: relative; z-index: 2;">
                                <span class="spinner-border spinner-border-sm me-1 d-none" id="spinnerRegRequest" role="status" aria-hidden="true"></span>
                                <span id="btnRegRequestText"><i class="bi bi-send-fill me-1"></i> Kirim OTP WA</span>
                            </button>
                        </div>
                        <div class="form-text text-muted mt-1" style="font-size: 0.78rem;">
                            Pastikan nomor WhatsApp Anda aktif untuk menerima kode verifikasi OTP 6 digit.
                        </div>
                    </div>

                    <!-- Box Input Kode OTP Registrasi -->
                    <div id="regOtpBox" class="p-3 rounded-4 mb-3 text-start" style="background: var(--bg-card-subtle); border: 1.5px solid var(--border-color);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="regOtp" class="fw-bold small text-cute-dark mb-0">
                                <i class="bi bi-shield-check text-success me-1"></i> Masukkan 6 Digit Kode OTP:
                            </label>
                            <span class="badge bg-warning text-dark px-2 py-1" id="regBadgeTimer" title="Masa berlaku kode OTP">
                                <i class="bi bi-stopwatch-fill me-1"></i> <span id="regExpiryCountdown">05:00</span>
                            </span>
                        </div>
                        <div class="input-group mb-2">
                            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" class="form-control text-center fw-bold fs-4 shadow-sm" id="regOtp" name="otp" placeholder="● ● ● ● ● ●" style="letter-spacing: 0.35rem; background: var(--bg-card);" autocomplete="one-time-code" required>
                            <button class="btn btn-outline-secondary btn-sm px-3" type="button" id="btnResendRegOtp" onclick="resendRegisterOtpAction(event)" disabled style="cursor: pointer;">
                                <span class="spinner-border spinner-border-sm me-1 d-none" id="spinnerRegResend" role="status" aria-hidden="true"></span>
                                <i class="bi bi-arrow-repeat me-1"></i> <span id="btnResendRegText">Kirim Ulang</span>
                            </button>
                        </div>
                        <div id="regOtpStatus" class="small text-muted" style="font-size: 0.75rem;">
                            Klik <b>"Kirim OTP WA"</b> di atas, lalu masukkan 6 digit kode yang diterima di WhatsApp Anda.
                        </div>
                    </div>

                    <div class="mb-3 text-start">
                        <label for="password" class="form-label text-cute-dark fw-semibold w-100">
                            <i class="bi bi-shield-lock-fill text-primary me-1"></i> Password
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter" required minlength="6" autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', this)" title="Lihat password">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3 text-start">
                        <label for="password_confirm" class="form-label text-cute-dark fw-semibold w-100">
                            <i class="bi bi-shield-check text-primary me-1"></i> Konfirmasi Password
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Ulangi password" required minlength="6" autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirm', this)" title="Lihat password">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-cute py-2 shadow-sm fw-bold w-100" id="btnSubmitRegister" style="cursor: pointer;">
                            <i class="bi bi-person-plus-fill me-1"></i> Daftar Sekarang
                        </button>
                    </div>
                </form>

                <div class="mt-4 pt-2 border-top text-center" style="border-color: var(--border-color) !important;">
                    <p class="small text-muted mb-0">Sudah punya akun? <a href="<?= BASEURL; ?>/auth/login" class="fw-bold text-decoration-none" style="color: #d81b60;">Login di sini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const CSRF_TOKEN = '<?= $data['data']['csrf_token'] ?>';
const BASE_URL = '<?= BASEURL ?>';

function togglePassword(inputId, btn) {
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

function showRegAlert(type, message, extraHtml = '') {
    const alertBox = document.getElementById('regAlertBox');
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

function hideRegAlert() {
    const alertBox = document.getElementById('regAlertBox');
    if (alertBox) alertBox.classList.add('d-none');
}

let regExpiryInterval = null;
let regResendInterval = null;

function startRegExpiryTimer(seconds = 300) {
    const regExpiryCountdown = document.getElementById('regExpiryCountdown');
    if (!regExpiryCountdown) return;
    clearInterval(regExpiryInterval);
    let remaining = seconds;

    function updateDisplay() {
        const m = Math.floor(remaining / 60);
        const s = remaining % 60;
        regExpiryCountdown.textContent = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        if (remaining <= 0) {
            clearInterval(regExpiryInterval);
            regExpiryCountdown.textContent = '00:00';
            showRegAlert('warning', 'Kode OTP telah kedaluwarsa. Silakan klik "Kirim Ulang" untuk mendapatkan kode baru.');
        }
        remaining--;
    }
    updateDisplay();
    regExpiryInterval = setInterval(updateDisplay, 1000);
}

function startRegResendCooldown(seconds = 60) {
    const btnResendRegOtp = document.getElementById('btnResendRegOtp');
    const btnResendRegText = document.getElementById('btnResendRegText');
    if (!btnResendRegOtp || !btnResendRegText) return;
    clearInterval(regResendInterval);
    btnResendRegOtp.disabled = true;
    let remaining = seconds;

    function updateResendText() {
        if (remaining > 0) {
            btnResendRegText.textContent = `Kirim Ulang (${remaining}s)`;
            btnResendRegOtp.disabled = true;
            remaining--;
        } else {
            clearInterval(regResendInterval);
            btnResendRegText.textContent = 'Kirim Ulang';
            btnResendRegOtp.disabled = false;
        }
    }
    updateResendText();
    regResendInterval = setInterval(updateResendText, 1000);
}

// 1. Kirim OTP Registrasi
function sendRegisterOtpAction(e) {
    if (e) e.preventDefault();
    const regPhone = document.getElementById('regPhone');
    const btnSendRegOtp = document.getElementById('btnSendRegOtp');
    const spinner = document.getElementById('spinnerRegRequest');
    const btnText = document.getElementById('btnRegRequestText');
    const regOtp = document.getElementById('regOtp');

    const phone = regPhone.value.trim();
    if (!phone) {
        showRegAlert('warning', 'Silakan masukkan nomor WhatsApp Anda terlebih dahulu.');
        regPhone.focus();
        return;
    }

    hideRegAlert();
    btnSendRegOtp.disabled = true;
    spinner.classList.remove('d-none');
    btnText.innerHTML = 'Mengirim...';

    const formData = new FormData();
    formData.append('phone', phone);
    formData.append('purpose', 'register');
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('is_ajax', '1');

    fetch(BASE_URL + '/auth/requestOtp', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btnSendRegOtp.disabled = false;
        spinner.classList.add('d-none');
        btnText.innerHTML = '<i class="bi bi-send-fill me-1"></i> Kirim OTP WA';

        if (data.success) {
            showRegAlert('success', `<b>Berhasil!</b> ${data.message}`);
            if (regOtp) {
                regOtp.focus();
            }
            startRegExpiryTimer(data.expires_in || 300);
            startRegResendCooldown(data.cooldown || 60);
        } else {
            let extraHtml = '';
            if (data.already_registered) {
                extraHtml = `
                    <div class="mt-2 pt-2 border-top">
                        <a href="${data.login_url || BASE_URL + '/auth/login?tab=otp'}" class="btn btn-sm btn-outline-success fw-bold rounded-pill">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Langsung Login via WhatsApp
                        </a>
                    </div>
                `;
            }
            showRegAlert('danger', data.message, extraHtml);

            if (data.cooldown) {
                startRegResendCooldown(data.cooldown);
            }
        }
    })
    .catch(err => {
        btnSendRegOtp.disabled = false;
        spinner.classList.add('d-none');
        btnText.innerHTML = '<i class="bi bi-send-fill me-1"></i> Kirim OTP WA';
        showRegAlert('danger', 'Terjadi gangguan koneksi ke server. Silakan coba lagi.');
    });
}

// 2. Kirim Ulang OTP Registrasi
function resendRegisterOtpAction(e) {
    if (e) e.preventDefault();
    const regPhone = document.getElementById('regPhone');
    const btnResendRegOtp = document.getElementById('btnResendRegOtp');
    const spinner = document.getElementById('spinnerRegResend');
    const phone = regPhone.value.trim();
    if (!phone) return;

    hideRegAlert();
    btnResendRegOtp.disabled = true;
    spinner.classList.remove('d-none');

    const formData = new FormData();
    formData.append('phone', phone);
    formData.append('purpose', 'register');
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
            showRegAlert('success', '<b>Berhasil!</b> Kode OTP baru berhasil dikirimkan ke WhatsApp Anda.');
            const regOtp = document.getElementById('regOtp');
            if (regOtp) {
                regOtp.value = '';
                regOtp.focus();
            }
            startRegExpiryTimer(data.expires_in || 300);
            startRegResendCooldown(data.cooldown || 60);
        } else {
            showRegAlert('danger', data.message);
            if (data.cooldown) {
                startRegResendCooldown(data.cooldown);
            } else {
                btnResendRegOtp.disabled = false;
            }
        }
    })
    .catch(err => {
        spinner.classList.add('d-none');
        btnResendRegOtp.disabled = false;
        showRegAlert('danger', 'Gagal mengirim ulang OTP. Silakan periksa koneksi internet Anda.');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const regOtp = document.getElementById('regOtp');
    if (regOtp) {
        regOtp.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }

    const regPhone = document.getElementById('regPhone');
    if (regPhone) {
        regPhone.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendRegisterOtpAction(e);
            }
        });
    }

    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const phone = regPhone ? regPhone.value.trim() : '';
            const otp = regOtp ? regOtp.value.trim() : '';

            if (phone && !otp) {
                e.preventDefault();
                showRegAlert('warning', 'Harap klik "Kirim OTP WA" dan masukkan 6 digit kode OTP yang diterima di WhatsApp Anda.');
                if (regOtp) regOtp.focus();
            }
        });
    }
});
</script>
