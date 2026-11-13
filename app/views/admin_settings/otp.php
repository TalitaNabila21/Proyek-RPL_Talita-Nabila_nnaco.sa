<?php
if (isset($data['data']['data']) && is_array($data['data']['data'])) {
    $data = $data['data']['data'];
} elseif (isset($data['data']) && is_array($data['data'])) {
    $data = $data['data'];
}
$csrfToken = $data['csrf_token'] ?? '';
$otpEnabled = ($data['otp_enabled'] ?? '1') === '1';
$maskedToken = $data['fonnte_token_masked'] ?? '';
$fonnteStatus = $data['fonnte_status'] ?? [];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
    <div>
        <h6 class="card-category text-muted mb-1">SECURITY & AUTHENTICATION</h6>
        <h1 class="h2 fw-bold text-cute-dark mb-0 d-flex align-items-center gap-2">
            <span class="icon-pod icon-pod-md icon-pod-emerald shadow-xs"><i class="bi bi-shield-lock-fill"></i></span>
            <span>Authentication OTP WhatsApp</span>
        </h1>
        <p class="text-muted small mb-0 mt-1">Kelola aturan autentikasi login dan registrasi berbasis kode One-Time Password (OTP) via gateway WhatsApp Fonnte.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="<?= BASEURL; ?>/adminSettings" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> <span>Kembali ke Pengaturan</span>
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

