<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-3 border-bottom">
    <div>
        <h6 class="card-category text-muted mb-1">GATEWAY & AUDIT TRAIL</h6>
        <h1 class="h2 text-cute-dark fw-bold mb-0 d-flex align-items-center gap-2">
            <span class="icon-pod icon-pod-md icon-pod-emerald shadow-xs"><i class="bi bi-whatsapp"></i></span>
            <span>Log Notifikasi WhatsApp</span>
        </h1>
        <p class="text-muted small mb-0 mt-1">Pantau status pengiriman pesan, kuota Fonnte API, dan audit trail notifikasi pesanan otomatis.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 d-flex gap-2">
        <a href="<?= BASEURL; ?>/NotificationController" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1 shadow-xs">
            <i class="bi bi-arrow-clockwise"></i> <span>Segarkan Data</span>
        </a>
    </div>
</div>

<?php
$deviceDetails = $data['data']['device_details'] ?? [];
$isDeviceConnected = ($deviceDetails['device_status'] ?? '') === 'connect';
$deviceNumber = $deviceDetails['device'] ?? '';
$deviceQuota = $deviceDetails['quota'] ?? '0';
?>
<!-- Status Koneksi Fonnte & Statistik Notifikasi -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="background: var(--bg-card-subtle, #f8f9fa); border-left: 4px solid <?= $isDeviceConnected ? '#198754' : '#dc3545'; ?> !important;">
            <div class="d-flex align-items-center mb-2">
                <div class="rounded-circle <?= $isDeviceConnected ? 'bg-success text-success' : 'bg-danger text-danger'; ?> bg-opacity-10 p-3 me-3 fs-3">
                    <i class="bi <?= $isDeviceConnected ? 'bi-whatsapp' : 'bi-exclamation-triangle-fill'; ?>"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Status Fonnte Gateway</span>
                    <strong class="fs-6 <?= $isDeviceConnected ? 'text-success' : 'text-danger'; ?>">
                        <?= $isDeviceConnected ? 'Terhubung (' . htmlspecialchars($deviceNumber) . ')' : 'Terputus (Offline)'; ?>
                    </strong>
                    <div class="small text-muted" style="font-size: 0.72rem;">
                        <?= $isDeviceConnected ? 'Sisa Kuota: ' . htmlspecialchars($deviceQuota) . ' pesan' : 'Perlu scan QR code WA'; ?>
                    </div>
                </div>
            </div>
            <div class="mt-auto pt-2 border-top">
                <?php if ($isDeviceConnected) : ?>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'owner') : ?>
                    <form action="<?= BASEURL; ?>/NotificationController/disconnectDevice" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin memutuskan koneksi WhatsApp saat ini untuk pairing dengan nomor lain?')">
                        <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token']; ?>">
                        <button type="submit" class="btn btn-xs btn-outline-danger w-100 rounded-pill py-1 fw-semibold" style="font-size: 0.73rem;">
                            <i class="bi bi-x-circle me-1"></i> Putus Koneksi / Ganti Nomor
                        </button>
                    </form>
                    <?php else : ?>
                    <span class="badge bg-success bg-opacity-25 text-success w-100 py-1">
                        <i class="bi bi-check-circle-fill me-1"></i> Gateway Siap Kirim
                    </span>
                    <?php endif; ?>
                <?php else : ?>
                    <button type="button" class="btn btn-xs btn-success w-100 rounded-pill py-1 fw-bold shadow-sm" onclick="openFonnteQrModal()" style="font-size: 0.75rem;">
                        <i class="bi bi-qr-code-scan me-1"></i> Scan QR Sambungkan WA
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="background: var(--bg-card-subtle, #f8f9fa); border-left: 4px solid #0d6efd !important;">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary fs-3">
                    <i class="bi bi-send-check-fill"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Total Terkirim</span>
                    <strong class="fs-4 text-primary"><?= number_format($data['data']['count_sent']); ?></strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="background: var(--bg-card-subtle, #f8f9fa); border-left: 4px solid #dc3545 !important;">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3 text-danger fs-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Gagal Dikirim</span>
                    <strong class="fs-4 text-danger"><?= number_format($data['data']['count_failed']); ?></strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="background: var(--bg-card-subtle, #f8f9fa); border-left: 4px solid #ffc107 !important;">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3 text-warning fs-3">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Dalam Antrean (Pending)</span>
                    <strong class="fs-4 text-dark"><?= number_format($data['data']['count_pending']); ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'owner') : ?>
