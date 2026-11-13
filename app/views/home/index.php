<!-- Hero Section -->
<div class="hero-section p-4 p-md-5 mb-5 mt-4">
    <div class="hero-bubble hero-bubble-1"></div>
    <div class="hero-bubble hero-bubble-2"></div>
    <div class="hero-bubble hero-bubble-3"></div>
    <div class="hero-bubble hero-bubble-4"></div>
    
    <div class="container-fluid py-4 hero-content">
        <div class="row align-items-center g-5">
            <div class="col-md-6 text-center text-md-start order-2 order-md-1">
                <span class="badge bg-white rounded-pill px-3 py-2 mb-3 fw-bold shadow-sm border" style="color: #0288d1; border-color: #4fc3f7 !important;">
                    <i class="bi bi-stars text-warning me-1"></i> New Collection 2026
                </span>
                <h1 class="display-4 fw-bolder mb-3" style="line-height: 1.2;">
                    <span style="color: var(--text-heading-blue);">Koleksi</span> <span style="color: var(--text-heading-pink);">Lucu</span><br>
                    <span style="color: var(--text-heading-blue);">Untuk Gaya</span> <span style="color: var(--text-heading-pink);">Manismu</span> 🎀
                </h1>
                <p class="fs-5 mb-4 text-muted" style="max-width: 90%;">
                    Temukan aksesoris kekinian, handmade, dan super estetik yang siap melengkapi OOTD kamu setiap hari.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-md-start">
                    <a href="<?= BASEURL; ?>/product" class="btn btn-cute fs-6 px-4 py-3">
                        <i class="bi bi-bag-heart-fill me-2 fs-5"></i> Belanja Sekarang
                    </a>
                    <a href="#recommendations" class="btn btn-cute-outline fs-6 px-4 py-3">
                        <i class="bi bi-sparkles me-2 fs-5"></i> Lihat Rekomendasi
                    </a>
                </div>
            </div>
            
            <div class="col-md-6 order-1 order-md-2 text-center position-relative">
                <div class="position-relative d-inline-block">
                    <!-- Circular hero image with floating animation -->
                    <img src="<?= BASEURL; ?>/assets/images/hero_cute.jpg" alt="Aesthetic Collection" class="img-fluid rounded-circle shadow-lg border border-4 border-white" style="width: 350px; height: 350px; object-fit: cover; animation: floatAnimation 5s ease-in-out infinite;">
                    
                    <!-- Floating decorations with enhanced icons -->
                    <div class="position-absolute icon-pod icon-pod-lg icon-pod-circle icon-pod-amber shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; top: 10%; left: -20px; animation: floatAnimation 4s ease-in-out infinite alternate; background: var(--bg-card);">
                        <i class="bi bi-stars fs-3 text-warning"></i>
                    </div>
                    <div class="position-absolute icon-pod icon-pod-xl icon-pod-circle icon-pod-pink shadow-lg d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; bottom: 15%; right: -20px; animation: floatAnimation 6s ease-in-out infinite alternate-reverse; background: var(--bg-card);">
                        <i class="bi bi-balloon-heart-fill fs-2" style="color: #ec4899;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kategori Pilihan -->
<div class="text-center mb-5 pb-3">
    <h2 class="section-title mb-5">Kategori Terpopuler 🌟</h2>
    <div class="row justify-content-center g-4">
        <div class="col-6 col-md-3 col-lg-2">
            <a href="<?= BASEURL; ?>/product?category=1" class="text-decoration-none">
                <div class="category-bubble p-1">
                    <img src="<?= BASEURL; ?>/assets/images/perhiasan_cute_new.jpg" class="w-100 h-100 rounded-circle shadow-sm" style="object-fit: cover;" alt="Perhiasan">
                </div>
                <h6 class="fw-bold" style="color: var(--text-heading-blue);">Perhiasan</h6>
            </a>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <a href="<?= BASEURL; ?>/product?category=5" class="text-decoration-none">
                <div class="category-bubble p-1">
                    <img src="<?= BASEURL; ?>/assets/images/products/penghapus_cute_1791528685329.jpg" class="w-100 h-100 rounded-circle shadow-sm" style="object-fit: cover;" alt="Alat Tulis">
                </div>
                <h6 class="fw-bold" style="color: var(--text-heading-blue);">Alat Tulis</h6>
            </a>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <a href="<?= BASEURL; ?>/product?category=4" class="text-decoration-none">
                <div class="category-bubble p-1">
                    <img src="<?= BASEURL; ?>/assets/images/products/gantungan_kunci_cute_1791528553923.jpg" class="w-100 h-100 rounded-circle shadow-sm" style="object-fit: cover;" alt="Gantungan Kunci">
                </div>
                <h6 class="fw-bold" style="color: var(--text-heading-blue);">Gantungan</h6>
            </a>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <a href="<?= BASEURL; ?>/product?category=6" class="text-decoration-none">
                <div class="category-bubble p-1">
                    <img src="<?= BASEURL; ?>/assets/images/kado_cute.jpg" class="w-100 h-100 rounded-circle shadow-sm" style="object-fit: cover;" alt="Kado & Craft">
                </div>
                <h6 class="fw-bold" style="color: var(--text-heading-blue);">Kado & Craft</h6>
            </a>
        </div>
    </div>
