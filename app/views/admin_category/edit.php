<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Kategori</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <form action="<?= BASEURL; ?>/AdminCategoryController/update" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token']; ?>">
            <input type="hidden" name="id" value="<?= $data['data']['category']['id']; ?>">
            
            <div class="mb-3">
                <label for="name" class="form-label">Nama Kategori</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= $data['data']['category']['name']; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= $data['data']['category']['description']; ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="active" <?= $data['data']['category']['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?= $data['data']['category']['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="<?= BASEURL; ?>/AdminCategoryController" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
