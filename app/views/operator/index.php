<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <h6 class="card-category text-muted mb-1">OPERASIONAL & PESANAN</h6>
                        <h3 class="fw-bold mb-0">Dashboard Operator</h3>
                        <p class="text-muted small mb-0">Selamat bertugas, <strong><?= htmlspecialchars($data['data']['user_name']); ?></strong>.</p>
                    </div>
                    <span class="badge rounded-pill bg-light text-dark border px-3 py-2 fw-semibold">
                        <i class="bi bi-shield-check text-info me-1"></i> Operator Access
                    </span>
                </div>
                
                <hr class="opacity-10 my-3">
                
                <div class="row mt-4 g-3">
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card bg-warning text-dark text-center shadow-sm rounded-4 h-100 card-hover-lift" style="border: none !important;">
                            <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                                <div class="icon-pod icon-pod-md icon-pod-circle bg-dark bg-opacity-10 text-dark mb-2 shadow-xs">
                                    <i class="bi bi-hourglass-split fs-4"></i>
                                </div>
                                <h6 class="text-uppercase fw-semibold mb-1 text-dark-50" style="font-size: 0.75rem;">Pending</h6>
                                <h2 class="fw-bold mb-0"><?= $data['data']['stats']['total_pending'] ?? 0; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card bg-info text-white text-center shadow-sm rounded-4 h-100 card-hover-lift" style="border: none !important;">
                            <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                                <div class="icon-pod icon-pod-md icon-pod-circle bg-white bg-opacity-25 text-white mb-2 shadow-xs">
                                    <i class="bi bi-gear-wide-connected fs-4"></i>
                                </div>
                                <h6 class="text-uppercase fw-semibold mb-1 text-white-50" style="font-size: 0.75rem;">Processing</h6>
                                <h2 class="fw-bold mb-0"><?= $data['data']['stats']['total_processing'] ?? 0; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card bg-primary text-white text-center shadow-sm rounded-4 h-100 card-hover-lift" style="border: none !important;">
                            <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                                <div class="icon-pod icon-pod-md icon-pod-circle bg-white bg-opacity-25 text-white mb-2 shadow-xs">
                                    <i class="bi bi-truck fs-4"></i>
                                </div>
                                <h6 class="text-uppercase fw-semibold mb-1 text-white-50" style="font-size: 0.75rem;">Shipped</h6>
                                <h2 class="fw-bold mb-0"><?= $data['data']['stats']['total_shipped'] ?? 0; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card bg-success text-white text-center shadow-sm rounded-4 h-100 card-hover-lift" style="border: none !important;">
                            <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                                <div class="icon-pod icon-pod-md icon-pod-circle bg-white bg-opacity-25 text-white mb-2 shadow-xs">
                                    <i class="bi bi-check-circle-fill fs-4"></i>
                                </div>
                                <h6 class="text-uppercase fw-semibold mb-1 text-white-50" style="font-size: 0.75rem;">Completed</h6>
                                <h2 class="fw-bold mb-0"><?= $data['data']['stats']['total_completed'] ?? 0; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex flex-wrap gap-2">
                    <a href="<?= BASEURL; ?>/OperatorOrderController" class="btn btn-dark rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                        <span class="icon-pod icon-pod-xs icon-pod-emerald"><i class="bi bi-inbox-fill"></i></span>
                        <span class="fw-semibold">Kelola Pesanan Masuk &rarr;</span>
                    </a>
                    <a href="<?= BASEURL; ?>/settings" class="btn btn-outline-secondary rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2">
                        <span class="icon-pod icon-pod-xs icon-pod-purple"><i class="bi bi-palette-fill"></i></span>
                        <span class="fw-semibold">Pengaturan Tema</span>
                    </a>
                    <a href="<?= BASEURL; ?>/auth/logout" class="btn btn-outline-danger rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="fw-semibold">Logout (Keluar)</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