</div>

<!-- Features Section with enhanced icons -->
<div class="row text-center mb-5 g-4">
    <div class="col-md-4">
        <div class="feature-card p-4 h-100 card-hover-lift">
            <div class="feature-icon icon-pod-blue shadow-sm">
                <i class="bi bi-patch-check-fill fs-1 text-primary"></i>
            </div>
            <h5 class="fw-bold" style="color: var(--text-heading-blue);">Kualitas Premium</h5>
            <p class="text-muted small mb-0">Bahan pilihan terbaik yang awet dan nyaman digunakan setiap hari.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="feature-card p-4 h-100 card-hover-lift">
            <div class="feature-icon icon-pod-pink shadow-sm">
                <i class="bi bi-gift-fill fs-1" style="color: #d81b60;"></i>
            </div>
            <h5 class="fw-bold" style="color: var(--text-heading-blue);">Packaging Lucu</h5>
            <p class="text-muted small mb-0">Setiap pesanan dikemas super cantik, cocok langsung untuk hadiah!</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="feature-card p-4 h-100 card-hover-lift">
            <div class="feature-icon icon-pod-purple shadow-sm">
                <i class="bi bi-palette-fill fs-1" style="color: #7c3aed;"></i>
            </div>
            <h5 class="fw-bold" style="color: var(--text-heading-blue);">Desain Estetik</h5>
            <p class="text-muted small mb-0">Gaya kekinian dan tren pastel yang bikin OOTD kamu makin stand out.</p>
        </div>
    </div>
</div>

<!-- Recommendations -->
<div class="row align-items-md-stretch mb-5" id="recommendations">
  <div class="col-md-12">
    <div class="p-4 p-md-5 card-cute border-0 shadow-sm" style="border-radius: 30px !important;">
      <div class="text-center mb-5">
          <h2 class="section-title mb-4">Rekomendasi Spesial 🧸</h2>
      </div>
      <?php require_once '../app/views/components/recommendations.php'; ?>
    </div>
  </div>
</div>

<!-- Testimonials -->
<div class="mb-5">
    <div class="text-center mb-5">
        <h2 class="section-title mb-4">Kata Mereka 💌</h2>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="testimonial-card h-100">
                <div class="position-relative z-1">
                    <div class="text-warning mb-2">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    </div>
                    <p class="mb-4 text-muted fw-medium">"Barangnya super duper lucu! Packagingnya juga aman banget dikasih bubble wrap tebal plus dapet freebies stiker lucu. Bakal langganan terus!"</p>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle me-3" style="width: 45px; height: 45px; background: url('https://ui-avatars.com/api/?name=Siti&background=fce4ec&color=c2185b') center/cover;"></div>
                        <div>
                            <h6 class="mb-0 fw-bold" style="color: #880e4f;">Kak Siti</h6>
                            <small class="text-muted">Customer Setia</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="testimonial-card h-100">
                <div class="position-relative z-1">
                    <div class="text-warning mb-2">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    </div>
                    <p class="mb-4 text-muted fw-medium">"Nyari kado buat sahabat dan ketemu Nnaco_SA. Bener-bener gak nyesel, kualitasnya premium dan harganya affordable banget!"</p>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle me-3" style="width: 45px; height: 45px; background: url('https://ui-avatars.com/api/?name=Rina&background=f8bbd0&color=880e4f') center/cover;"></div>
                        <div>
                            <h6 class="mb-0 fw-bold" style="color: #880e4f;">Kak Rina</h6>
                            <small class="text-muted">Pecinta Aksesoris</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="testimonial-card h-100">
                <div class="position-relative z-1">
                    <div class="text-warning mb-2">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    </div>
                    <p class="mb-4 text-muted fw-medium">"Jepit rambut sama scrunchienya bagus banget! Bikin rambut rapi dan tetep kelihatan aesthetic ala-ala cewek kue. Luv it! 💖"</p>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle me-3" style="width: 45px; height: 45px; background: url('https://ui-avatars.com/api/?name=Amel&background=f48fb1&color=fff') center/cover;"></div>
                        <div>
                            <h6 class="mb-0 fw-bold" style="color: #880e4f;">Kak Amel</h6>
                            <small class="text-muted">Pembeli Baru</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