<!-- ============================================================= -->
<!-- FORM PENGATURAN PENERIMA NOTIFIKASI WHATSAPP (KHUSUS OWNER)   -->
<!-- ============================================================= -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-cute-dark">
                    <i class="bi bi-sliders text-primary me-2"></i> Konfigurasi Penerima Notifikasi WhatsApp
                </h5>
                <span class="text-muted small">Atur nomor WhatsApp toko dan tentukan penerima notifikasi otomatis</span>
            </div>
            <span class="badge bg-warning bg-opacity-25 text-dark px-3 py-2 rounded-pill fw-semibold border border-warning">
                <i class="bi bi-shield-lock-fill me-1"></i> Khusus Hak Akses Owner
            </span>
        </div>
    </div>
    <div class="card-body p-4 pt-2">
        <form action="<?= BASEURL; ?>/NotificationController/saveSettings" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token']; ?>">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="order_notification_phone" class="form-label fw-bold">
                        <i class="bi bi-whatsapp text-success me-1"></i> Nomor WhatsApp Owner / Toko (Penerima Notifikasi Pesanan Baru)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone"></i></span>
                        <input type="tel" class="form-control" id="order_notification_phone" name="order_notification_phone" 
                               value="<?= htmlspecialchars($data['data']['settings']['order_notification_phone'] ?? ''); ?>" 
                               placeholder="Contoh: 081234567890 atau 6281234567890" required>
                    </div>
                    <div class="form-text text-muted small mt-1">
                        Nomor ini akan menerima rincian setiap kali Customer berhasil membuat pesanan baru secara tunai (Cash).
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="admin_notification_phones" class="form-label fw-bold">
                        <i class="bi bi-people text-primary me-1"></i> Nomor WhatsApp Admin Tambahan (Opsional)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-person-lines-fill"></i></span>
                        <input type="text" class="form-control" id="admin_notification_phones" name="admin_notification_phones" 
                               value="<?= htmlspecialchars($data['data']['settings']['admin_notification_phones'] ?? ''); ?>" 
                               placeholder="Contoh: 081234567891, 081234567892">
                    </div>
                    <div class="form-text text-muted small mt-1">
                        Pisahkan dengan tanda koma jika terdapat lebih dari satu nomor Admin yang perlu menerima salinan pesanan.
                    </div>
                </div>
            </div>

            <!-- Toggle Options -->
            <div class="row g-3 mb-4 p-3 rounded-4" style="background: var(--bg-card-subtle, #f8f9fa); border: 1px solid var(--border-color, #e9ecef);">
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="order_notify_owner" name="order_notify_owner" value="1" 
                               <?= ($data['data']['settings']['order_notify_owner'] ?? '1') === '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-semibold" for="order_notify_owner">
                            Kirim Notifikasi ke Owner
                        </label>
                        <div class="text-muted small">Notifikasi pesanan baru masuk ke nomor Owner.</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="order_notify_customer" name="order_notify_customer" value="1" 
                               <?= ($data['data']['settings']['order_notify_customer'] ?? '1') === '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-semibold" for="order_notify_customer">
                            Kirim Konfirmasi ke Customer
                        </label>
                        <div class="text-muted small">Kirim konfirmasi nomor pesanan & rincian ke WhatsApp pembeli.</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="order_notify_admin" name="order_notify_admin" value="1" 
                               <?= ($data['data']['settings']['order_notify_admin'] ?? '0') === '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-semibold" for="order_notify_admin">
                            Kirim Salinan ke Admin
                        </label>
                        <div class="text-muted small">Kirim salinan pesanan ke nomor Admin tambahan di atas.</div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <button type="submit" class="btn btn-cute px-4 fw-bold shadow-sm">
                    <i class="bi bi-save-fill me-1"></i> Simpan Konfigurasi Penerima
                </button>
        </form>

                <!-- Tombol Test Send -->
                <form action="<?= BASEURL; ?>/NotificationController/testSend" method="POST" class="d-inline" onsubmit="return confirm('Kirim pesan uji coba ke nomor WhatsApp Owner yang tersimpan?')">
                    <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token']; ?>">
                    <button type="submit" class="btn btn-outline-success btn-sm px-3 fw-bold rounded-pill">
                        <i class="bi bi-send-fill me-1"></i> Tes Kirim Pesan WhatsApp ke Owner
                    </button>
                </form>
            </div>
    </div>