<div class="row g-4 mb-5">
    
    <!-- Kolom Formulir Pengaturan OTP -->
    <div class="col-lg-7">
        <form action="<?= BASEURL; ?>/adminSettings/saveOtp" method="POST" id="formOtpSettings">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>">

            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--bg-card);">
                <div class="card-header border-0 bg-transparent py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-cute-dark">
                        <i class="bi bi-sliders text-success me-2"></i>Konfigurasi Autentikasi OTP
                    </h5>
                    <div class="form-check form-switch fs-5 mb-0" title="Aktifkan atau nonaktifkan fitur Login OTP di web">
                        <input class="form-check-input" type="checkbox" role="switch" id="otp_enabled" name="otp_enabled" value="1" <?= $otpEnabled ? 'checked' : ''; ?>>
                        <label class="form-check-label fs-6 fw-semibold text-cute-dark" for="otp_enabled">
                            <?= $otpEnabled ? '<span class="text-success">Aktif</span>' : '<span class="text-muted">Nonaktif</span>'; ?>
                        </label>
                    </div>
                </div>

                <div class="card-body px-4 pb-4 pt-2">

                    <!-- Token Fonnte API -->
                    <div class="mb-4">
                        <label for="fonnte_token" class="form-label fw-bold text-cute-dark d-flex justify-content-between">
                            <span>Token Fonnte API Gateway:</span>
                            <?php if (!empty($maskedToken)): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle-fill me-1"></i> Tersimpan (<?= $maskedToken ?>)
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i> Belum Dikonfigurasi
                                </span>
                            <?php endif; ?>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-key-fill text-success"></i></span>
                            <input type="password" class="form-control" id="fonnte_token" name="fonnte_token" placeholder="<?= !empty($maskedToken) ? 'Biarkan kosong jika tidak ingin mengubah (' . $maskedToken . ')' : 'Masukkan token Fonnte'; ?>" autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleTokenVisibility()" id="btnToggleToken" title="Tampilkan / Sembunyikan Token">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </div>
                        <div class="form-text text-muted small mt-1">
                            Token ini digunakan untuk mengirim pesan OTP autentikasi dan notifikasi pesanan ke nomor WhatsApp pengguna.
                        </div>
                    </div>

                    <!-- Parameter Keamanan OTP -->
                    <div class="row g-3 mb-4">
                        <!-- Masa Berlaku -->
                        <div class="col-md-6">
                            <label for="otp_expiry_minutes" class="form-label fw-bold text-cute-dark">
                                Masa Berlaku OTP (Expiry):
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="otp_expiry_minutes" name="otp_expiry_minutes" value="<?= htmlspecialchars($data['otp_expiry_minutes'] ?? 5); ?>" min="1" max="30" required>
                                <span class="input-group-text bg-light text-muted small">Menit</span>
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.74rem;">
                                Standar: 5 menit. Setelah waktu habis, OTP otomatis kedaluwarsa.
                            </div>
                        </div>

                        <!-- Batas Percobaan -->
                        <div class="col-md-6">
                            <label for="otp_max_attempts" class="form-label fw-bold text-cute-dark">
                                Batas Percobaan Salah:
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="otp_max_attempts" name="otp_max_attempts" value="<?= htmlspecialchars($data['otp_max_attempts'] ?? 5); ?>" min="1" max="10" required>
                                <span class="input-group-text bg-light text-muted small">Kali</span>
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.74rem;">
                                Standar: 5 kali. Jika melebihi batas, OTP langsung hangus.
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Jeda Kirim Ulang -->
                        <div class="col-md-6">
                            <label for="otp_resend_cooldown" class="form-label fw-bold text-cute-dark">
                                Jeda Tunggu Kirim Ulang (Cooldown):
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="otp_resend_cooldown" name="otp_resend_cooldown" value="<?= htmlspecialchars($data['otp_resend_cooldown'] ?? 60); ?>" min="15" max="300" required>
                                <span class="input-group-text bg-light text-muted small">Detik</span>
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.74rem;">
                                Standar: 60 detik. Mencegah spam klik tombol kirim ulang.
                            </div>
                        </div>

                        <!-- Jumlah Digit OTP -->
                        <div class="col-md-6">
                            <label for="otp_length" class="form-label fw-bold text-cute-dark">
                                Panjang Kode OTP:
                            </label>
                            <select class="form-select" id="otp_length" name="otp_length">
                                <option value="6" <?= ($data['otp_length'] ?? 6) == 6 ? 'selected' : ''; ?>>6 Digit Angka (Standar Keamanan Tinggi)</option>
                                <option value="4" <?= ($data['otp_length'] ?? 6) == 4 ? 'selected' : ''; ?>>4 Digit Angka (Simpel)</option>
                            </select>
                            <div class="form-text text-muted" style="font-size: 0.74rem;">
                                Format acak kriptografis (misal: 6 digit: 839201).
                            </div>
                        </div>
                    </div>

                    <!-- Rate Limit per Jam -->
                    <div class="mb-4">
                        <label for="otp_rate_limit_per_hour" class="form-label fw-bold text-cute-dark">
                            Batas Maksimal Permintaan OTP per Jam (Rate Limit):
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="otp_rate_limit_per_hour" name="otp_rate_limit_per_hour" value="<?= htmlspecialchars($data['otp_rate_limit_per_hour'] ?? 15); ?>" min="3" max="100" required>
                            <span class="input-group-text bg-light text-muted small">Permintaan / Jam</span>
                        </div>
                        <div class="form-text text-muted small mt-1">
                            Mencegah serangan brute-force atau kehabisan kuota WhatsApp akibat permintaan berulang pada satu nomor/IP.
                        </div>
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <button type="button" class="btn btn-success px-4 py-2 rounded-pill fw-bold shadow-sm d-inline-flex align-items-center gap-2" onclick="confirmSaveOtp()">
                            <i class="bi bi-save2-fill"></i> Simpan Konfigurasi OTP
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <!-- Kolom Live Test & Status Gateway -->
    <div class="col-lg-5">
        
        <!-- Live Test Send OTP Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--bg-card); border-left: 4px solid #10b981 !important;">
            <div class="card-header border-0 bg-transparent py-3 px-4">
                <h5 class="fw-bold mb-0 text-cute-dark d-flex align-items-center gap-2">
                    <i class="bi bi-send-check-fill text-success"></i> Uji Coba Kirim OTP WhatsApp
                </h5>
            </div>
            <div class="card-body px-4 pb-4 pt-1">
                <p class="text-muted small mb-3">
                    Kirim pesan simulasi OTP ke nomor WhatsApp Anda untuk menguji apakah Fonnte API menerima dan menyampaikan pesan ke nomor tujuan.
                </p>

                <div class="mb-3">
                    <label for="testOtpPhone" class="form-label fw-semibold small text-cute-dark">Nomor WhatsApp Pengujian:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-whatsapp text-success"></i></span>
                        <input type="tel" class="form-control" id="testOtpPhone" placeholder="Contoh: 081234567890">
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="button" class="btn btn-outline-success rounded-pill py-2 fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm" id="btnTestOtp" onclick="runOtpLiveTest()">
                        <span class="spinner-border spinner-border-sm d-none" id="testOtpSpinner" role="status" aria-hidden="true"></span>
                        <i class="bi bi-send-fill" id="testOtpIcon"></i>
                        <span id="testOtpBtnText">Kirim Pesan Tes OTP</span>
                    </button>
                </div>

                <!-- Box Hasil Uji Coba -->
                <div id="testOtpResultBox" class="d-none rounded-3 p-3" style="background: var(--bg-card-subtle); border: 1px solid var(--border-color);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge" id="testOtpBadgeStatus">Status</span>
                        <span class="small text-muted font-monospace" id="testOtpLatency">0 ms</span>
                    </div>
                    <div class="small fw-semibold text-cute-dark mb-1" id="testOtpResultTitle">Hasil Pengujian:</div>
                    <div class="small text-muted font-monospace p-2 rounded border bg-white" id="testOtpResponseContent" style="font-size: 0.82rem;">
                        -
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Gateway Fonnte -->
        <div class="card border-0 shadow-sm rounded-4" style="background: var(--bg-card);">
            <div class="card-header border-0 bg-transparent py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-cute-dark">
                    <i class="bi bi-hdd-network text-primary me-2"></i>Status Perangkat Fonnte
                </h6>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2" onclick="refreshFonnteDeviceStatus()" title="Refresh Status Perangkat">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
            <div class="card-body px-4 pb-4 pt-1 text-muted small">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>Status Koneksi:</span>
                    <span id="devStatusText" class="fw-bold <?= $fonnteStatus['connected'] ? 'text-success' : 'text-danger'; ?>">
                        <?= $fonnteStatus['connected'] ? '<i class="bi bi-check-circle-fill me-1"></i> Connected' : '<i class="bi bi-x-circle-fill me-1"></i> Disconnected'; ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>Nama Perangkat:</span>
                    <span class="fw-semibold text-dark" id="devNameText"><?= htmlspecialchars($fonnteStatus['name'] ?? '-'); ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>Nomor WhatsApp Sender:</span>
                    <span class="font-monospace fw-semibold" id="devPhoneText"><?= htmlspecialchars($fonnteStatus['device'] ?? '-'); ?></span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span>Sisa Kuota:</span>
                    <span class="fw-bold text-success" id="devQuotaText"><?= number_format((int)($fonnteStatus['quota'] ?? 0), 0, ',', '.'); ?> pesan</span>
                </div>

                <div class="mt-3 pt-2 border-top">
                    <a href="<?= BASEURL; ?>/adminSettings/whatsapp" class="text-primary text-decoration-none small fw-semibold">
                        <i class="bi bi-qr-code-scan me-1"></i> Buka Manajemen QR Pairing WhatsApp &rarr;
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Modal Konfirmasi Simpan OTP -->
<div class="modal fade" id="modalConfirmSaveOtp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-cute-dark">
                    <i class="bi bi-question-circle text-success me-2"></i>Konfirmasi Perubahan OTP
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-0 text-muted">
                    Apakah Anda yakin ingin memperbarui konfigurasi parameter WhatsApp OTP? Perubahan masa berlaku, batas percobaan, dan panjang digit akan langsung berlaku pada permintaan OTP berikutnya.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" onclick="document.getElementById('formOtpSettings').submit()">
                    Ya, Simpan Konfigurasi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleTokenVisibility() {
    const input = document.getElementById('fonnte_token');
    const btn = document.getElementById('btnToggleToken');
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="bi bi-eye-slash-fill"></i>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<i class="bi bi-eye-fill"></i>';
    }
}

