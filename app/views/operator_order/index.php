<!-- Header Section -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-3 border-bottom">
    <div>
        <h6 class="card-category text-muted mb-1">OPERASIONAL PESANAN</h6>
        <h1 class="h2 fw-bold text-cute-dark d-flex align-items-center gap-2 mb-0">
            <span class="icon-pod icon-pod-md icon-pod-emerald shadow-xs"><i class="bi bi-inbox-fill"></i></span>
            <span>Kelola Pesanan Masuk</span>
        </h1>
        <p class="text-muted small mb-0 mt-1">Verifikasi order baru, perbarui progres pengemasan, dan masukkan nomor resi kurir.</p>
    </div>
</div>

<!-- Filter Bar -->
<div class="card shadow-sm rounded-4 mb-4 border">
    <div class="card-body p-3">
        <form class="row g-2 align-items-center" action="<?= BASEURL; ?>/OperatorOrderController" method="GET">
            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari Resi, No. Pesanan, atau Nama..." value="<?= htmlspecialchars($data['data']['search'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-6 col-md-4">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Status Pesanan</option>
                    <option value="pending" <?= ($data['data']['status'] ?? '') == 'pending' ? 'selected' : ''; ?>>Menunggu Konfirmasi (Pending)</option>
                    <option value="confirmed" <?= ($data['data']['status'] ?? '') == 'confirmed' ? 'selected' : ''; ?>>Dikonfirmasi (Confirmed)</option>
                    <option value="processing" <?= ($data['data']['status'] ?? '') == 'processing' ? 'selected' : ''; ?>>Sedang Diproses (Processing)</option>
                    <option value="shipped" <?= ($data['data']['status'] ?? '') == 'shipped' ? 'selected' : ''; ?>>Sedang Dikirim (Shipped)</option>
                    <option value="completed" <?= ($data['data']['status'] ?? '') == 'completed' ? 'selected' : ''; ?>>Selesai (Completed)</option>
                    <option value="cancelled" <?= ($data['data']['status'] ?? '') == 'cancelled' ? 'selected' : ''; ?>>Dibatalkan (Cancelled)</option>
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-1 justify-content-end">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <?php if(!empty($data['data']['search']) || !empty($data['data']['status'])): ?>
                    <a href="<?= BASEURL; ?>/OperatorOrderController" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card shadow-sm rounded-4 border overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
            <span class="icon-pod icon-pod-xs icon-pod-emerald"><i class="bi bi-table"></i></span>
            <span>Daftar Antrean Pesanan</span>
        </h5>
        <span class="badge rounded-pill bg-light text-dark border px-3 py-1">
            Total <?= count($data['data']['orders']); ?> Pesanan
        </span>
    </div>

    <div class="card-body p-0">
        <?php if(empty($data['data']['orders'])): ?>
            <div class="text-center py-5">
                <div class="icon-pod icon-pod-xl icon-pod-circle icon-pod-emerald mb-3 shadow-xs">
                    <i class="bi bi-check2-all fs-1"></i>
                </div>
                <h5 class="fw-bold">Tidak ada antrean pesanan</h5>
                <p class="text-muted small mb-3">Semua pesanan saat ini sudah terproses dengan baik.</p>
                <a href="<?= BASEURL; ?>/OperatorOrderController" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fw-semibold" style="font-size: 0.74rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4">No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Tanggal Masuk</th>
                            <th>Total Tagihan</th>
                            <th>Status</th>
                            <th class="text-end pe-4" style="min-width: 130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['data']['orders'] as $order) : ?>
                            <?php 
                                $badgeClass = 'bg-secondary';
                                $statusIcon = 'bi-clock';
                                if($order['status'] == 'pending') {
                                    $badgeClass = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                    $statusIcon = 'bi-hourglass-split';
                                } elseif($order['status'] == 'confirmed') {
                                    $badgeClass = 'bg-info-subtle text-info border border-info-subtle';
                                    $statusIcon = 'bi-check2-circle';
                                } elseif($order['status'] == 'processing') {
                                    $badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                                    $statusIcon = 'bi-gear-wide-connected';
                                } elseif($order['status'] == 'shipped') {
                                    $badgeClass = 'bg-warning text-dark';
                                    $statusIcon = 'bi-truck';
                                } elseif($order['status'] == 'completed') {
                                    $badgeClass = 'bg-success text-white shadow-xs';
                                    $statusIcon = 'bi-check-circle-fill';
                                } elseif($order['status'] == 'cancelled') {
                                    $badgeClass = 'bg-danger text-white';
                                    $statusIcon = 'bi-x-circle-fill';
                                }
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-cute-dark d-flex align-items-center gap-1">
                                        <i class="bi bi-hash text-muted"></i>
                                        <span><?= htmlspecialchars($order['order_number']); ?></span>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.75rem;">ID #ORD-<?= $order['id']; ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold text-cute-dark"><?= htmlspecialchars($order['customer_name']); ?></div>
                                </td>
                                <td>
                                    <div class="text-muted" style="font-size: 0.84rem;">
                                        <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($order['created_at'])); ?>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.75rem;"><?= date('H:i', strtotime($order['created_at'])); ?> WIB</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary" style="font-size: 0.95rem;">
                                        Rp <?= number_format($order['total_amount'], 0, ',', '.'); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-3 py-1 <?= $badgeClass; ?> d-inline-flex align-items-center gap-1 fw-semibold">
                                        <i class="bi <?= $statusIcon; ?>"></i> <?= strtoupper($order['status']); ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="<?= BASEURL; ?>/OperatorOrderController/detail/<?= $order['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1 shadow-xs">
                                        <i class="bi bi-pencil-square"></i> <span>Proses</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
