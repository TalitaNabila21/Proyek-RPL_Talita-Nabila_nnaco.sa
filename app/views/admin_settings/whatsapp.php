<?php
if (isset($data['data']['data']) && is_array($data['data']['data'])) {
    $data = $data['data']['data'];
} elseif (isset($data['data']) && is_array($data['data'])) {
    $data = $data['data'];
}
$csrfToken = $data['csrf_token'] ?? '';
$maskedToken = $data['fonnte_token_masked'] ?? '';
$fonnteStatus = $data['fonnte_status'] ?? [];
$notifyOwner = ($data['order_notify_owner'] ?? '1') === '1';
$notifyCustomer = ($data['order_notify_customer'] ?? '1') === '1';
$notifyAdmin = ($data['order_notify_admin'] ?? '0') === '1';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
    <div>
        <h6 class="card-category text-muted mb-1">NOTIFICATIONS & MESSAGING</h6>
        <h1 class="h2 fw-bold text-cute-dark mb-0 d-flex align-items-center gap-2">
            <span class="icon-pod icon-pod-md icon-pod-success shadow-xs"><i class="bi bi-whatsapp"></i></span>
            <span>Pengaturan Notifikasi WhatsApp</span>
        </h1>
        <p class="text-muted small mb-0 mt-1">Kelola gateway Fonnte API, pairing perangkat WhatsApp, nomor tujuan notifikasi toko, dan otomatisasi pesan status pesanan.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="<?= BASEURL; ?>/NotificationController" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1">
            <i class="bi bi-journal-text"></i> <span>Buka Log Riwayat WhatsApp</span>
        </a>
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
    
    <!-- Kolom Formulir Pengaturan Notifikasi -->
    <div class="col-lg-7">
        <form action="<?= BASEURL; ?>/adminSettings/saveWhatsapp" method="POST" id="formWhatsappSettings">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>">

            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--bg-card);">
                <div class="card-header border-0 bg-transparent py-3 px-4">
                    <h5 class="fw-bold mb-0 text-cute-dark">
                        <i class="bi bi-sliders text-success me-2"></i>Konfigurasi Penerima & Notifikasi
                    </h5>
                </div>

                <div class="card-body px-4 pb-4 pt-1">

                    <!-- Token Fonnte API -->
                    <div class="mb-4">
                        <label for="wa_fonnte_token" class="form-label fw-bold text-cute-dark d-flex justify-content-between">
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
                            <input type="password" class="form-control" id="wa_fonnte_token" name="fonnte_token" placeholder="<?= !empty($maskedToken) ? 'Biarkan kosong jika tidak ingin mengubah (' . $maskedToken . ')' : 'Masukkan token Fonnte'; ?>" autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleWaTokenVisibility()" id="btnToggleWaToken" title="Tampilkan / Sembunyikan Token">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </div>
                        <div class="form-text text-muted small mt-1">
                            Token Fonnte dapat diperoleh dari <a href="https://md.fonnte.com" target="_blank" rel="noopener noreferrer" class="text-success text-decoration-none fw-semibold">md.fonnte.com</a>.
                        </div>
                    </div>

                    <!-- Nomor WhatsApp Owner / Toko -->
                    <div class="mb-4">
                        <label for="order_notification_phone" class="form-label fw-bold text-cute-dark">
                            Nomor WhatsApp Owner / Toko (Penerima Notifikasi Pesanan Baru):
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone-fill text-success"></i></span>
                            <input type="tel" class="form-control" id="order_notification_phone" name="order_notification_phone" value="<?= htmlspecialchars($data['order_notification_phone'] ?? ''); ?>" placeholder="Contoh: 081234567890 atau 6281234567890">
                        </div>
                        <div class="form-text text-muted small mt-1">
                            Nomor ini akan menerima pesan WhatsApp otomatis setiap kali customer berhasil checkout pesanan baru.
                        </div>
                    </div>

                    <!-- Toggle Aturan Notifikasi -->
                    <div class="mb-4 p-3 rounded-4" style="background: var(--bg-card-subtle); border: 1px solid var(--border-color);">
                        <label class="form-label fw-bold text-cute-dark mb-3">Otomatisasi Pengiriman Pesan:</label>

                        <!-- Toggle Owner -->
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="order_notify_owner" name="order_notify_owner" value="1" <?= $notifyOwner ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-semibold text-cute-dark" for="order_notify_owner">
                                Kirim Notifikasi Pesanan Baru ke Owner / Toko
                            </label>
                            <div class="text-muted small">Pemberitahuan instan begitu pesanan berhasil disimpan di database.</div>
                        </div>

                        <!-- Toggle Customer -->
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="order_notify_customer" name="order_notify_customer" value="1" <?= $notifyCustomer ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-semibold text-cute-dark" for="order_notify_customer">
                                Kirim Notifikasi Status Pesanan ke Customer
                            </label>
                            <div class="text-muted small">Kirim update WhatsApp saat pesanan Diterima Toko, Diproses, Sedang Diantar (dengan resi), dan Diterima Customer.</div>
                        </div>

                        <!-- Toggle Admin Tambahan -->
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="order_notify_admin" name="order_notify_admin" value="1" <?= $notifyAdmin ? 'checked' : ''; ?> onchange="toggleAdminPhonesInput(this.checked)">
                            <label class="form-check-label fw-semibold text-cute-dark" for="order_notify_admin">
                                Teruskan Notifikasi Pesanan Baru ke Admin Tambahan
                            </label>
                            <div class="text-muted small">Kirim salinan notifikasi pesanan ke nomor pengelola lain jika diperlukan.</div>
                        </div>
                    </div>

                    <!-- Input Nomor Admin Tambahan -->
                    <div class="mb-4 <?= $notifyAdmin ? '' : 'd-none'; ?>" id="adminPhonesContainer">
                        <label for="admin_notification_phones" class="form-label fw-bold text-cute-dark">
                            Daftar Nomor WhatsApp Admin Tambahan:
                        </label>
                        <textarea class="form-control" id="admin_notification_phones" name="admin_notification_phones" rows="2" placeholder="081234567891, 089876543210 (pisahkan dengan koma)"><?= htmlspecialchars($data['admin_notification_phones'] ?? ''); ?></textarea>
                        <div class="form-text text-muted small">
                            Masukkan beberapa nomor dipisahkan dengan tanda koma (<code>,</code>).
                        </div>
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <button type="button" class="btn btn-success px-4 py-2 rounded-pill fw-bold shadow-sm d-inline-flex align-items-center gap-2" onclick="confirmSaveWhatsapp()">
                            <i class="bi bi-save2-fill"></i> Simpan Pengaturan Notifikasi
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <!-- Kolom Status Gateway & Live Test -->
    <div class="col-lg-5">
        
        <!-- Status Gateway Fonnte & Action Buttons -->
        <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--bg-card); border-top: 4px solid #25d366 !important;">
            <div class="card-header border-0 bg-transparent py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-cute-dark">
                    <i class="bi bi-hdd-network text-success me-2"></i>Status Perangkat WhatsApp
                </h5>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2" onclick="refreshGatewayDetails()" title="Perbarui Status">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>

            <div class="card-body px-4 pb-4 pt-1">
                <div class="d-flex align-items-center justify-content-between p-3 rounded-4 mb-3" style="background: var(--bg-card-subtle); border: 1px solid var(--border-color);">
                    <div>
                        <div class="small text-muted mb-1">Status Koneksi Gateway:</div>
                        <div class="h5 fw-bold mb-0" id="gwStatusBadge">
                            <?php if ($fonnteStatus['connected']): ?>
                                <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Connected</span>
                            <?php else: ?>
                                <span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i> Disconnected</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted mb-1">Sisa Kuota:</div>
                        <div class="fw-bold fs-6 text-success" id="gwQuotaBadge"><?= number_format((int)($fonnteStatus['quota'] ?? 0), 0, ',', '.'); ?> pesan</div>
                    </div>
                </div>

                <div class="small text-muted mb-3">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span>Nama Device:</span>
                        <span class="fw-semibold text-dark" id="gwNameDisplay"><?= htmlspecialchars($fonnteStatus['name'] ?? '-'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span>Nomor WhatsApp:</span>
                        <span class="font-monospace fw-semibold" id="gwPhoneDisplay"><?= htmlspecialchars($fonnteStatus['device'] ?? '-'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span>Masa Aktif:</span>
                        <span class="fw-semibold" id="gwExpiredDisplay"><?= htmlspecialchars($fonnteStatus['expired'] ?? '-'); ?></span>
                    </div>
                </div>

                <!-- Action Pairing & Disconnect Buttons -->
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary rounded-pill flex-fill py-2 fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm" onclick="openFonnteQrModal()">
                        <i class="bi bi-qr-code-scan"></i> Scan QR Pairing
                    </button>
                    <?php if ($fonnteStatus['connected']): ?>
                        <button type="button" class="btn btn-outline-danger rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center justify-content-center gap-1 shadow-sm" onclick="confirmDisconnectDevice()" title="Putus koneksi perangkat saat ini">
                            <i class="bi bi-box-arrow-left"></i> Putus
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Live Test Send Message Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--bg-card); border-left: 4px solid #25d366 !important;">
            <div class="card-header border-0 bg-transparent py-3 px-4">
                <h5 class="fw-bold mb-0 text-cute-dark d-flex align-items-center gap-2">
                    <i class="bi bi-chat-dots-fill text-success"></i> Uji Coba Kirim Pesan WhatsApp
                </h5>
            </div>
            <div class="card-body px-4 pb-4 pt-1">
                <p class="text-muted small mb-3">
                    Kirim pesan notifikasi uji coba ke nomor tujuan sembarang untuk memverifikasi kesiapan gateway secara real-time.
                </p>

                <div class="mb-3">
                    <label for="testWaPhone" class="form-label fw-semibold small text-cute-dark">Nomor WhatsApp Tujuan:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-whatsapp text-success"></i></span>
                        <input type="tel" class="form-control" id="testWaPhone" placeholder="Contoh: 081234567890">
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="button" class="btn btn-outline-success rounded-pill py-2 fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm" id="btnTestWa" onclick="runWaLiveTest()">
                        <span class="spinner-border spinner-border-sm d-none" id="testWaSpinner" role="status" aria-hidden="true"></span>
                        <i class="bi bi-send-fill" id="testWaIcon"></i>
                        <span id="testWaBtnText">Kirim Pesan Uji Coba</span>
                    </button>
                </div>

                <!-- Box Hasil Uji Coba -->
                <div id="testWaResultBox" class="d-none rounded-3 p-3" style="background: var(--bg-card-subtle); border: 1px solid var(--border-color);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge" id="testWaBadgeStatus">Status</span>
                        <span class="small text-muted font-monospace" id="testWaLatency">0 ms</span>
                    </div>
                    <div class="small fw-semibold text-cute-dark mb-1" id="testWaResultTitle">Hasil Pengujian:</div>
                    <div class="small text-muted font-monospace p-2 rounded border bg-white" id="testWaResponseContent" style="font-size: 0.82rem;">
                        -
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Modal Scan QR Code Pairing -->
<div class="modal fade" id="modalFonnteQr" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-cute-dark d-flex align-items-center gap-2">
                    <i class="bi bi-qr-code text-success"></i> Pindai QR Code WhatsApp
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopQrPolling()"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="text-muted small mb-3">Buka aplikasi WhatsApp di HP Anda &rarr; Pengaturan / Titik Tiga &rarr; <strong>Perangkat Tertaut</strong> &rarr; Tautkan Perangkat, lalu arahkan kamera ke kode di bawah:</p>
                
                <div id="qrLoadingSpinner" class="py-5">
                    <div class="spinner-border text-success" role="status"></div>
                    <div class="small text-muted mt-2">Memuat kode QR dari server Fonnte...</div>
                </div>

                <div id="qrImageContainer" class="d-none text-center">
                    <img id="fonnteQrImage" src="" alt="Fonnte QR Code" class="img-fluid rounded-4 shadow-sm border p-2 mb-3" style="max-width: 250px;">
                    <div class="small text-muted">
                        <i class="bi bi-stopwatch text-warning me-1"></i> QR Code akan otomatis diperbarui setiap beberapa detik.
                    </div>
                </div>

                <div id="qrConnectedNotice" class="d-none alert alert-success border-0 py-3 rounded-4 mb-0">
                    <i class="bi bi-check-circle-fill fs-2 text-success d-block mb-2"></i>
                    <h6 class="fw-bold mb-1">Perangkat Berhasil Terhubung!</h6>
                    <p class="small text-muted mb-0" id="qrConnectedDevicePhone">-</p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal" onclick="stopQrPolling()">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Putus Perangkat -->
<div class="modal fade" id="modalConfirmDisconnect" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Putus Koneksi WhatsApp
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-0 text-muted">
                    Apakah Anda yakin ingin memutuskan koneksi perangkat WhatsApp saat ini dari gateway Fonnte? Notifikasi dan OTP tidak dapat terkirim sampai perangkat dipindai kembali.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                <form action="<?= BASEURL; ?>/adminSettings/disconnectDevice" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>">
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                        Ya, Putuskan Perangkat
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Simpan Pengaturan -->
<div class="modal fade" id="modalConfirmSaveWa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-cute-dark">
                    <i class="bi bi-question-circle text-success me-2"></i>Konfirmasi Perubahan Notifikasi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-0 text-muted">
                    Apakah Anda yakin ingin menyimpan perubahan konfigurasi Notifikasi WhatsApp ini?
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" onclick="document.getElementById('formWhatsappSettings').submit()">
                    Ya, Simpan Pengaturan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let qrInterval = null;

function toggleWaTokenVisibility() {
    const input = document.getElementById('wa_fonnte_token');
    const btn = document.getElementById('btnToggleWaToken');
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="bi bi-eye-slash-fill"></i>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<i class="bi bi-eye-fill"></i>';
    }
}

