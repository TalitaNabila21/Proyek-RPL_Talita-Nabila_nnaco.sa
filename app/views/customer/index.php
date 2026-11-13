<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 border-start border-4 border-info">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="icon-pod icon-pod-lg icon-pod-circle icon-pod-blue shadow-sm">
                        <i class="bi bi-person-circle fs-1"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-cute-dark">Halo, <?= $data['data']['user_name']; ?>! 🎀</h3>
                        <p class="text-muted small mb-0">Selamat datang di halaman Profil dan Dashboard Customer nnaco_sa.</p>
                    </div>
                </div>
                
                <hr>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white shadow-sm rounded-4 overflow-hidden card-hover-lift" style="border: none !important;">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-white-50 text-uppercase fw-semibold mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Pesanan</h6>
                                    <h3 class="fw-bold mb-0"><?= $data['data']['total_orders'] ?? 0; ?></h3>
                                    <small class="text-white-50" style="font-size: 0.75rem;">Semua transaksi</small>
                                </div>
                                <div class="icon-pod icon-pod-md icon-pod-circle bg-white bg-opacity-25 text-white shadow-xs">
                                    <i class="bi bi-bag-check-fill fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark shadow-sm rounded-4 overflow-hidden card-hover-lift" style="border: none !important;">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-dark-50 text-uppercase fw-semibold mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Sedang Diproses</h6>
                                    <h3 class="fw-bold mb-0"><?= $data['data']['pending_orders'] ?? 0; ?></h3>
                                    <small class="text-dark-50" style="font-size: 0.75rem;">Menunggu / Dikirim</small>
                                </div>
                                <div class="icon-pod icon-pod-md icon-pod-circle bg-dark bg-opacity-10 text-dark shadow-xs">
                                    <i class="bi bi-hourglass-split fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white shadow-sm rounded-4 overflow-hidden card-hover-lift" style="border: none !important;">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-white-50 text-uppercase fw-semibold mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Pesanan Selesai</h6>
                                    <h3 class="fw-bold mb-0"><?= $data['data']['completed_orders'] ?? 0; ?></h3>
                                    <small class="text-white-50" style="font-size: 0.75rem;">Berhasil diterima</small>
                                </div>
                                <div class="icon-pod icon-pod-md icon-pod-circle bg-white bg-opacity-25 text-white shadow-xs">
                                    <i class="bi bi-patch-check-fill fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <span class="icon-pod icon-pod-xs icon-pod-pink"><i class="bi bi-person-vcard-fill"></i></span>
                    <span>Informasi Akun Anda</span>
                </h5>
                <ul class="list-group list-group-flush mb-4 rounded-3 border">
                    <li class="list-group-item d-flex align-items-center gap-2">
                        <span class="icon-pod icon-pod-xs icon-pod-blue"><i class="bi bi-person"></i></span>
                        <strong>Nama:</strong> <?= $data['data']['user_name']; ?>
                    </li>
                    <li class="list-group-item d-flex align-items-center gap-2">
                        <span class="icon-pod icon-pod-xs icon-pod-emerald"><i class="bi bi-shield-check"></i></span>
                        <strong>Role:</strong> <span class="badge bg-success-subtle text-success rounded-pill px-3">Customer</span>
                    </li>
                </ul>

                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= BASEURL; ?>/CustomerController/orders" class="btn btn-cute rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-bag-check-fill fs-5"></i> <span>Lihat Riwayat Belanja</span>
                    </a>
                    <a href="<?= BASEURL; ?>/ProductController" class="btn btn-outline-primary rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2">
                        <span class="icon-pod icon-pod-xs icon-pod-pink"><i class="bi bi-gem"></i></span>
                        <span>Mulai Belanja</span>
                    </a>
                    <a href="<?= BASEURL; ?>/settings#password-section" class="btn btn-outline-secondary rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2">
                        <span class="icon-pod icon-pod-xs icon-pod-amber"><i class="bi bi-shield-lock-fill"></i></span>
                        <span>Ubah Kata Sandi</span>
                    </a>
                    <a href="<?= BASEURL; ?>/settings" class="btn btn-cute-outline rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2">
                        <span class="icon-pod icon-pod-xs icon-pod-purple"><i class="bi bi-palette-fill"></i></span>
                        <span>Pengaturan Tema</span>
                    </a>
                    <a href="<?= BASEURL; ?>/auth/logout" class="btn btn-outline-danger rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-box-arrow-right"></i> <span>Logout (Keluar Akun)</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