</div>
<?php endif; ?>

<!-- ============================================================= -->
<!-- TABEL LOG RIWAYAT NOTIFIKASI WHATSAPP                         -->
<!-- ============================================================= -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-cute-dark">
                <i class="bi bi-journal-text text-primary me-2"></i> Riwayat Pengiriman Notifikasi
            </h5>
            <span class="text-muted small">Log detail pengiriman pesan WhatsApp ke Owner, Customer, dan Admin</span>
        </div>

        <!-- Filter Status Buttons -->
        <div class="btn-group btn-group-sm" role="group">
            <a href="<?= BASEURL; ?>/NotificationController" class="btn <?= empty($data['data']['filter_status']) ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                Semua (<?= $data['data']['count_all']; ?>)
            </a>
            <a href="<?= BASEURL; ?>/NotificationController?status=sent" class="btn <?= $data['data']['filter_status'] === 'sent' ? 'btn-success' : 'btn-outline-secondary'; ?>">
                Terkirim (<?= $data['data']['count_sent']; ?>)
            </a>
            <a href="<?= BASEURL; ?>/NotificationController?status=failed" class="btn <?= $data['data']['filter_status'] === 'failed' ? 'btn-danger' : 'btn-outline-secondary'; ?>">
                Gagal (<?= $data['data']['count_failed']; ?>)
            </a>
            <a href="<?= BASEURL; ?>/NotificationController?status=pending" class="btn <?= $data['data']['filter_status'] === 'pending' ? 'btn-warning text-dark' : 'btn-outline-secondary'; ?>">
                Pending (<?= $data['data']['count_pending']; ?>)
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" style="width: 5%;">#</th>
                    <th scope="col">Waktu</th>
                    <th scope="col">No. Pesanan</th>
                    <th scope="col">Penerima</th>
                    <th scope="col">No. WhatsApp</th>
                    <th scope="col">Tipe Notifikasi</th>
                    <th scope="col">Status</th>
                    <th scope="col">Percobaan</th>
                    <th scope="col">Keterangan / Respons</th>
                    <th scope="col" class="text-center" style="width: 10%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['data']['logs'])) : ?>
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Belum ada riwayat notifikasi WhatsApp yang tercatat.
                        </td>
                    </tr>
                <?php else : ?>
                    <?php 
                    $no = ($data['data']['current_page'] - 1) * 20 + 1;
                    foreach ($data['data']['logs'] as $log) : 
                        // Masking nomor telepon untuk privasi (contoh: 62819****9613)
                        $phone = $log['recipient_phone'];
                        $maskedPhone = '-';
                        if (!empty($phone) && strlen($phone) >= 8) {
                            $maskedPhone = substr($phone, 0, 5) . '****' . substr($phone, -3);
                        } elseif (!empty($phone)) {
                            $maskedPhone = htmlspecialchars($phone);
                        }

                        // Badge recipient type
                        $recBadge = 'bg-secondary';
                        $recText = 'Lainnya';
                        if ($log['recipient_type'] === 'owner') {
                            $recBadge = 'bg-danger bg-opacity-10 text-danger border border-danger';
                            $recText = '👑 Owner';
                        } elseif ($log['recipient_type'] === 'customer') {
                            $recBadge = 'bg-success bg-opacity-10 text-success border border-success';
                            $recText = '🛍️ Customer';
                        } elseif ($log['recipient_type'] === 'admin') {
                            $recBadge = 'bg-primary bg-opacity-10 text-primary border border-primary';
                            $recText = '🛠️ Admin';
                        }

                        // Badge status
                        $statusBadge = 'bg-warning text-dark';
                        $statusIcon = 'bi-hourglass-split';
                        $statusLabel = 'Pending';
                        if ($log['message_status'] === 'sent') {
                            $statusBadge = 'bg-success';
                            $statusIcon = 'bi-check-circle-fill';
                            $statusLabel = 'Terkirim';
                        } elseif ($log['message_status'] === 'failed') {
                            $statusBadge = 'bg-danger';
                            $statusIcon = 'bi-x-circle-fill';
                            $statusLabel = 'Gagal';
                        }
                    ?>
                    <tr>
                        <td class="text-muted small"><?= $no++; ?></td>
                        <td>
                            <small class="fw-semibold d-block text-dark"><?= date('d/m/Y H:i', strtotime($log['created_at'])); ?> WIB</small>
                            <?php if (!empty($log['sent_at'])) : ?>
                                <small class="text-muted" style="font-size: 0.72rem;">Sukses: <?= date('H:i:s', strtotime($log['sent_at'])); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= BASEURL; ?>/AdminOrderController/detail/<?= $log['order_id']; ?>" class="fw-bold text-decoration-none">
                                <?= htmlspecialchars($log['order_number']); ?>
                            </a>
                            <small class="text-muted d-block" style="font-size: 0.73rem;">
                                <?= htmlspecialchars($log['shipping_name'] ?? ''); ?> (Rp <?= number_format($log['total_amount'] ?? 0, 0, ',', '.'); ?>)
                            </small>
                        </td>
                        <td>
                            <span class="badge <?= $recBadge; ?> rounded-pill px-2 py-1">
                                <?= $recText; ?>
                            </span>
                        </td>
                        <td>
                            <code class="small text-dark"><?= $maskedPhone; ?></code>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border small fw-normal">
                                <?= htmlspecialchars($log['notification_type']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $statusBadge; ?> rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1">
                                <i class="bi <?= $statusIcon; ?>"></i> <?= $statusLabel; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-muted border"><?= (int)$log['attempt_count']; ?>x</span>
                        </td>
                        <td>
                            <small class="text-muted d-block text-break" style="max-width: 250px; font-size: 0.75rem;">
                                <?php if (!empty($log['error_message'])) : ?>
                                    <span class="text-danger"><i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($log['error_message']); ?></span>
                                <?php elseif ($log['message_status'] === 'sent') : ?>
                                    <span class="text-success"><i class="bi bi-check2 me-1"></i>Respons HTTP <?= htmlspecialchars($log['provider_response_code'] ?? '200'); ?> (OK)</span>
                                <?php else : ?>
                                    <span class="text-muted">Dalam proses kirim</span>
                                <?php endif; ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <?php if ($log['message_status'] === 'failed') : ?>
                                <form action="<?= BASEURL; ?>/NotificationController/retry" method="POST" class="d-inline" onsubmit="return confirm('Kirim ulang notifikasi pesanan ini?')">
                                    <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token']; ?>">
                                    <input type="hidden" name="log_id" value="<?= $log['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 rounded-pill" title="Kirim ulang notifikasi">
                                        <i class="bi bi-arrow-repeat"></i> Coba Lagi
                                    </button>
                                </form>
                            <?php else : ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($data['data']['total_pages'] > 1) : ?>
    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="text-muted small">
            Menampilkan halaman <?= $data['data']['current_page']; ?> dari <?= $data['data']['total_pages']; ?> (Total <?= $data['data']['total_logs']; ?> log)
        </span>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $data['data']['total_pages']; $p++) : ?>
                    <li class="page-item <?= $p == $data['data']['current_page'] ? 'active' : ''; ?>">
                        <a class="page-link" href="<?= BASEURL; ?>/NotificationController?page=<?= $p; ?><?= !empty($data['data']['filter_status']) ? '&status=' . urlencode($data['data']['filter_status']) : ''; ?>">
                            <?= $p; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Scan QR Code Fonnte -->
