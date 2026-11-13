<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Detail Pesanan: <?= $data['data']['order']['order_number']; ?></h1>
    <a href="<?= BASEURL; ?>/CustomerController/orders" class="btn btn-sm btn-outline-secondary">Kembali</a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold">
                Item yang Dipesan
            </div>
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
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total Pembayaran:</td>
                            <td class="text-end fw-bold text-danger fs-5">Rp <?= number_format($data['data']['order']['total_amount'], 0, ',', '.'); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold">
                Informasi Pengiriman
            </div>
            <div class="card-body">
                <p class="mb-1 text-muted small">Status Pesanan</p>
                <h5 class="mb-3 text-uppercase fw-bold"><?= $data['data']['order']['status']; ?></h5>
                
                <p class="mb-1 text-muted small">Status Pembayaran</p>
                <h5 class="mb-3 text-uppercase fw-bold <?= isset($data['data']['order']['payment_status']) && $data['data']['order']['payment_status'] == 'paid' ? 'text-success' : 'text-danger' ?>">
                    <?= isset($data['data']['order']['payment_status']) ? $data['data']['order']['payment_status'] : 'unpaid' ?>
                </h5>
                
                <p class="mb-1 text-muted small">Metode Pembayaran</p>
                <p class="mb-3 fw-bold text-uppercase"><?= isset($data['data']['order']['payment_method']) ? $data['data']['order']['payment_method'] : 'cash' ?></p>
                
                
                <p class="mb-1 text-muted small">Nama Penerima</p>
                <p class="mb-3 fw-bold"><?= $data['data']['order']['shipping_name']; ?></p>
                
                <p class="mb-1 text-muted small">No. Telepon</p>
                <p class="mb-3 fw-bold"><?= $data['data']['order']['shipping_phone']; ?></p>
                
                <p class="mb-1 text-muted small">Alamat Lengkap</p>
                <p class="mb-4 fw-bold"><?= nl2br($data['data']['order']['shipping_address']); ?></p>

                <?php if($data['data']['order']['status'] == 'shipped' || $data['data']['order']['status'] == 'completed') : ?>
                    <hr>
                    <h6 class="fw-bold mb-3">Informasi Kurir</h6>
                    <p class="mb-1 text-muted small">Kurir</p>
                    <p class="mb-3 fw-bold text-uppercase"><?= $data['data']['order']['shipping_courier']; ?></p>
                    
                    <p class="mb-1 text-muted small">Nomor Resi</p>
                    <p class="mb-0 fw-bold user-select-all"><?= $data['data']['order']['tracking_number']; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