function toggleAdminPhonesInput(checked) {
    const container = document.getElementById('adminPhonesContainer');
    if (checked) {
        container.classList.remove('d-none');
    } else {
        container.classList.add('d-none');
    }
}

function confirmSaveWhatsapp() {
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmSaveWa'));
    modal.show();
}

function confirmDisconnectDevice() {
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmDisconnect'));
    modal.show();
}

async function runWaLiveTest() {
    const btn = document.getElementById('btnTestWa');
    const spinner = document.getElementById('testWaSpinner');
    const icon = document.getElementById('testWaIcon');
    const btnText = document.getElementById('testWaBtnText');

    const resultBox = document.getElementById('testWaResultBox');
    const badgeStatus = document.getElementById('testWaBadgeStatus');
    const latencyDisplay = document.getElementById('testWaLatency');
    const responseBox = document.getElementById('testWaResponseContent');

    const phone = document.getElementById('testWaPhone').value.trim();
    const token = document.getElementById('wa_fonnte_token').value.trim();
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    if (!phone) {
        alert('Silakan masukkan nomor WhatsApp tujuan tes terlebih dahulu.');
        document.getElementById('testWaPhone').focus();
        return;
    }

    btn.disabled = true;
    spinner.classList.remove('d-none');
    icon.classList.add('d-none');
    btnText.innerText = 'Mengirim pesan...';

    try {
        const res = await fetch('<?= BASEURL; ?>/adminSettings/testWhatsapp', {
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
            responseBox.innerText = data.message;
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
        btnText.innerText = 'Kirim Pesan Uji Coba';
    }
}

async function refreshGatewayDetails() {
    try {
        const res = await fetch('<?= BASEURL; ?>/adminSettings/checkStatus');
        const data = await res.json();
        if (data.status) {
            const statusBadge = document.getElementById('gwStatusBadge');
            if (data.connected) {
                statusBadge.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Connected</span>';
            } else {
                statusBadge.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i> Disconnected</span>';
            }
            document.getElementById('gwQuotaBadge').innerText = (data.quota || 0) + ' pesan';
            document.getElementById('gwNameDisplay').innerText = data.name || '-';
            document.getElementById('gwPhoneDisplay').innerText = data.device || '-';
            document.getElementById('gwExpiredDisplay').innerText = data.expired || '-';
        }
    } catch (e) {
        console.error('Error refreshing gateway status:', e);
    }
}

function openFonnteQrModal() {
    const modal = new bootstrap.Modal(document.getElementById('modalFonnteQr'));
    modal.show();
    loadFonnteQr();
    if (!qrInterval) {
        qrInterval = setInterval(loadFonnteQr, 6000);
    }
}

function stopQrPolling() {
    if (qrInterval) {
        clearInterval(qrInterval);
        qrInterval = null;
    }
}

async function loadFonnteQr() {
    const spinner = document.getElementById('qrLoadingSpinner');
    const imgContainer = document.getElementById('qrImageContainer');
    const qrImg = document.getElementById('fonnteQrImage');
    const connectedNotice = document.getElementById('qrConnectedNotice');
    const connectedPhone = document.getElementById('qrConnectedDevicePhone');

    try {
        const res = await fetch('<?= BASEURL; ?>/adminSettings/fonnteQr');
        const data = await res.json();

        spinner.classList.add('d-none');

        if (data.connected) {
            imgContainer.classList.add('d-none');
            connectedNotice.classList.remove('d-none');
            connectedPhone.innerText = 'Nomor Sender: ' + (data.device || '-');
            stopQrPolling();
            refreshGatewayDetails();
        } else if (data.qr_image) {
            connectedNotice.classList.add('d-none');
            imgContainer.classList.remove('d-none');
            qrImg.src = data.qr_image;
        }
    } catch (err) {
        console.error('Error loading QR Code:', err);
    }
}
</script>