<div class="modal fade" id="modalFonnteQr" tabindex="-1" aria-labelledby="modalFonnteQrLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-cute-dark" id="modalFonnteQrLabel">
                    <i class="bi bi-whatsapp text-success me-2"></i> Sambungkan WhatsApp Gateway Fonnte
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p class="text-muted small mb-3">
                    Scan QR Code ini menggunakan aplikasi WhatsApp di HP toko Anda untuk mengaktifkan pengiriman notifikasi otomatis pesanan dan OTP.
                </p>
                
                <div id="notifQrLoading" class="my-4">
                    <div class="spinner-border text-success" role="status"></div>
                    <div class="small text-muted mt-2">Memuat QR Code resmi dari Fonnte API...</div>
                </div>

                <div id="notifQrContainer" class="d-none my-2">
                    <div class="d-inline-block p-2 bg-white rounded-3 shadow-sm border mb-3">
                        <img id="notifQrImg" src="" alt="QR Code WhatsApp Fonnte" class="img-fluid" style="width: 240px; height: 240px;">
                    </div>
                    <div>
                        <span class="badge px-3 py-2 rounded-pill" id="notifQrStatusBadge" style="background: rgba(255, 193, 7, 0.2); color: #856404; border: 1px solid #ffeeba;">
                            <span class="spinner-grow spinner-grow-sm me-1" role="status" aria-hidden="true" style="width: 0.6rem; height: 0.6rem;"></span>
                            Menunggu Scan WhatsApp...
                        </span>
                    </div>
                </div>

                <div class="text-start bg-light p-3 rounded-3 small mt-3 border">
                    <strong><i class="bi bi-info-circle-fill text-primary me-1"></i> Langkah Scan WhatsApp:</strong>
                    <ol class="mb-0 ps-3 mt-1 text-muted" style="font-size: 0.78rem;">
                        <li>Buka aplikasi <b>WhatsApp</b> di ponsel Anda</li>
                        <li>Ketuk <b>Menu (⋮)</b> (Android) atau <b>Pengaturan</b> (iPhone)</li>
                        <li>Pilih <b>Perangkat Tertaut (Linked Devices)</b> > <b>Tautkan Perangkat</b></li>
                        <li>Arahkan kamera ke QR Code di atas</li>
                    </ol>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="loadNotifFonnteQr()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang QR
                </button>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
