<?php
$chatUserRole = $_SESSION['user_role'] ?? 'guest';
$isPrivilegedChat = in_array($chatUserRole, ['admin', 'owner']);
$initialGreeting = $isPrivilegedChat
    ? "Halo! 👋 Saya AI Assistant nnaco_sa, siap membantu Anda seputar produk, pesanan, data akun, omset, dan operasional toko. Ada yang bisa dibantu?"
    : "Hai! 👋 Selamat datang di nnaco_sa. Aku AI Assistant yang siap membantu kamu mencari aksesoris cantik, cek harga, stok, atau riwayat pesananmu. Ada yang bisa dibantu? ✨";
$chatPlaceholder = $isPrivilegedChat
    ? "Tanya seputar produk, pesanan, omset, atau data toko..."
    : "Tanya seputar produk, pesanan, atau rekomendasi...";
?>
<script>
window.APP_BASEURL = "<?= BASEURL; ?>";
</script>

<!-- AI Chat Floating Button -->
<div class="ai-chat-btn" id="aiChatBtn" title="Chat dengan AI Assistant">
    <i class="bi bi-chat-heart-fill fs-3"></i>
</div>

<!-- AI Chat Window -->
<div class="ai-chat-window" id="aiChatWindow">
    <div class="ai-chat-header">
        <span><i class="bi bi-stars me-1 text-warning"></i> AI Assistant nnaco_sa</span>
        <span class="ai-chat-close" id="aiChatClose">&times;</span>
    </div>
    <div class="ai-chat-body" id="aiChatBody">
        <div class="chat-message ai"><?= htmlspecialchars($initialGreeting) ?></div>
        <div class="chat-loading" id="aiChatLoading">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>
    <div class="ai-chat-footer">
        <input type="text" id="aiChatInput" class="ai-chat-input" placeholder="<?= htmlspecialchars($chatPlaceholder) ?>">
        <button id="aiChatSend" class="ai-chat-send" title="Kirim Pesan">
            <i class="bi bi-send-fill fs-6"></i>
        </button>
    </div>
</div>

