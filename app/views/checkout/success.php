<div class="row justify-content-center mt-5">
    <div class="col-md-8 text-center">
        <div class="card shadow-sm border-0 py-5 rounded-4" style="background: var(--bg-card);">
            <div class="card-body">
                <div class="icon-pod icon-pod-xl icon-pod-circle icon-pod-gradient-emerald shadow-lg mb-4 icon-animate-bounce" style="width: 90px; height: 90px; font-size: 3rem;">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h2 class="fw-bold mt-2 text-cute-dark">Pesanan Berhasil Dibuat! 🎉</h2>
                <p class="lead text-muted">Terima kasih banyak telah berbelanja aksesoris di Nnaco_SA.</p>
                
                <div class="alert alert-light border d-inline-block px-5 py-3 mt-2 mb-3 rounded-4 shadow-xs" style="background: var(--bg-card-subtle);">
                    <p class="mb-1 text-muted small text-uppercase fw-semibold">Nomor Pesanan Anda:</p>
                    <h3 class="fw-bold text-primary font-monospace mb-0">#<?= $data['data']['order_number']; ?></h3>
                </div>

                <div class="d-block mb-3">
                    <div class="alert alert-success d-inline-flex align-items-center gap-2 px-4 py-2 rounded-pill text-start small mb-0 shadow-sm">
                        <span class="icon-pod icon-pod-xs icon-pod-emerald"><i class="bi bi-whatsapp"></i></span>
                        <span class="fw-medium">Konfirmasi rincian pesanan telah dikirimkan otomatis ke WhatsApp Anda.</span>
                    </div>
                </div>
                
                <p class="text-secondary mt-2 small">Silakan lakukan pembayaran tunai kepada penjual atau saat pengambilan pesanan sesuai ketentuan toko.</p>
                
                <div class="d-flex justify-content-center gap-2 mt-4">
                    <?php if (isset($_SESSION['user_id'])) : ?>
                        <a href="<?= BASEURL; ?>/CustomerController/orders" class="btn btn-cute-outline rounded-pill px-4 d-inline-flex align-items-center gap-2">
                            <i class="bi bi-receipt"></i> <span>Lihat Pesanan Saya</span>
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASEURL; ?>/ProductController" class="btn btn-cute rounded-pill px-4 d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-gem"></i> <span>Lanjut Belanja</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center mt-5">
    <div class="col-md-10">
        <div class="p-4 bg-light border rounded-3">
            <h4 class="fw-bold mb-4">Lengkapi Koleksi Anda</h4>
            <?php require_once '../app/views/components/recommendations.php'; ?>
        </div>
    </div>
</div>
