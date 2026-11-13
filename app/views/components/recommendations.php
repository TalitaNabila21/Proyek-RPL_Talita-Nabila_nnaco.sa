<?php if (isset($data['recommendations']) && !empty($data['recommendations'])): ?>
    <div class="row row-cols-1 row-cols-md-4 g-4 mt-2">
        <?php foreach ($data['recommendations'] as $rec): ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0 product-card card-hover-lift" style="background: var(--bg-card); border-radius: 20px; overflow: hidden; border: 1px solid var(--border-color) !important;">
                    <a href="<?= BASEURL; ?>/ProductController/detail/<?= $rec['slug']; ?>" class="text-decoration-none" style="color: var(--text-primary);">
                        <?php if ($rec['image']): ?>
                            <img src="<?= BASEURL; ?>/assets/images/products/<?= $rec['image']; ?>" class="card-img-top object-fit-cover" alt="<?= $rec['name']; ?>" style="height: 200px;">
                        <?php else: ?>
                            <div class="text-muted d-flex align-items-center justify-content-center" style="height: 200px; background: var(--bg-card-subtle);">
                                <div class="icon-pod icon-pod-lg icon-pod-circle icon-pod-blue opacity-50 shadow-xs">
                                    <i class="bi bi-image fs-2"></i>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column p-3">
                            <span class="badge rounded-pill align-self-start mb-2 px-3 py-1" style="background: var(--bg-card-subtle); color: var(--text-heading-blue); border: 1px solid var(--border-color);">
                                <i class="bi bi-tag-fill me-1 text-primary"></i><?= $rec['category_name']; ?>
                            </span>
                            <h6 class="card-title fw-bold text-truncate mb-2" style="color: var(--text-primary);"><?= $rec['name']; ?></h6>
                            <p class="card-text fw-bold mt-auto mb-0" style="color: var(--text-heading-pink);">
                                Rp <?= number_format($rec['price'], 0, ',', '.'); ?>
                            </p>
                        </div>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <style>
        .product-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
    </style>
<?php endif; ?>
