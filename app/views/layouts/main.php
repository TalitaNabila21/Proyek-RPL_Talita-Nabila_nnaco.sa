<!DOCTYPE html>
<html lang="id" data-theme="light" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['data']['judul'] ?? 'nnaco_sa' ?> | nnaco_sa</title>

    <!-- Google Fonts: Outfit & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom Style & Chat CSS -->
    <link rel="stylesheet" href="<?= BASEURL; ?>/css/style.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASEURL; ?>/css/chat.css?v=<?= time(); ?>">

    <!-- Early Theme Initializer to Prevent Flash of Wrong Theme -->
    <script>
        (function() {
            const savedMode = localStorage.getItem('nnaco_theme_mode');
            let theme = 'light';
            if (savedMode === 'system') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            } else if (savedMode) {
                theme = savedMode;
            } else {
                theme = localStorage.getItem('nnaco_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            }
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.setAttribute('data-bs-theme', theme);

            // Font size preference
            const savedFontSize = localStorage.getItem('nnaco_font_size');
            if (savedFontSize === 'medium') {
                document.documentElement.style.fontSize = '17px';
            } else if (savedFontSize === 'large') {
                document.documentElement.style.fontSize = '18.5px';
            }
        })();
    </script>
</head>
<body>

    <!-- Memanggil Component Navbar -->
    <?php require_once '../app/views/components/navbar.php'; ?>

    <!-- Container Utama -->
    <main class="container mt-4 min-vh-100">
        
        <!-- Flash Message -->
        <?php if(isset($_SESSION['flash_msg'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
                <?= $_SESSION['flash_msg']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php 
                unset($_SESSION['flash_msg']); 
                unset($_SESSION['flash_type']);
            ?>
        <?php endif; ?>

        <?php 
            // Memanggil spesifik view
            if(isset($data['view'])) {
                require_once '../app/views/' . $data['view'] . '.php';
            }
        ?>
    </main>

    <!-- Memanggil Component AI Chatbot -->
    <?php require '../app/views/components/chat.php'; ?>


    <!-- Memanggil Component Footer -->
    <?php require_once '../app/views/components/footer.php'; ?>

    <!-- Bootstrap JS Bundle & Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?= BASEURL; ?>/js/script.js?v=<?= time(); ?>"></script>
    <script src="<?= BASEURL; ?>/js/chat.js?v=<?= time(); ?>"></script>
</body>
</html>
