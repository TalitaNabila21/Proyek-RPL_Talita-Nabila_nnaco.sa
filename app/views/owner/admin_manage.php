<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manajemen Admin</h1>
</div>

<div class="row">
    <!-- Form Tambah Admin -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold">Tambah Admin Baru</div>
            <div class="card-body">
                <form action="<?= BASEURL; ?>/OwnerAdminController/create" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Buat Akun Admin</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Daftar Admin -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">Daftar Akun Admin</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Tgl Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['data']['admins'] as $admin) : ?>
                        <tr>
                            <td><?= $admin['name']; ?></td>
                            <td><?= $admin['email']; ?></td>
                            <td>
                                <?php if($admin['status'] == 'active') : ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else : ?>
                                    <span class="badge bg-danger">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y', strtotime($admin['created_at'])); ?></td>
                            <td>
                                <div class="d-inline-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" data-bs-toggle="modal" data-bs-target="#editPasswordModal<?= $admin['id'] ?>" title="Ubah Password Admin">
                                        <i class="bi bi-key-fill"></i> <span class="d-none d-md-inline">Password</span>
                                    </button>
                                    <form action="<?= BASEURL; ?>/OwnerAdminController/toggle/<?= $admin['id'] ?>" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token'] ?>">
                                        <?php if($admin['status'] == 'active') : ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" onclick="return confirm('Nonaktifkan admin ini?')" title="Nonaktifkan Akun">Nonaktifkan</button>
                                        <?php else : ?>
                                            <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" onclick="return confirm('Aktifkan admin ini?')" title="Aktifkan Akun">Aktifkan</button>
                                        <?php endif; ?>
                                    </form>
                                </div>

                                <!-- Modal Ubah Password Admin -->
                                <div class="modal fade" id="editPasswordModal<?= $admin['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $admin['id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content text-start">
                                            <div class="modal-header">
                                                <h5 class="modal-title d-flex align-items-center gap-2" id="modalLabel<?= $admin['id'] ?>">
                                                    <span class="icon-pod icon-pod-xs icon-pod-indigo"><i class="bi bi-key-fill"></i></span>
                                                    <span>Ubah Password Admin</span>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="<?= BASEURL; ?>/OwnerAdminController/updatePassword/<?= $admin['id'] ?>" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token'] ?>">
                                                <div class="modal-body py-3">
                                                    <p class="mb-3 small text-muted">
                                                        Ubah kata sandi untuk admin <strong><?= htmlspecialchars($admin['name']) ?></strong> (<?= htmlspecialchars($admin['email']) ?>).
                                                    </p>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Kata Sandi Baru <span class="text-danger">*</span></label>
                                                        <input type="password" class="form-control" name="password" placeholder="Minimal 6 karakter" required minlength="6">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Ulangi Kata Sandi Baru <span class="text-danger">*</span></label>
                                                        <input type="password" class="form-control" name="password_confirm" placeholder="Ulangi kata sandi baru" required minlength="6">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
                                                        <i class="bi bi-check2-circle me-1"></i> Simpan Password
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($data['data']['admins'])) : ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada akun admin terdaftar.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
