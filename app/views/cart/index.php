<h2 class="fw-bold mb-4 text-cute-dark d-flex align-items-center gap-2">
    <span class="icon-pod icon-pod-md icon-pod-pink shadow-xs"><i class="bi bi-bag-heart-fill"></i></span>
    <span>Keranjang Belanja</span>
</h2>

<?php if(empty($data['data']['cart'])) : ?>
    <div class="p-5 text-center bg-white rounded-4 shadow-sm border my-4">
        <div class="icon-pod icon-pod-xl icon-pod-circle icon-pod-pink mb-3 shadow-xs">
            <i class="bi bi-bag-heart fs-1"></i>
        </div>
        <h4 class="fw-bold text-cute-dark">Keranjang Belanja Masih Kosong</h4>
        <p class="text-muted mb-4">Yuk cari aksesoris estetik dan cantik untuk melengkapi gayamu hari ini!</p>
        <a href="<?= BASEURL; ?>/ProductController" class="btn btn-cute rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-gem"></i> <span>Mulai Belanja Sekarang</span>
        </a>
    </div>
<?php else : ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-cute mb-4 overflow-hidden shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th style="width: 130px;">Qty</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($data['data']['cart'] as $item) : ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if($item['image']) : ?>
                                            <img src="<?= BASEURL; ?>/assets/images/products/<?= $item['image']; ?>" class="rounded-3 me-3 object-fit-cover shadow-xs" width="60" height="60">
                                        <?php endif; ?>
                                        <a href="<?= BASEURL; ?>/ProductController/detail/<?= $item['slug']; ?>" class="text-decoration-none text-dark fw-bold">
                                            <?= $item['name']; ?>
                                        </a>
                                    </div>
                                </td>
                                <td>Rp <?= number_format($item['price'], 0, ',', '.'); ?></td>
                                <td>
                                    <form action="<?= BASEURL; ?>/CartController/update" method="POST" class="d-inline-block">
                                        <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token']; ?>">
                                        <input type="hidden" name="product_id" value="<?= $item['id']; ?>">
                                        
                                        <div class="qty-control-box">
                                            <button type="submit" name="quantity" value="<?= $item['quantity'] - 1 ?>" class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;" title="Kurangi"><i class="bi bi-dash"></i></button>
                                            <span class="qty-num"><?= $item['quantity']; ?></span>
                                            <button type="submit" name="quantity" value="<?= $item['quantity'] + 1 ?>" class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;" <?= $item['quantity'] >= $item['stock'] ? 'disabled' : '' ?> title="Tambah"><i class="bi bi-plus"></i></button>
                                        </div>
                                    </form>
                                </td>
                                <td class="fw-bold">Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                <td>
                                    <form action="<?= BASEURL; ?>/CartController/remove/<?= $item['id']; ?>" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1 shadow-xs" title="Hapus item">
                                            <i class="bi bi-trash3-fill"></i> <span>Hapus</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card card-cute shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold border-bottom pb-3 mb-3 d-flex align-items-center gap-2">
                        <span class="icon-pod icon-pod-xs icon-pod-purple"><i class="bi bi-receipt"></i></span>
                        <span>Ringkasan Belanja</span>
                    </h5>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Total Pembayaran</span>
                        <span class="fw-bold fs-5 text-danger">Rp <?= number_format($data['data']['total'], 0, ',', '.'); ?></span>
                    </div>
                    
                    <a href="<?= BASEURL; ?>/CheckoutController" class="btn btn-cute w-100 btn-lg mb-2 d-inline-flex align-items-center justify-content-center gap-2 shadow-sm">
                        <i class="bi bi-credit-card-2-front-fill fs-5"></i> <span>Lanjut ke Checkout</span>
                    </a>
                    <a href="<?= BASEURL; ?>/ProductController" class="btn btn-cute-outline w-100 d-inline-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-arrow-left"></i> <span>Lanjut Belanja</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
