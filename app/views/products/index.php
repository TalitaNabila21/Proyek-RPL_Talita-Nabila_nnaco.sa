<?php 
$filters = $data['data']['filters'] ?? []; 
$pagination = $data['data']['pagination'] ?? [];
?>

<div class="row">
    <!-- Sidebar Filters -->
    <div class="col-lg-3 mb-4">
        <div class="card card-cute sticky-lg-top" style="top: 20px; z-index: 1;">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3 text-cute-dark d-flex align-items-center gap-2">
                    <span class="icon-pod icon-pod-xs icon-pod-blue"><i class="bi bi-funnel-fill"></i></span>
                    <span>Filter Katalog</span>
                </h5>
                
                <form action="<?= BASEURL; ?>/product" method="GET">
                    
                    <!-- Pencarian -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Pencarian</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-primary"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Cari aksesoris..." value="<?= $filters['search'] ?? ''; ?>">
                        </div>
                    </div>

                    <!-- Kategori -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Kategori</label>
                        <select name="category" class="form-select bg-light">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($data['data']['categories'] as $cat) : ?>
                                <option value="<?= $cat['slug']; ?>" <?= ($filters['category'] ?? '') == $cat['slug'] ? 'selected' : ''; ?>>
                                    <?= $cat['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Rentang Harga -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Harga</label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <input type="number" name="min_price" class="form-control bg-light" placeholder="Min" value="<?= $filters['min_price'] ?? ''; ?>" min="0">
                            <span class="text-muted">-</span>
                            <input type="number" name="max_price" class="form-control bg-light" placeholder="Max" value="<?= $filters['max_price'] ?? ''; ?>" min="0">
                        </div>
                    </div>

                    <!-- Pengurutan -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Urutkan Berdasarkan</label>
                        <select name="sort" class="form-select bg-light">
                            <option value="newest" <?= ($filters['sort'] ?? '') == 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                            <option value="price_low" <?= ($filters['sort'] ?? '') == 'price_low' ? 'selected' : ''; ?>>Harga Terendah</option>
                            <option value="price_high" <?= ($filters['sort'] ?? '') == 'price_high' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                            <option value="name_asc" <?= ($filters['sort'] ?? '') == 'name_asc' ? 'selected' : ''; ?>>Nama A - Z</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-cute-blue w-100 d-inline-flex align-items-center justify-content-center gap-2 shadow-xs">
                        <i class="bi bi-funnel"></i> <span>Terapkan Filter</span>
                    </button>
                    <?php if(!empty(array_filter($filters))): ?>
                        <a href="<?= BASEURL; ?>/product" class="btn btn-cute-outline w-100 mt-2 d-inline-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-arrow-counterclockwise"></i> <span>Reset Filter</span>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0 text-cute-dark">Katalog Produk 🎀</h2>
            <span class="text-muted"><?= $pagination['total_items'] ?? 0; ?> Produk Ditemukan</span>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4">
            <?php if(empty($data['data']['products'])) : ?>
                <div class="col-12 w-100 text-center py-5">
                    <div class="p-5 bg-light rounded-4">
                        <i class="bi bi-box-seam fs-1 text-muted mb-3 d-block"></i>
                        <h4 class="text-dark fw-bold">Produk tidak ditemukan.</h4>
                        <p class="text-muted mb-0">Silakan coba pencarian, kategori, atau rentang harga lainnya.</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($data['data']['products'] as $prod) : ?>
                <div class="col">
                    <div class="card h-100 card-cute overflow-hidden product-card transition-hover">
                        
                        <a href="<?= BASEURL; ?>/product/detail/<?= $prod['slug']; ?>" class="text-decoration-none">
                            <div class="position-relative overflow-hidden" style="height: 220px;">
                                <?php 
                                $imgSrc = !empty($prod['image']) && file_exists('assets/images/products/' . $prod['image']) 
                                    ? BASEURL . '/assets/images/products/' . $prod['image'] 
                                    : BASEURL . '/assets/images/products/placeholder-product.jpg';
                                ?>
                                <img src="<?= $imgSrc; ?>" class="card-img-top object-fit-cover h-100 w-100 transition-scale" alt="<?= $prod['name']; ?>">
                                
                                <?php if($prod['stock'] == 0): ?>
                                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center">
                                        <span class="badge bg-danger rounded-pill px-3 py-2 fs-6">Habis Terjual</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                        
                        <div class="card-body d-flex flex-column p-4">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary mb-2 align-self-start rounded-pill px-3"><?= $prod['category_name']; ?></span>
                            
                            <a href="<?= BASEURL; ?>/product/detail/<?= $prod['slug']; ?>" class="text-decoration-none text-dark">
                                <h5 class="card-title fw-bold text-cute-dark text-truncate mb-1" title="<?= $prod['name']; ?>"><?= $prod['name']; ?></h5>
                            </a>
                            
                            <p class="card-text text-danger fw-bold fs-5 mb-3"><?= format_rupiah($prod['price']); ?></p>
                            
                            <div class="mt-auto d-flex justify-content-between align-items-center mb-3">
                                <?php if($prod['stock'] > 0): ?>
                                    <small class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Stok tersedia</small>
                                <?php else: ?>
                                    <small class="text-danger fw-semibold"><i class="bi bi-x-circle-fill me-1"></i> Stok habis</small>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="<?= BASEURL; ?>/product/detail/<?= $prod['slug']; ?>" class="btn btn-cute-outline w-50 flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1">
                                    <i class="bi bi-eye"></i> <span>Detail</span>
                                </a>
                                
                                <form action="<?= BASEURL; ?>/CartController/add" method="POST" class="w-50 flex-grow-1">
                                    <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token'] ?? ''; ?>">
                                    <input type="hidden" name="product_id" value="<?= $prod['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-cute w-100 d-inline-flex align-items-center justify-content-center gap-1 shadow-xs" <?= $prod['stock'] == 0 ? 'disabled' : ''; ?> title="Tambah ke keranjang">
                                        <i class="bi bi-bag-plus-fill fs-5"></i>
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if(!empty($data['data']['products']) && isset($pagination['total_pages']) && $pagination['total_pages'] > 1): ?>
            <nav class="mt-5 mb-4 d-flex justify-content-center">
                <ul class="pagination pagination-sm shadow-sm rounded-pill overflow-hidden">
                    
                    <?php 
                        // Build query string for pagination links
                        $qsData = $filters;
                        unset($qsData['limit'], $qsData['offset']);
                        $buildQuery = function($pageNum) use ($qsData) {
                            $qsData['page'] = $pageNum;
                            return http_build_query(array_filter($qsData));
                        };
                    ?>
                    
                    <!-- Prev -->
                    <li class="page-item <?= $pagination['current_page'] <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link px-3 py-2 border-0 text-dark" href="?<?= $buildQuery($pagination['current_page'] - 1); ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    
                    <!-- Page Numbers -->
                    <?php for($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <li class="page-item <?= $pagination['current_page'] == $i ? 'active' : ''; ?>">
                            <a class="page-link px-3 py-2 border-0 <?= $pagination['current_page'] == $i ? 'bg-dark text-white' : 'text-dark'; ?>" href="?<?= $buildQuery($i); ?>">
                                <?= $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Next -->
                    <li class="page-item <?= $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : ''; ?>">
                        <a class="page-link px-3 py-2 border-0 text-dark" href="?<?= $buildQuery($pagination['current_page'] + 1); ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

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
