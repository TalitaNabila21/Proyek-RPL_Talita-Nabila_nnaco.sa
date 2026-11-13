<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Proses Pesanan: <?= $data['data']['order']['order_number']; ?></h1>
    <a href="<?= BASEURL; ?>/OperatorOrderController" class="btn btn-sm btn-outline-secondary">Kembali</a>
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
                <form action="<?= BASEURL; ?>/OperatorOrderController/updateStatus" method="POST">
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
                    
                    <button type="submit" class="btn btn-primary">Simpan Perubahan Status</button>
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
