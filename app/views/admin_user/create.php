<?php
    $csrfToken = $data['data']['csrf_token'] ?? '';
    $currentUserRole = $data['data']['current_user_role'] ?? '';
    $oldInput = $data['data']['old_input'] ?? [];
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm rounded-4 border">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <span class="icon-pod icon-pod-xs icon-pod-indigo"><i class="bi bi-person-plus-fill"></i></span>
                    <span>Tambah Pengguna Baru</span>
                </h5>
                <a href="<?= BASEURL; ?>/AdminUserController" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="<?= BASEURL; ?>/AdminUserController/store">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken; ?>">

                    <div class="row g-3">
                        <!-- Nama Lengkap -->
                        <div class="col-12 col-md-6">
                            <label for="name" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($oldInput['name'] ?? ''); ?>" placeholder="Contoh: Budi Santoso" required>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($oldInput['email'] ?? ''); ?>" placeholder="nama@domain.com" required>
                            </div>
                        </div>

                        <!-- Nomor WhatsApp -->
                        <div class="col-12 col-md-6">
                            <label for="phone" class="form-label fw-semibold">Nomor WhatsApp / HP</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-success"><i class="bi bi-whatsapp"></i></span>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($oldInput['phone'] ?? ''); ?>" placeholder="Contoh: 081234567890">
                            </div>
                            <small class="text-muted" style="font-size: 0.78rem;">Digunakan untuk login via WhatsApp & kirim notifikasi.</small>
                        </div>

                        <!-- Role -->
                        <div class="col-12 col-md-3">
                            <label for="role" class="form-label fw-semibold">Role Akun <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="customer" <?= ($oldInput['role'] ?? 'customer') === 'customer' ? 'selected' : ''; ?>>Customer (Pelanggan)</option>
                                <option value="operator" <?= ($oldInput['role'] ?? '') === 'operator' ? 'selected' : ''; ?>>Operator (Pesanan)</option>
                                <option value="admin" <?= ($oldInput['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin (Pengelola)</option>
                                <?php if($currentUserRole === 'owner'): ?>
                                    <option value="owner" <?= ($oldInput['role'] ?? '') === 'owner' ? 'selected' : ''; ?>>Owner (Pemilik)</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-12 col-md-3">
                            <label for="status" class="form-label fw-semibold">Status Akun <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?= ($oldInput['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Aktif (Bisa Login)</option>
                                <option value="inactive" <?= ($oldInput['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Nonaktif (Diblokir)</option>
                            </select>
                        </div>

                        <!-- Password -->
                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label fw-semibold">Kata Sandi <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter" required minlength="6">
                            </div>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="col-12 col-md-6">
                            <label for="password_confirm" class="form-label fw-semibold">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-check"></i></span>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Ulangi kata sandi" required minlength="6">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASEURL; ?>/AdminUserController" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
