<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-3 border-bottom">
    <div>
        <h6 class="card-category text-muted mb-1">EXECUTIVE OVERVIEW</h6>
        <h1 class="h2 fw-bold text-cute-dark d-flex align-items-center gap-2 mb-0">
            <span class="icon-pod icon-pod-md icon-pod-purple shadow-xs"><i class="bi bi-graph-up-arrow"></i></span>
            <span>Dashboard Owner</span>
        </h1>
        <p class="text-muted small mb-0 mt-1">Selamat datang, <strong><?= htmlspecialchars($data['data']['user_name']); ?></strong>. Ringkasan performa penjualan periode: <strong><?= $data['data']['start_date'] ?: 'Semua waktu' ?></strong> hingga <strong><?= $data['data']['end_date'] ?: 'Semua waktu' ?></strong>.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2 align-items-center">
        <form method="GET" action="<?= BASEURL; ?>/owner" class="d-flex align-items-center gap-2">
            <select name="filter" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                <option value="today" <?= $data['data']['filter'] == 'today' ? 'selected' : ''; ?>>Hari Ini</option>
                <option value="this_week" <?= $data['data']['filter'] == 'this_week' ? 'selected' : ''; ?>>Minggu Ini</option>
                <option value="this_month" <?= $data['data']['filter'] == 'this_month' ? 'selected' : ''; ?>>Bulan Ini</option>
                <option value="this_year" <?= $data['data']['filter'] == 'this_year' ? 'selected' : ''; ?>>Tahun Ini</option>
                <option value="custom" <?= $data['data']['filter'] == 'custom' ? 'selected' : ''; ?>>Custom Range</option>
            </select>
            <?php if($data['data']['filter'] == 'custom'): ?>
                <input type="date" name="start_date" class="form-control form-control-sm rounded-pill" value="<?= $data['data']['start_date'] ?>" required>
                <input type="date" name="end_date" class="form-control form-control-sm rounded-pill" value="<?= $data['data']['end_date'] ?>" required>
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">Filter</button>
            <?php endif; ?>
        </form>
        <a href="<?= BASEURL; ?>/AdminUserController" class="btn btn-sm btn-outline-primary rounded-pill px-3 d-inline-flex align-items-center gap-1 shadow-xs">
            <i class="bi bi-people-fill"></i> <span class="d-none d-lg-inline">Pengguna</span>
        </a>
        <a href="<?= BASEURL; ?>/OwnerAdminController" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center gap-1 shadow-xs">
            <i class="bi bi-person-badge-fill"></i> <span class="d-none d-lg-inline">Kelola Admin</span>
        </a>
    </div>
</div>

