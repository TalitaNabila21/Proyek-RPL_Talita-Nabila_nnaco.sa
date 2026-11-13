<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-3 border-bottom">
    <div>
        <h6 class="card-category text-muted mb-1">DETAIL TRANSAKSI</h6>
        <h1 class="h2 fw-bold text-cute-dark d-flex align-items-center gap-2 mb-0">
            <span class="icon-pod icon-pod-md icon-pod-indigo shadow-xs"><i class="bi bi-receipt-cutoff"></i></span>
            <span>Pesanan #<?= htmlspecialchars($data['data']['order']['order_number']); ?></span>
        </h1>
        <p class="text-muted small mb-0 mt-1">Kelola rincian item, perbarui status pengiriman, kurir, resi, dan pantau log notifikasi WhatsApp.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="<?= BASEURL; ?>/AdminOrderController" class="btn btn-outline-secondary rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1 shadow-xs">
            <i class="bi bi-arrow-left"></i> <span>Kembali ke Daftar</span>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold">Detail Item</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['data']['items'] as $item) : ?>
                        <tr>
                            <td class="fw-medium"><?= $item['product_name']; ?></td>
                            <td>Rp <?= number_format($item['price'], 0, ',', '.'); ?></td>
                            <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1 fs-6 rounded-pill"><?= $item['quantity']; ?></span></td>
                            <td class="text-end fw-bold">Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold">Update Status Pesanan</div>
            <div class="card-body">
                <form action="<?= BASEURL; ?>/AdminOrderController/updateStatus" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token']; ?>">
                    <input type="hidden" name="order_id" value="<?= $data['data']['order']['id']; ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Ubah Status</label>
                            <select class="form-select" name="status" id="orderStatus" required>
                                <option value="pending" <?= $data['data']['order']['status'] == 'pending' ? 'selected' : ''; ?>>Menunggu Konfirmasi (Pending)</option>
                                <option value="confirmed" <?= $data['data']['order']['status'] == 'confirmed' ? 'selected' : ''; ?>>Pesanan Diterima oleh Toko (Confirmed)</option>
                                <option value="processing" <?= $data['data']['order']['status'] == 'processing' ? 'selected' : ''; ?>>Sedang Diproses (Processing)</option>
                                <option value="shipped" <?= $data['data']['order']['status'] == 'shipped' ? 'selected' : ''; ?>>Sedang Diantar / Dikirim (Shipped)</option>
                                <option value="completed" <?= $data['data']['order']['status'] == 'completed' ? 'selected' : ''; ?>>Pesanan Diterima Customer (Completed)</option>
                                <option value="cancelled" <?= $data['data']['order']['status'] == 'cancelled' ? 'selected' : ''; ?>>Dibatalkan (Cancelled)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Pembayaran</label>
                            <select class="form-select" name="payment_status" required>
                                <option value="unpaid" <?= isset($data['data']['order']['payment_status']) && $data['data']['order']['payment_status'] == 'unpaid' ? 'selected' : ''; ?>>Belum Dibayar (Unpaid)</option>
                                <option value="paid" <?= isset($data['data']['order']['payment_status']) && $data['data']['order']['payment_status'] == 'paid' ? 'selected' : ''; ?>>Sudah Dibayar (Paid)</option>
                                <option value="refunded" <?= isset($data['data']['order']['payment_status']) && $data['data']['order']['payment_status'] == 'refunded' ? 'selected' : ''; ?>>Dikembalikan (Refunded)</option>
                            </select>
                        </div>
                    </div>

                    <div id="shippingFields" style="display: <?= $data['data']['order']['status'] == 'shipped' || $data['data']['order']['status'] == 'completed' ? 'block' : 'none'; ?>;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Kurir Pengiriman</label>
                                <input type="text" class="form-control" name="shipping_courier" value="<?= $data['data']['order']['shipping_courier']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Resi (Tracking Number)</label>
                                <input type="text" class="form-control" name="tracking_number" value="<?= $data['data']['order']['tracking_number']; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-check2-circle fs-5"></i>
                        <span>Simpan Perubahan Status</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold">Informasi Pelanggan</div>
            <div class="card-body">
                <p class="mb-1 text-muted small">Status Saat Ini</p>
                <h5 class="mb-3 text-uppercase fw-bold text-primary"><?= $data['data']['order']['status']; ?></h5>
                
                <p class="mb-1 text-muted small">Status Pembayaran</p>
                <h5 class="mb-3 text-uppercase fw-bold <?= isset($data['data']['order']['payment_status']) && $data['data']['order']['payment_status'] == 'paid' ? 'text-success' : 'text-danger' ?>">
                    <?= isset($data['data']['order']['payment_status']) ? $data['data']['order']['payment_status'] : 'unpaid' ?>
                </h5>
                
                <p class="mb-1 text-muted small">Metode Pembayaran</p>
                <p class="mb-3 fw-bold text-uppercase"><?= isset($data['data']['order']['payment_method']) ? $data['data']['order']['payment_method'] : 'cash' ?></p>
                
                <p class="mb-1 text-muted small">Nama Penerima</p>
                <p class="mb-3 fw-bold"><?= $data['data']['order']['shipping_name']; ?></p>
                
                <p class="mb-1 text-muted small">No. Telepon / WhatsApp</p>
                <p class="mb-3 fw-bold"><?= $data['data']['order']['shipping_phone']; ?> <a href="https://wa.me/<?= $data['data']['order']['shipping_phone']; ?>" target="_blank" class="badge bg-success text-decoration-none">Chat WA</a></p>
                
                <p class="mb-1 text-muted small">Alamat Lengkap</p>
                <p class="mb-3 fw-bold"><?= nl2br($data['data']['order']['shipping_address']); ?></p>
                
                <p class="mb-1 text-muted small">Waktu Pesanan Dibuat</p>
                <p class="mb-0 fw-bold"><?= date('d M Y H:i', strtotime($data['data']['order']['created_at'])); ?></p>
            </div>
        </div>

        <!-- Riwayat Notifikasi WhatsApp untuk Pesanan Ini -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-whatsapp text-success me-1"></i> Notifikasi WhatsApp</span>
                <span class="badge bg-light text-muted border"><?= count($data['data']['notifications'] ?? []); ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($data['data']['notifications'])) : ?>
                    <div class="p-3 text-center text-muted small">
                        Belum ada riwayat notifikasi untuk pesanan ini.
                    </div>
                <?php else : ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($data['data']['notifications'] as $notif) : 
                            $statusClass = $notif['message_status'] === 'sent' ? 'text-success' : ($notif['message_status'] === 'failed' ? 'text-danger' : 'text-warning');
                            $statusIcon = $notif['message_status'] === 'sent' ? 'bi-check-circle-fill' : ($notif['message_status'] === 'failed' ? 'bi-x-circle-fill' : 'bi-clock-fill');
                            $phone = $notif['recipient_phone'];
                            $maskedPhone = strlen($phone) >= 8 ? substr($phone, 0, 5) . '****' . substr($phone, -3) : $phone;
                        ?>
                        <li class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge <?= $notif['recipient_type'] === 'owner' ? 'bg-danger bg-opacity-10 text-danger border border-danger' : 'bg-success bg-opacity-10 text-success border border-success' ?> rounded-pill small">
                                    <?= strtoupper($notif['recipient_type']); ?>
                                </span>
                                <span class="small fw-bold <?= $statusClass; ?>">
                                    <i class="bi <?= $statusIcon; ?> me-1"></i> <?= ucfirst($notif['message_status']); ?>
                                </span>
                            </div>
                            <div class="small text-muted mb-1">
                                <code class="text-dark"><?= $maskedPhone; ?></code> &bull; <?= htmlspecialchars($notif['notification_type']); ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted" style="font-size: 0.72rem;">
                                    <?= date('d/m/Y H:i', strtotime($notif['created_at'])); ?> WIB
                                </small>
                                <?php if ($notif['message_status'] === 'failed') : ?>
                                    <form action="<?= BASEURL; ?>/NotificationController/retry" method="POST" class="d-inline" onsubmit="return confirm('Kirim ulang notifikasi ini?')">
                                        <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token']; ?>">
                                        <input type="hidden" name="log_id" value="<?= $notif['id']; ?>">
                                        <input type="hidden" name="redirect_back" value="<?= BASEURL; ?>/AdminOrderController/detail/<?= $data['data']['order']['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill" style="font-size: 0.72rem;">
                                            <i class="bi bi-arrow-repeat"></i> Kirim Ulang
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($notif['error_message'])) : ?>
                                <div class="text-danger small mt-1" style="font-size: 0.72rem;">
                                    <i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($notif['error_message']); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('orderStatus').addEventListener('change', function() {
        var shippingFields = document.getElementById('shippingFields');
        if (this.value === 'shipped' || this.value === 'completed') {
            shippingFields.style.display = 'block';
        } else {
            shippingFields.style.display = 'none';
        }
    });
</script>
