<h2 class="fw-bold mb-4 text-cute-dark">Checkout 🛍️</h2>

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card card-cute">
            <div class="card-body p-4">
                <h5 class="fw-bold border-bottom pb-3 mb-4">Informasi Pengiriman</h5>
                
                <form action="<?= BASEURL; ?>/CheckoutController/process" method="POST" id="checkoutForm">
                    <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <label for="shipping_name" class="form-label fw-semibold">Nama Penerima</label>
                        <input type="text" class="form-control" id="shipping_name" name="shipping_name" value="<?= htmlspecialchars($data['data']['user']['name'] ?? ($_SESSION['user_name'] ?? '')); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="shipping_phone" class="form-label fw-semibold">
                            <i class="bi bi-whatsapp text-success me-1"></i> No. Telepon / WhatsApp
                        </label>
                        <input type="tel" class="form-control" id="shipping_phone" name="shipping_phone" value="<?= htmlspecialchars($data['data']['user']['phone'] ?? ''); ?>" placeholder="Contoh: 081234567890" required>
                        <div class="form-text small text-muted">Konfirmasi pesanan akan dikirimkan otomatis ke nomor WhatsApp ini.</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="shipping_address" class="form-label fw-semibold">Alamat Lengkap</label>
                        <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required placeholder="Nama Jalan, Gedung, RT/RW, Kecamatan, Kota..."></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Metode Pembayaran</label>
                        <select class="form-select bg-light" name="payment_method" readonly>
                            <option value="cash" selected>Cash / Tunai</option>
                        </select>
                        <small class="text-muted">Saat ini kami hanya menerima pembayaran tunai (bayar di tempat/saat pengambilan). Status awal pembayaran adalah <strong>Belum Dibayar</strong>.</small>
                    </div>
                    
                    <button type="submit" class="btn btn-cute w-100 btn-lg shadow-sm" id="btnSubmitCheckout">
                        <i class="bi bi-bag-check-fill me-1"></i> <span id="btnSubmitText">Buat Pesanan</span>
                    </button>
                </form>

                <script>
                document.getElementById('checkoutForm').addEventListener('submit', function(e) {
                    const btn = document.getElementById('btnSubmitCheckout');
                    const text = document.getElementById('btnSubmitText');
                    if (btn && !btn.disabled) {
                        btn.disabled = true;
                        btn.classList.add('disabled');
                        text.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan Pesanan...';
                    }
                });
                </script>
            </div>
        </div>
    </div>
    
    <div class="col-lg-5">
        <div class="card card-cute">
            <div class="card-body p-4">
                <h5 class="fw-bold border-bottom pb-3 mb-4">Ringkasan Pesanan</h5>
                <ul class="list-group list-group-flush mb-4">
                    <?php foreach($data['data']['cart'] as $item) : ?>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 text-truncate" style="max-width: 200px;"><?= $item['name']; ?></h6>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Qty: <?= $item['quantity']; ?></span>
                                    <small class="text-muted">&times; Rp <?= number_format($item['price'], 0, ',', '.'); ?></small>
                                </div>
                            </div>
                            <span class="fw-bold">Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <div class="d-flex justify-content-between border-top pt-3">
                    <h5 class="fw-bold">Total</h5>
                    <h4 class="fw-bold text-danger">Rp <?= number_format($data['data']['total'], 0, ',', '.'); ?></h4>
                </div>
                
                <div class="alert alert-info mt-4 mb-0 small">
                    <strong>Informasi:</strong> Pembayaran dilakukan secara <strong>Tunai (Cash)</strong>. Anda dapat membayar saat mengambil pesanan atau langsung kepada penjual.
                </div>
            </div>
        </div>
    </div>
</div>
