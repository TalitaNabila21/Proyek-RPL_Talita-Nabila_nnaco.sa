<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tambah Produk</h1>
</div>

<?php $old = isset($data['data']['old_input']) ? $data['data']['old_input'] : []; ?>

<div class="row mb-5">
    <div class="col-md-8">
        <form action="<?= BASEURL; ?>/AdminProductController/store" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token']; ?>">
            
            <div class="mb-3">
                <label for="name" class="form-label">Nama Produk</label>
                <input type="text" class="form-control" id="name" name="name" minlength="3" value="<?= isset($old['name']) ? htmlspecialchars($old['name']) : ''; ?>" required>
            </div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Kategori</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($data['data']['categories'] as $cat) : ?>
                        <option value="<?= $cat['id']; ?>" <?= (isset($old['category_id']) && $old['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?= $cat['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="4" required><?= isset($old['description']) ? htmlspecialchars($old['description']) : ''; ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="price" class="form-label">Harga (Rp)</label>
                        <input type="number" class="form-control" id="price" name="price" min="1" value="<?= isset($old['price']) ? htmlspecialchars($old['price']) : ''; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stok</label>
                        <input type="number" class="form-control" id="stock" name="stock" min="0" value="<?= isset($old['stock']) ? htmlspecialchars($old['stock']) : ''; ?>" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Gambar Produk</label>
                <input class="form-control mb-2" type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)" required>
                <img id="imagePreview" src="#" alt="Preview" class="img-fluid rounded mb-2 d-none" style="max-height: 200px;">
                <div class="form-text">Maksimal 2MB. Format: JPG, PNG, WEBP. Wajib diupload.</div>
            </div>
            
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="active" <?= (isset($old['status']) && $old['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?= (isset($old['status']) && $old['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Produk</button>
            <a href="<?= BASEURL; ?>/AdminProductController" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '#';
        preview.classList.add('d-none');
    }
}
</script>
