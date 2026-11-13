<?php $prod = $data['data']['product']; ?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= BASEURL; ?>" class="text-decoration-none text-muted"><i class="bi bi-house-door me-1 text-primary"></i> Home</a></li>
    <li class="breadcrumb-item"><a href="<?= BASEURL; ?>/product" class="text-decoration-none text-muted"><i class="bi bi-grid me-1 text-warning"></i> Katalog</a></li>
    <li class="breadcrumb-item active fw-semibold" aria-current="page"><?= $prod['name']; ?></li>
  </ol>
</nav>

<div class="card card-cute overflow-hidden mb-5">
    <div class="row g-0">
        <!-- Bagian Gambar -->
        <div class="col-md-5 bg-light d-flex align-items-center justify-content-center position-relative">
            <?php if($prod['image']) : ?>
                <img src="<?= BASEURL; ?>/assets/images/products/<?= $prod['image']; ?>" class="img-fluid w-100 object-fit-cover" alt="<?= $prod['name']; ?>" style="min-height: 400px; max-height: 500px;">
            <?php else : ?>
                <div class="text-secondary d-flex flex-column align-items-center justify-content-center py-5" style="min-height: 400px;">
                    <i class="bi bi-image fs-1 mb-2 opacity-50"></i>
                    <span>No Image Available</span>
                </div>
            <?php endif; ?>
            
            <?php if($prod['stock'] == 0): ?>
                <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center">
                    <span class="badge bg-danger rounded-pill px-4 py-3 fs-5 shadow">Habis Terjual</span>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Bagian Detail Info -->
        <div class="col-md-7">
            <div class="card-body p-4 p-lg-5 h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2 fs-6"><?= $prod['category_name']; ?></span>
                </div>
                
                <h2 class="card-title fw-bold mb-3 text-cute-dark"><?= $prod['name']; ?> 🎀</h2>
                <h3 class="text-danger fw-bold mb-4"><?= format_rupiah($prod['price']); ?></h3>
                
                <h5 class="fw-bold mb-3 text-cute-dark">Deskripsi Produk</h5>
                <div class="card-text mb-4 text-muted lh-lg" style="white-space: pre-line;">
                    <?= $prod['description'] ? $prod['description'] : 'Tidak ada deskripsi.'; ?>
                </div>

                <div class="mt-auto p-4 bg-cute-light rounded-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-semibold">Status Ketersediaan:</span> 
                        <?php if($prod['stock'] > 0): ?>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">
                                <i class="bi bi-check-circle-fill me-1"></i> Tersedia (<?= $prod['stock']; ?> stok)
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2">
                                <i class="bi bi-x-circle-fill me-1"></i> Stok Habis
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($prod['stock'] > 0) : ?>
                        <form action="<?= BASEURL; ?>/CartController/add" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token']; ?>">
                            <input type="hidden" name="product_id" value="<?= $prod['id']; ?>">
                            
                            <div class="row g-2 align-items-center">
                                <div class="col-5 col-sm-4">
                                    <div class="input-group">
                                        <button type="button" class="btn btn-outline-secondary px-2" onclick="let q=document.getElementById('productQty'); if(q.value>1) q.value--;"><i class="bi bi-dash"></i></button>
                                        <input type="number" id="productQty" name="quantity" class="form-control text-center fw-bold px-1 qty-input" value="1" min="1" max="<?= $prod['stock']; ?>" required>
                                        <button type="button" class="btn btn-outline-secondary px-2" onclick="let q=document.getElementById('productQty'); if(parseInt(q.value) < <?= $prod['stock']; ?>) q.value++;"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>
                                <div class="col-8 col-sm-9">
                                    <button type="submit" class="btn btn-cute btn-lg w-100 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-bag-plus-fill fs-5"></i> <span>Tambah ke Keranjang</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php else : ?>
                        <button class="btn btn-secondary btn-lg w-100 rounded-pill fw-semibold shadow-sm" disabled>
                            Habis Terjual
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rekomendasi Produk -->
<div class="mt-5 pt-4 border-top">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0 text-cute-dark">Mungkin Anda Sukai 🧸</h4>
        <a href="<?= BASEURL; ?>/product" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Lihat Semua</a>
    </div>
    
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4">
        <?php foreach ($data['data']['recommendations'] as $rec) : ?>
            <div class="col">
                <a href="<?= BASEURL; ?>/product/detail/<?= $rec['slug']; ?>" class="text-decoration-none text-dark">
                    <div class="card h-100 card-cute overflow-hidden product-card transition-hover">
                        <?php if($rec['image']) : ?>
                            <img src="<?= BASEURL; ?>/assets/images/products/<?= $rec['image']; ?>" class="card-img-top object-fit-cover transition-scale" alt="<?= $rec['name']; ?>" style="height: 180px;">
                        <?php else : ?>
                            <div class="bg-light text-secondary d-flex align-items-center justify-content-center" style="height: 180px;">
                                <i class="bi bi-image fs-1 opacity-50"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body p-3">
                            <h6 class="card-title fw-bold text-truncate mb-1" title="<?= $rec['name']; ?>"><?= $rec['name']; ?></h6>
                            <p class="card-text text-danger fw-semibold mb-0"><?= format_rupiah($rec['price']); ?></p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .transition-hover {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .transition-scale {
        transition: transform 0.3s ease-in-out;
    }
    .product-card:hover .transition-scale {
        transform: scale(1.05);
    }
</style>