<!-- Stats Row -->
<!-- Stats Row -->
<div class="row mb-4 g-3">
    <div class="col-md-3 mb-2">
        <div class="card card-stat-gradient-pink text-white h-100 shadow-sm rounded-4 overflow-hidden card-hover-lift" style="border: none !important;">
            <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-category text-white-50 mb-1" style="font-size: 0.72rem;">TOTAL REVENUE</h6>
                    <h3 class="card-text fw-bold mb-0">Rp <?= number_format($data['data']['stats']['total_revenue'] ?? 0, 0, ',', '.'); ?></h3>
                </div>
                <div class="icon-pod icon-pod-lg icon-pod-circle bg-white bg-opacity-25 text-white shadow-sm">
                    <i class="bi bi-wallet2 fs-2"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card card-stat-gradient-blue text-white h-100 shadow-sm rounded-4 overflow-hidden card-hover-lift" style="border: none !important;">
            <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-category text-white-50 mb-1" style="font-size: 0.72rem;">TOTAL ORDERS</h6>
                    <h3 class="card-text fw-bold mb-0"><?= number_format($data['data']['stats']['total_orders'] ?? 0, 0, ',', '.'); ?></h3>
                </div>
                <div class="icon-pod icon-pod-lg icon-pod-circle bg-white bg-opacity-25 text-white shadow-sm">
                    <i class="bi bi-bag-check-fill fs-2"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card card-stat-gradient-green text-white h-100 shadow-sm rounded-4 overflow-hidden card-hover-lift" style="border: none !important;">
            <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-category text-white-50 mb-1" style="font-size: 0.72rem;">TOTAL CUSTOMERS</h6>
                    <h3 class="card-text fw-bold mb-0"><?= number_format($data['data']['stats']['total_customers'] ?? 0, 0, ',', '.'); ?></h3>
                </div>
                <div class="icon-pod icon-pod-lg icon-pod-circle bg-white bg-opacity-25 text-white shadow-sm">
                    <i class="bi bi-people-fill fs-2"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card card-stat-gradient-orange text-white h-100 shadow-sm rounded-4 overflow-hidden card-hover-lift" style="border: none !important;">
            <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-category text-white-50 mb-1" style="font-size: 0.72rem;">TOTAL PRODUCTS</h6>
                    <h3 class="card-text fw-bold mb-0"><?= number_format($data['data']['stats']['total_products'] ?? 0, 0, ',', '.'); ?></h3>
                </div>
                <div class="icon-pod icon-pod-lg icon-pod-circle bg-white bg-opacity-25 text-white shadow-sm">
                    <i class="bi bi-gem fs-2"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart Revenue -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm h-100 rounded-4">
            <div class="card-header bg-white py-3 rounded-top-4">
                <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <span class="icon-pod icon-pod-sm icon-pod-blue"><i class="bi bi-graph-up-arrow"></i></span>
                    <span>Tren Pendapatan Toko</span>
                </h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" style="width:100%; height:300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Order Status Summary -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100 rounded-4">
            <div class="card-header bg-white py-3 rounded-top-4">
                <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <span class="icon-pod icon-pod-sm icon-pod-pink"><i class="bi bi-pie-chart-fill"></i></span>
                    <span>Ringkasan Status Pesanan</span>
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-0">
                        <span class="d-flex align-items-center gap-2">
                            <span class="icon-pod icon-pod-xs icon-pod-amber"><i class="bi bi-hourglass-split"></i></span>
                            <span class="fw-medium">Menunggu Konfirmasi (Pending)</span>
                        </span>
                        <span class="badge bg-secondary rounded-pill px-3 py-1"><?= $data['data']['status_count']['pending'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-0">
                        <span class="d-flex align-items-center gap-2">
                            <span class="icon-pod icon-pod-xs icon-pod-blue"><i class="bi bi-check2-circle"></i></span>
                            <span class="fw-medium">Dikonfirmasi (Confirmed)</span>
                        </span>
                        <span class="badge bg-info rounded-pill px-3 py-1"><?= $data['data']['status_count']['confirmed'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-0">
                        <span class="d-flex align-items-center gap-2">
                            <span class="icon-pod icon-pod-xs icon-pod-indigo"><i class="bi bi-gear-wide-connected"></i></span>
                            <span class="fw-medium">Diproses (Processing)</span>
                        </span>
                        <span class="badge bg-primary rounded-pill px-3 py-1"><?= $data['data']['status_count']['processing'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-0">
                        <span class="d-flex align-items-center gap-2">
                            <span class="icon-pod icon-pod-xs icon-pod-warning"><i class="bi bi-truck"></i></span>
                            <span class="fw-medium">Dikirim (Shipped)</span>
                        </span>
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1"><?= $data['data']['status_count']['shipped'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-0">
                        <span class="d-flex align-items-center gap-2">
                            <span class="icon-pod icon-pod-xs icon-pod-emerald"><i class="bi bi-check-circle-fill"></i></span>
                            <span class="fw-medium">Selesai (Completed)</span>
                        </span>
                        <span class="badge bg-success rounded-pill px-3 py-1"><?= $data['data']['status_count']['completed'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-0">
                        <span class="d-flex align-items-center gap-2">
                            <span class="icon-pod icon-pod-xs icon-pod-rose"><i class="bi bi-x-circle-fill"></i></span>
                            <span class="fw-medium">Dibatalkan (Cancelled)</span>
                        </span>
                        <span class="badge bg-danger rounded-pill px-3 py-1"><?= $data['data']['status_count']['cancelled'] ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Selling Products -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Top Selling Products</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Sold</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($data['data']['top_products'])): ?>
                                <?php foreach($data['data']['top_products'] as $tp): ?>
                                <tr>
                                    <td><?= $tp['product_name'] ?></td>
                                    <td class="text-center"><?= $tp['total_sold'] ?></td>
                                    <td class="text-end">Rp <?= number_format($tp['total_revenue'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">Belum ada data penjualan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Recent Orders</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($data['data']['recent_orders'])): ?>
                                <?php foreach($data['data']['recent_orders'] as $ro): ?>
                                <tr>
                                    <td><small class="text-muted">#<?= $ro['order_number'] ?></small></td>
                                    <td><?= $ro['customer_name'] ?></td>
                                    <td>
                                        <?php
                                            $badgeClass = 'bg-secondary';
                                            if($ro['status'] == 'completed') $badgeClass = 'bg-success';
                                            if($ro['status'] == 'shipped') $badgeClass = 'bg-warning text-dark';
                                            if($ro['status'] == 'processing') $badgeClass = 'bg-primary';
                                            if($ro['status'] == 'confirmed') $badgeClass = 'bg-info text-dark';
                                            if($ro['status'] == 'cancelled') $badgeClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= ucfirst($ro['status']) ?></span>
                                    </td>
                                    <td class="text-end">Rp <?= number_format($ro['total_amount'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada pesanan terbaru.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    <?php
        $labels = [];
        $revenue = [];
        if(!empty($data['data']['chart_data'])) {
            foreach($data['data']['chart_data'] as $cd) {
                $labels[] = date('d M Y', strtotime($cd['date']));
                $revenue[] = $cd['revenue'];
            }
        }
    ?>
    
    var ctx = document.getElementById('revenueChart').getContext('2d');
    var revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Revenue (Rp)',
                data: <?= json_encode($revenue) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>
