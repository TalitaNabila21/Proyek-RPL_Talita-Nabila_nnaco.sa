<?php
    $user = $data['data']['user'] ?? [];
    $csrfToken = $data['data']['csrf_token'] ?? '';
    $currentUserId = $data['data']['current_user_id'] ?? null;
    $currentUserRole = $data['data']['current_user_role'] ?? '';
    $isSelf = ($user['id'] == $currentUserId);
    $isOwner = ($user['role'] === 'owner');
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm rounded-4 border">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <span class="icon-pod icon-pod-xs icon-pod-indigo"><i class="bi bi-pencil-square"></i></span>
                    <span>Edit Data Pengguna</span>
                </h5>
                <a href="<?= BASEURL; ?>/AdminUserController" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="<?= BASEURL; ?>/AdminUserController/update/<?= $user['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken; ?>">

                    <div class="row g-3">
                        <!-- Nama Lengkap -->
                        <div class="col-12 col-md-6">
                            <label for="name" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <!-- Nomor WhatsApp -->
                        <div class="col-12 col-md-6">
                            <label for="phone" class="form-label fw-semibold">Nomor WhatsApp / HP</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-success"><i class="bi bi-whatsapp"></i></span>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Contoh: 081234567890">
                            </div>
                            <small class="text-muted" style="font-size: 0.78rem;">Digunakan untuk login via WhatsApp & kirim notifikasi.</small>
                        </div>

                        <!-- Role -->
                        <div class="col-12 col-md-3">
                            <label for="role" class="form-label fw-semibold">Role Akun <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" <?= $isSelf ? 'disabled' : ''; ?> required>
                                <option value="customer" <?= ($user['role'] ?? '') === 'customer' ? 'selected' : ''; ?>>Customer (Pelanggan)</option>
                                <option value="operator" <?= ($user['role'] ?? '') === 'operator' ? 'selected' : ''; ?>>Operator (Pesanan)</option>
                                <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin (Pengelola)</option>
                                <?php if($currentUserRole === 'owner' || $isOwner): ?>
                                    <option value="owner" <?= ($user['role'] ?? '') === 'owner' ? 'selected' : ''; ?>>Owner (Pemilik)</option>
                                <?php endif; ?>
                            </select>
                            <?php if($isSelf): ?>
                                <input type="hidden" name="role" value="<?= htmlspecialchars($user['role']); ?>">
                                <small class="text-muted" style="font-size: 0.72rem;">Tidak dapat mengubah role akun sendiri.</small>
                            <?php endif; ?>
                        </div>

                        <!-- Status -->
                        <div class="col-12 col-md-3">
                            <label for="status" class="form-label fw-semibold">Status Akun <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" <?= ($isSelf || $isOwner) ? 'disabled' : ''; ?> required>
                                <option value="active" <?= ($user['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Aktif (Bisa Login)</option>
                                <option value="inactive" <?= ($user['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Nonaktif (Diblokir)</option>
                            </select>
                            <?php if($isSelf || $isOwner): ?>
                                <input type="hidden" name="status" value="<?= htmlspecialchars($user['status']); ?>">
                                <small class="text-muted" style="font-size: 0.72rem;"><?= $isSelf ? 'Tidak dapat menonaktifkan akun sendiri.' : 'Akun Owner selalu aktif.'; ?></small>
                            <?php endif; ?>
                        </div>

                        <!-- Separator for Password Change -->
                        <div class="col-12 mt-4">
                            <div class="p-3 rounded-4 bg-light border">
                                <h6 class="fw-bold text-cute-dark mb-1 d-flex align-items-center gap-2">
                                    <span class="icon-pod icon-pod-xs icon-pod-amber"><i class="bi bi-shield-lock"></i></span>
                                    <span>Ganti Kata Sandi (Opsional)</span>
                                </h6>
                                <p class="text-muted small mb-3">Biarkan kedua kolom di bawah kosong jika tidak ingin mengubah kata sandi pengguna ini.</p>

                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label for="password" class="form-label fw-semibold small">Kata Sandi Baru</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted"><i class="bi bi-key"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Kosongkan jika tetap" minlength="6">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', this)" title="Lihat/Sembunyikan">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="password_confirm" class="form-label fw-semibold small">Konfirmasi Kata Sandi Baru</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted"><i class="bi bi-shield-check"></i></span>
                                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Ulangi kata sandi baru" minlength="6">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password_confirm', this)" title="Lihat/Sembunyikan">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASEURL; ?>/AdminUserController" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Perbarui Data & Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(fieldId, btn) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    const isPassword = field.type === 'password';
    field.type = isPassword ? 'text' : 'password';
    const icon = btn.querySelector('i');
    if (icon) {
        icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
    }
}
</script>
