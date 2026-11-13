<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
    <h1 class="h2 fw-bold text-cute-dark d-flex align-items-center gap-2">
        <span class="icon-pod icon-pod-md icon-pod-pink shadow-xs"><i class="bi bi-receipt"></i></span>
        <span>Riwayat Pesanan</span>
    </h1>
    <a href="<?= BASEURL; ?>/CustomerController" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> <span>Kembali ke Profil</span>
    </a>
</div>

<?php if(empty($data['data']['orders'])) : ?>
    <div class="p-5 text-center bg-white rounded-4 shadow-sm border my-4">
        <div class="icon-pod icon-pod-xl icon-pod-circle icon-pod-blue mb-3 shadow-xs">
            <i class="bi bi-bag-x fs-1"></i>
        </div>
        <h4 class="fw-bold text-cute-dark">Belum ada riwayat pesanan</h4>
        <p class="text-muted mb-4">Anda belum pernah melakukan pemesanan aksesoris di nnaco_sa.</p>
        <a href="<?= BASEURL; ?>/ProductController" class="btn btn-cute rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-gem"></i> <span>Mulai Belanja Sekarang</span>
        </a>
    </div>
<?php else : ?>
    <div class="table-responsive bg-white rounded-4 shadow-sm border overflow-hidden">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Pesanan</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Status Pesanan</th>
                    <th>Status Pembayaran</th>
                    <th>Metode</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data['data']['orders'] as $order) : ?>
                <tr>
                    <td class="fw-bold font-monospace text-primary">#<?= $order['order_number']; ?></td>
                    <td><?= date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                    <td class="fw-bold">Rp <?= number_format($order['total_amount'], 0, ',', '.'); ?></td>
                    <td>
                        <?php 
                            $status = $order['status'] ?? 'pending';
                            if ($status == 'completed') : ?>
                                <span class="badge bg-success rounded-pill px-3 py-1"><i class="bi bi-check-circle-fill me-1"></i> Selesai</span>
                            <?php elseif ($status == 'shipped') : ?>
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1"><i class="bi bi-truck me-1"></i> Dikirim</span>
                            <?php elseif ($status == 'processing') : ?>
                                <span class="badge bg-info text-dark rounded-pill px-3 py-1"><i class="bi bi-gear-wide-connected me-1"></i> Diproses</span>
                            <?php elseif ($status == 'cancelled') : ?>
                                <span class="badge bg-danger rounded-pill px-3 py-1"><i class="bi bi-x-circle-fill me-1"></i> Dibatalkan</span>
                            <?php else : ?>
                                <span class="badge bg-secondary rounded-pill px-3 py-1"><i class="bi bi-hourglass-split me-1"></i> Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                            $paymentStatus = $order['payment_status'] ?? 'unpaid';
                            if ($paymentStatus == 'paid') : ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1"><i class="bi bi-check-lg me-1"></i> Lunas</span>
                            <?php elseif ($paymentStatus == 'refunded') : ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1"><i class="bi bi-arrow-return-left me-1"></i> Dikembalikan</span>
                            <?php else : ?>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1"><i class="bi bi-clock me-1"></i> Belum Lunas</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-uppercase small fw-bold">
                        <span class="badge bg-light text-dark border rounded-pill px-2 py-1"><?= $order['payment_method'] ?? 'CASH'; ?></span>
                    </td>
                    <td>
                        <a href="<?= BASEURL; ?>/CustomerController/orderDetail/<?= $order['id']; ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3 d-inline-flex align-items-center gap-1 shadow-xs">
                            <i class="bi bi-eye"></i> <span>Detail</span>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
