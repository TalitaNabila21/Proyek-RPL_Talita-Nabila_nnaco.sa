<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Produk</h1>
</div>

<div class="row mb-5">
    <div class="col-md-8">
        <form action="<?= BASEURL; ?>/AdminProductController/update" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token']; ?>">
            <input type="hidden" name="id" value="<?= $data['data']['product']['id']; ?>">
            
            <div class="mb-3">
                <label for="name" class="form-label">Nama Produk</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= $data['data']['product']['name']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Kategori</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <?php foreach ($data['data']['categories'] as $cat) : ?>
                        <option value="<?= $cat['id']; ?>" <?= $cat['id'] == $data['data']['product']['category_id'] ? 'selected' : ''; ?>>
                            <?= $cat['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?= $data['data']['product']['description']; ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="price" class="form-label">Harga (Rp)</label>
                        <input type="number" class="form-control" id="price" name="price" min="0" value="<?= (int)$data['data']['product']['price']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stok</label>
                        <input type="number" class="form-control" id="stock" name="stock" min="0" value="<?= $data['data']['product']['stock']; ?>" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label d-block">Gambar Saat Ini</label>
                <?php if($data['data']['product']['image']) : ?>
                    <img src="<?= BASEURL; ?>/assets/images/products/<?= $data['data']['product']['image']; ?>" width="100" class="mb-2 rounded">
                <?php else : ?>
                    <span class="text-muted">Tidak ada gambar</span>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Ganti Gambar Produk (Opsional)</label>
                <input class="form-control" type="file" id="image" name="image" accept="image/*">
            </div>
            
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="active" <?= $data['data']['product']['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?= $data['data']['product']['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="<?= BASEURL; ?>/AdminProductController" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
