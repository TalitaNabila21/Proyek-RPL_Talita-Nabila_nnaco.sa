<?php
    $stats = $data['data']['stats'] ?? [];
    $totalUsers = $stats['total'] ?? 0;
    $customerCount = $stats['by_role']['customer'] ?? 0;
    $staffCount = ($stats['by_role']['admin'] ?? 0) + ($stats['by_role']['operator'] ?? 0);
    $activeCount = $stats['can_login_total'] ?? 0;
    $inactiveCount = $stats['cannot_login_total'] ?? 0;
    $users = $data['data']['users'] ?? [];
    $csrfToken = $data['data']['csrf_token'] ?? '';
    $currentUserId = $data['data']['current_user_id'] ?? null;
    $currentUserRole = $data['data']['current_user_role'] ?? '';
?>

<!-- Header Section -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-3 border-bottom">
    <div>
        <h1 class="h2 fw-bold text-cute-dark d-flex align-items-center gap-2 mb-1">
            <span class="icon-pod icon-pod-md icon-pod-indigo shadow-xs"><i class="bi bi-people-fill"></i></span>
            <span>Manajemen Pengguna</span>
        </h1>
        <p class="text-muted small mb-0">Kelola akun customer, tim operasional, admin, dan hak akses pengguna sistem.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="<?= BASEURL; ?>/AdminUserController/create" class="btn btn-primary rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-person-plus-fill fs-5"></i>
            <span class="fw-semibold">Tambah Pengguna Baru</span>
        </a>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card bg-primary text-white h-100 shadow-sm rounded-4 overflow-hidden card-hover-lift" style="border: none !important;">
            <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-white-50 text-uppercase fw-semibold mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Pengguna</h6>
                    <h3 class="fw-bold mb-0"><?= number_format($totalUsers, 0, ',', '.'); ?></h3>
                    <small class="text-white-50" style="font-size: 0.75rem;">Semua akun terdaftar</small>
                </div>
                <div class="icon-pod icon-pod-md icon-pod-circle bg-white bg-opacity-25 text-white shadow-xs">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-info text-dark h-100 shadow-sm rounded-4 overflow-hidden card-hover-lift" style="border: none !important;">
            <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-dark-50 text-uppercase fw-semibold mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Customer</h6>
                    <h3 class="fw-bold mb-0"><?= number_format($customerCount, 0, ',', '.'); ?></h3>
                    <small class="text-dark-50" style="font-size: 0.75rem;">Pelanggan setia</small>
                </div>
                <div class="icon-pod icon-pod-md icon-pod-circle bg-dark bg-opacity-10 text-dark shadow-xs">
                    <i class="bi bi-person-heart fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-warning text-dark h-100 shadow-sm rounded-4 overflow-hidden card-hover-lift" style="border: none !important;">
            <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-dark-50 text-uppercase fw-semibold mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Admin & Operator</h6>
                    <h3 class="fw-bold mb-0"><?= number_format($staffCount, 0, ',', '.'); ?></h3>
                    <small class="text-dark-50" style="font-size: 0.75rem;">Staff pengelola</small>
                </div>
                <div class="icon-pod icon-pod-md icon-pod-circle bg-dark bg-opacity-10 text-dark shadow-xs">
                    <i class="bi bi-shield-lock-fill fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-success text-white h-100 shadow-sm rounded-4 overflow-hidden card-hover-lift" style="border: none !important;">
            <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-white-50 text-uppercase fw-semibold mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Status Akun</h6>
                    <h3 class="fw-bold mb-0"><?= number_format($activeCount, 0, ',', '.'); ?> <span style="font-size: 0.9rem; font-weight: normal;">Aktif</span></h3>
                    <small class="text-white-50" style="font-size: 0.75rem;"><?= $inactiveCount; ?> akun nonaktif</small>
                </div>
                <div class="icon-pod icon-pod-md icon-pod-circle bg-white bg-opacity-25 text-white shadow-xs">
                    <i class="bi bi-person-check-fill fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card shadow-sm rounded-4 mb-4 border">
    <div class="card-body p-3">
        <form method="GET" action="<?= BASEURL; ?>/AdminUserController" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0" name="search" value="<?= htmlspecialchars($data['data']['search'] ?? ''); ?>" placeholder="Cari nama, email, nomor WA...">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Role Pengguna</option>
                    <option value="customer" <?= ($data['data']['role'] ?? '') === 'customer' ? 'selected' : ''; ?>>Customer (Pelanggan)</option>
                    <option value="operator" <?= ($data['data']['role'] ?? '') === 'operator' ? 'selected' : ''; ?>>Operator (Pesanan)</option>
                    <option value="admin" <?= ($data['data']['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin (Pengelola)</option>
                    <option value="owner" <?= ($data['data']['role'] ?? '') === 'owner' ? 'selected' : ''; ?>>Owner (Pemilik)</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="active" <?= ($data['data']['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Aktif (Bisa Login)</option>
                    <option value="inactive" <?= ($data['data']['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Nonaktif (Diblokir)</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-1 justify-content-end">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <?php if(!empty($data['data']['search']) || !empty($data['data']['role']) || !empty($data['data']['status'])): ?>
                    <a href="<?= BASEURL; ?>/AdminUserController" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card shadow-sm rounded-4 border overflow-hidden">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
            <span class="icon-pod icon-pod-xs icon-pod-indigo"><i class="bi bi-table"></i></span>
            <span>Daftar Pengguna</span>
        </h5>
        <span class="badge rounded-pill bg-light text-dark border px-3 py-1">
            Ditemukan <?= count($users); ?> Pengguna
        </span>
    </div>

    <div class="card-body p-0">
        <?php if(empty($users)): ?>
            <div class="text-center py-5">
                <div class="icon-pod icon-pod-xl icon-pod-circle icon-pod-blue mb-3 shadow-xs">
                    <i class="bi bi-people fs-1"></i>
                </div>
                <h5 class="fw-bold text-cute-dark">Tidak ada data pengguna</h5>
                <p class="text-muted small mb-3">Tidak ditemukan pengguna yang sesuai dengan kriteria pencarian Anda.</p>
                <a href="<?= BASEURL; ?>/AdminUserController" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Pencarian
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fw-semibold" style="font-size: 0.78rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4" style="width: 60px;">#</th>
                            <th>Pengguna</th>
                            <th>No. WhatsApp / HP</th>
                            <th>Role Akun</th>
                            <th>Status</th>
                            <th>Terdaftar</th>
                            <th class="text-end pe-4" style="min-width: 170px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $index => $u): ?>
                            <?php 
                                $isSelf = ($u['id'] == $currentUserId);
                                $isOwner = ($u['role'] === 'owner');
                                $initials = strtoupper(substr($u['name'], 0, 2));

                                // Badge role color
                                $roleBadge = 'bg-secondary';
                                $roleIcon = 'bi-person';
                                if ($u['role'] === 'owner') {
                                    $roleBadge = 'bg-purple-subtle text-purple border border-purple';
                                    $roleIcon = 'bi-crown-fill';
                                } elseif ($u['role'] === 'admin') {
                                    $roleBadge = 'bg-danger-subtle text-danger border border-danger-subtle';
                                    $roleIcon = 'bi-shield-shaded';
                                } elseif ($u['role'] === 'operator') {
                                    $roleBadge = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                    $roleIcon = 'bi-inbox-fill';
                                } elseif ($u['role'] === 'customer') {
                                    $roleBadge = 'bg-info-subtle text-info border border-info-subtle';
                                    $roleIcon = 'bi-person-heart';
                                }
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="icon-pod icon-pod-sm icon-pod-circle <?= $isOwner ? 'icon-pod-purple' : ($u['role'] === 'admin' ? 'icon-pod-pink' : 'icon-pod-blue'); ?> shadow-xs fw-bold" style="font-size: 0.75rem;">
                                        <?= $initials; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-cute-dark d-flex align-items-center gap-1">
                                        <span><?= htmlspecialchars($u['name']); ?></span>
                                        <?php if($isSelf): ?>
                                            <span class="badge bg-primary text-white rounded-pill px-2 py-0" style="font-size: 0.65rem;">Anda</span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted d-block" style="font-size: 0.8rem;"><?= htmlspecialchars($u['email']); ?></small>
                                </td>
                                <td>
                                    <?php if(!empty($u['phone'])): ?>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $u['phone']); ?>" target="_blank" class="badge rounded-pill bg-success-subtle text-success text-decoration-none border border-success-subtle px-2 py-1 d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-whatsapp"></i> <?= htmlspecialchars($u['phone']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-3 py-1 <?= $roleBadge; ?> d-inline-flex align-items-center gap-1 fw-semibold">
                                        <i class="bi <?= $roleIcon; ?>"></i> <?= ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($u['status'] === 'active'): ?>
                                        <span class="badge rounded-pill bg-success px-3 py-1 d-inline-flex align-items-center gap-1 shadow-xs">
                                            <i class="bi bi-check-circle-fill"></i> Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-secondary px-3 py-1 d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-x-circle"></i> Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?= date('d M Y', strtotime($u['created_at'])); ?></small>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <!-- Edit Button -->
                                        <a href="<?= BASEURL; ?>/AdminUserController/edit/<?= $u['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" title="Edit Pengguna">
                                            <i class="bi bi-pencil-square"></i> <span class="d-none d-md-inline">Edit</span>
                                        </a>

                                        <!-- Toggle Status Button -->
                                        <?php if(!$isSelf && !$isOwner): ?>
                                            <form method="POST" action="<?= BASEURL; ?>/AdminUserController/toggleStatus/<?= $u['id']; ?>" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin <?= $u['status'] === 'active' ? 'menonaktifkan' : 'mengaktifkan'; ?> akun <?= htmlspecialchars($u['name']); ?>?');">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken; ?>">
                                                <button type="submit" class="btn btn-sm <?= $u['status'] === 'active' ? 'btn-outline-warning text-dark' : 'btn-outline-success'; ?> rounded-pill px-2 py-1" title="<?= $u['status'] === 'active' ? 'Nonaktifkan Akun' : 'Aktifkan Akun'; ?>">
                                                    <i class="bi <?= $u['status'] === 'active' ? 'bi-toggle-on' : 'bi-toggle-off'; ?>"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Delete Button -->
                                        <?php if(!$isSelf && !$isOwner): ?>
                                            <form method="POST" action="<?= BASEURL; ?>/AdminUserController/delete/<?= $u['id']; ?>" class="d-inline" onsubmit="return confirm('Hapus akun <?= htmlspecialchars($u['name']); ?>? Tindakan ini tidak dapat dibatalkan.');">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" title="Hapus Pengguna">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