function confirmSaveOtp() {
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmSaveOtp'));
    modal.show();
}

async function runOtpLiveTest() {
    const btn = document.getElementById('btnTestOtp');
    const spinner = document.getElementById('testOtpSpinner');
    const icon = document.getElementById('testOtpIcon');
    const btnText = document.getElementById('testOtpBtnText');

    const resultBox = document.getElementById('testOtpResultBox');
    const badgeStatus = document.getElementById('testOtpBadgeStatus');
    const latencyDisplay = document.getElementById('testOtpLatency');
    const responseBox = document.getElementById('testOtpResponseContent');

    const phone = document.getElementById('testOtpPhone').value.trim();
    const token = document.getElementById('fonnte_token').value.trim();
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    if (!phone) {
        alert('Silakan masukkan nomor WhatsApp tujuan tes terlebih dahulu.');
        document.getElementById('testOtpPhone').focus();
        return;
    }

    btn.disabled = true;
    spinner.classList.remove('d-none');
    icon.classList.add('d-none');
    btnText.innerText = 'Mengirim pesan...';

    try {
        const res = await fetch('<?= BASEURL; ?>/adminSettings/testOtp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                csrf_token: csrfToken,
                phone: phone,
                token: token
            })
        });

        const data = await res.json();
        resultBox.classList.remove('d-none');
        latencyDisplay.innerText = (data.latency_ms || 0) + ' ms';

        if (data.status) {
            badgeStatus.className = 'badge bg-success text-white';
            badgeStatus.innerText = 'Sukses Terkirim';
            responseBox.innerText = data.message + (data.code_sent ? '\nKode OTP Simulasi: ' + data.code_sent : '');
            responseBox.className = 'small text-success font-monospace p-2 rounded border bg-light';
        } else {
            badgeStatus.className = 'badge bg-danger text-white';
            badgeStatus.innerText = 'Pengiriman Gagal';
            responseBox.innerText = data.message || 'Terjadi kesalahan tidak diketahui.';
            responseBox.className = 'small text-danger font-monospace p-2 rounded border bg-light';
        }
    } catch (err) {
        resultBox.classList.remove('d-none');
        badgeStatus.className = 'badge bg-danger text-white';
        badgeStatus.innerText = 'Koneksi Error';
        latencyDisplay.innerText = '-';
        responseBox.innerText = 'Gagal menghubungi server untuk pengetesan: ' + err.message;
        responseBox.className = 'small text-danger font-monospace p-2 rounded border bg-light';
    } finally {
        btn.disabled = false;
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
        btnText.innerText = 'Kirim Pesan Tes OTP';
    }
}

async function refreshFonnteDeviceStatus() {
    try {
        const res = await fetch('<?= BASEURL; ?>/adminSettings/checkStatus');
        const data = await res.json();
        if (data.status) {
            const devStatusText = document.getElementById('devStatusText');
            if (data.connected) {
                devStatusText.className = 'fw-bold text-success';
                devStatusText.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Connected';
            } else {
                devStatusText.className = 'fw-bold text-danger';
                devStatusText.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> Disconnected';
            }
            document.getElementById('devNameText').innerText = data.name || '-';
            document.getElementById('devPhoneText').innerText = data.device || '-';
            document.getElementById('devQuotaText').innerText = (data.quota || 0) + ' pesan';
        }
    } catch (e) {
        console.error('Error refreshing Fonnte status:', e);
    }
}
</script>