let notifFonntePollInterval = null;

function openFonnteQrModal() {
    let modalEl = document.getElementById('modalFonnteQr');
    if (!modalEl) return;
    let modal = bootstrap.Modal.getInstance(modalEl);
    if (!modal) {
        modal = new bootstrap.Modal(modalEl);
    }
    modal.show();
    loadNotifFonnteQr();
}

function loadNotifFonnteQr() {
    const loading = document.getElementById('notifQrLoading');
    const container = document.getElementById('notifQrContainer');
    const img = document.getElementById('notifQrImg');
    const badge = document.getElementById('notifQrStatusBadge');

    if (loading) loading.classList.remove('d-none');
    if (container) container.classList.add('d-none');

    fetch('<?= BASEURL; ?>/NotificationController/fonnteQr')
    .then(res => res.json())
    .then(data => {
        if (loading) loading.classList.add('d-none');
        if (data.qr_image) {
            if (img) img.src = data.qr_image;
            if (container) container.classList.remove('d-none');
            if (badge) {
                badge.className = 'badge px-3 py-2 rounded-pill bg-warning text-dark';
                badge.innerHTML = '<span class="spinner-grow spinner-grow-sm me-1" role="status"></span> Menunggu Scan WhatsApp...';
            }
            startNotifFonntePolling();
        } else if (data.connected) {
            if (container) container.classList.remove('d-none');
            if (badge) {
                badge.className = 'badge px-3 py-2 rounded-pill bg-success text-white';
                badge.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> WhatsApp Sudah Terhubung (${data.device || ''})`;
            }
        } else {
            if (loading) {
                loading.classList.remove('d-none');
                loading.innerHTML = `<div class="text-danger small"><i class="bi bi-exclamation-triangle-fill me-1"></i> Gagal memuat QR dari Fonnte. Klik tombol muat ulang di bawah.</div>`;
            }
        }
    })
    .catch(err => {
        if (loading) {
            loading.classList.remove('d-none');
            loading.innerHTML = `<div class="text-danger small">Gagal menghubungi server Fonnte. Cek koneksi Anda.</div>`;
        }
    });
}

function startNotifFonntePolling() {
    clearInterval(notifFonntePollInterval);
    notifFonntePollInterval = setInterval(() => {
        fetch('<?= BASEURL; ?>/NotificationController/checkStatus')
        .then(res => res.json())
        .then(data => {
            if (data.connected) {
                clearInterval(notifFonntePollInterval);
                const badge = document.getElementById('notifQrStatusBadge');
                if (badge) {
                    badge.className = 'badge px-3 py-2 rounded-pill bg-success text-white';
                    badge.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Berhasil Terhubung! (${data.device || ''})`;
                }
                setTimeout(() => {
                    location.reload();
                }, 1500);
            }
        })
        .catch(() => {});
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('modalFonnteQr');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function() {
            clearInterval(notifFonntePollInterval);
        });
    }
});
</script>

