<?php
if (isset($data['data']['data']) && is_array($data['data']['data'])) {
    $data = $data['data']['data'];
} elseif (isset($data['data']) && is_array($data['data'])) {
    $data = $data['data'];
}
$csrfToken = $data['csrf_token'] ?? '';
$aiEnabled = ($data['ai_enabled'] ?? '1') === '1';
$currentProvider = $data['ai_provider'] ?? 'groq';
$currentModel = $data['ai_model'] ?? 'qwen/qwen3.8-27b';
$maskedKey = $data['ai_api_key_masked'] ?? '';
$rawKey = $data['ai_api_key'] ?? '';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
    <div>
        <h6 class="card-category text-muted mb-1">INTELLIGENCE & LLM</h6>
        <h1 class="h2 fw-bold text-cute-dark mb-0 d-flex align-items-center gap-2">
            <span class="icon-pod icon-pod-md icon-pod-indigo shadow-xs"><i class="bi bi-robot"></i></span>
            <span>Konfigurasi AI Model</span>
        </h1>
        <p class="text-muted small mb-0 mt-1">Kelola penyedia LLM (Groq, Gemini, Nvidia), model AI, API Key, dan parameter generasi asisten belanja toko.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="<?= BASEURL; ?>/adminSettings" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> <span>Kembali ke Pengaturan</span>
        </a>
    </div>
</div>

<!-- Alert Box Global -->
<?php if(isset($_SESSION['flash_msg'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info'; ?> alert-dismissible fade show shadow-sm rounded-4 mb-4" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill fs-5"></i>
            <div><?= $_SESSION['flash_msg']; ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<div class="row g-4 mb-5">
    
    <!-- Kolom Formulir Pengaturan AI -->
    <div class="col-lg-7">
        <form action="<?= BASEURL; ?>/adminSettings/saveAi" method="POST" id="formAiSettings">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>">

            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--bg-card);">
                <div class="card-header border-0 bg-transparent py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-cute-dark">
                        <i class="bi bi-sliders text-primary me-2"></i>Parameter Model AI
                    </h5>
                    <div class="form-check form-switch fs-5 mb-0" title="Aktifkan atau nonaktifkan AI Chatbot di seluruh web">
                        <input class="form-check-input" type="checkbox" role="switch" id="ai_enabled" name="ai_enabled" value="1" <?= $aiEnabled ? 'checked' : ''; ?>>
                        <label class="form-check-label fs-6 fw-semibold text-cute-dark" for="ai_enabled">
                            <?= $aiEnabled ? '<span class="text-success">Aktif</span>' : '<span class="text-muted">Nonaktif</span>'; ?>
                        </label>
                    </div>
                </div>

                <div class="card-body px-4 pb-4 pt-2">
                    
                    <!-- Pilihan Provider AI -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-cute-dark">Penyedia Layanan (AI Provider):</label>
                        <div class="row g-2">
                            <!-- Groq -->
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="ai_provider" id="provider_groq" value="groq" autocomplete="off" <?= $currentProvider === 'groq' ? 'checked' : ''; ?> onchange="onProviderChanged('groq')">
                                <label class="btn btn-outline-primary w-100 p-3 rounded-4 text-start d-flex flex-column justify-content-between h-100" for="provider_groq">
                                    <div>
                                        <div class="fw-bold fs-6 mb-1"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Groq</div>
                                        <div class="small text-muted" style="font-size: 0.76rem;">Ultra fast inference (Qwen 2.5, LLaMA 3.3).</div>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill align-self-start mt-2">Rekomendasi</span>
                                </label>
                            </div>
                            <!-- Gemini -->
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="ai_provider" id="provider_gemini" value="gemini" autocomplete="off" <?= $currentProvider === 'gemini' ? 'checked' : ''; ?> onchange="onProviderChanged('gemini')">
                                <label class="btn btn-outline-primary w-100 p-3 rounded-4 text-start d-flex flex-column justify-content-between h-100" for="provider_gemini">
                                    <div>
                                        <div class="fw-bold fs-6 mb-1"><i class="bi bi-google text-danger me-1"></i> Google Gemini</div>
                                        <div class="small text-muted" style="font-size: 0.76rem;">Gemini 1.5 Flash & Pro via Google AI Studio.</div>
                                    </div>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill align-self-start mt-2">Google Cloud</span>
                                </label>
                            </div>
                            <!-- Nvidia -->
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="ai_provider" id="provider_nvidia" value="nvidia" autocomplete="off" <?= $currentProvider === 'nvidia' ? 'checked' : ''; ?> onchange="onProviderChanged('nvidia')">
                                <label class="btn btn-outline-primary w-100 p-3 rounded-4 text-start d-flex flex-column justify-content-between h-100" for="provider_nvidia">
                                    <div>
                                        <div class="fw-bold fs-6 mb-1"><i class="bi bi-cpu-fill text-success me-1"></i> NVIDIA NIM</div>
                                        <div class="small text-muted" style="font-size: 0.76rem;">Llama-3.1 Nemotron via integrate.api.nvidia.com.</div>
                                    </div>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill align-self-start mt-2">Enterprise</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Model AI -->
                    <div class="mb-4">
                        <label for="ai_model" class="form-label fw-bold text-cute-dark d-flex justify-content-between">
                            <span>Nama Model AI:</span>
                            <span class="text-muted small fw-normal" id="modelHelpText">Ketik atau pilih preset model di bawah</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-box text-primary"></i></span>
                            <input type="text" class="form-control" id="ai_model" name="ai_model" value="<?= htmlspecialchars($currentModel); ?>" required placeholder="Contoh: qwen/qwen3.8-27b">
                        </div>
                        <!-- Presets Dropdown Pills -->
                        <div class="mt-2 d-flex flex-wrap gap-1" id="modelPresetsContainer">
                            <!-- Injected by JavaScript based on provider -->
                        </div>
                    </div>

                    <!-- Kunci API (API Key) -->
                    <div class="mb-4">
                        <label for="ai_api_key" class="form-label fw-bold text-cute-dark d-flex justify-content-between">
                            <span>API Key:</span>
                            <?php if (!empty($maskedKey)): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle-fill me-1"></i> Tersimpan (<?= $maskedKey ?>)
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i> Belum Dikonfigurasi
                                </span>
                            <?php endif; ?>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-key-fill text-primary"></i></span>
                            <input type="password" class="form-control" id="ai_api_key" name="ai_api_key" placeholder="<?= !empty($maskedKey) ? 'Biarkan kosong jika tidak ingin mengubah (' . $maskedKey . ')' : 'Masukkan API Key dari provider'; ?>" autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleApiKeyVisibility()" id="btnToggleKey" title="Tampilkan / Sembunyikan Key">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </div>
                        <div class="form-text text-muted small mt-1">
                            Kunci API disimpan secara aman di database dan tidak akan dibocorkan ke pengunjung publik.
                        </div>
                    </div>

                    <!-- Slider Temperature & Max Tokens -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="ai_temperature" class="form-label fw-bold text-cute-dark d-flex justify-content-between">
                                <span>Kreativitas (Temperature):</span>
                                <span class="badge bg-primary text-white" id="tempValDisplay"><?= htmlspecialchars((string)($data['ai_temperature'] ?? '0.5')); ?></span>
                            </label>
                            <input type="range" class="form-range" min="0.0" max="1.0" step="0.05" id="ai_temperature" name="ai_temperature" value="<?= htmlspecialchars((string)($data['ai_temperature'] ?? '0.5')); ?>" oninput="document.getElementById('tempValDisplay').innerText = this.value">
                            <div class="d-flex justify-content-between small text-muted" style="font-size: 0.72rem;">
                                <span>0.0 (Presisi/Ketat)</span>
                                <span>0.5 (Seimbang)</span>
                                <span>1.0 (Kreatif)</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="ai_max_tokens" class="form-label fw-bold text-cute-dark">
                                Batas Token (Max Tokens):
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="ai_max_tokens" name="ai_max_tokens" value="<?= htmlspecialchars((string)($data['ai_max_tokens'] ?? '500')); ?>" min="50" max="2048" step="50" required>
                                <span class="input-group-text bg-light text-muted small">tokens</span>
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">
                                Rentang 50 - 2048 (default: 500 token untuk respon cepat).
                            </div>
                        </div>
                    </div>

                    <!-- Custom System Prompt / Instruksi Tambahan Toko -->
                    <div class="mb-4">
                        <label for="ai_system_instruction" class="form-label fw-bold text-cute-dark">
                            Instruksi Tambahan Toko (Optional System Prompt):
                        </label>
                        <textarea class="form-control" id="ai_system_instruction" name="ai_system_instruction" rows="3" placeholder="Contoh: Berikan salam khas 'Halo Kakak Cantik!' di awal chat, dan sarankan promo bundling gelang jika user belanja di atas Rp 50.000..."><?= htmlspecialchars((string)($data['ai_system_instruction'] ?? '')); ?></textarea>
                        <div class="form-text text-muted small">
                            Instruksi ini akan ditambahkan ke panduan AI Assistant toko tanpa mengorbankan batasan privasi data internal.
                        </div>
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <button type="button" class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm d-inline-flex align-items-center gap-2" onclick="confirmSaveAi()">
                            <i class="bi bi-save2-fill"></i> Simpan Konfigurasi AI
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <!-- Kolom Live Test & Panduan API Key -->
    <div class="col-lg-5">
        
        <!-- Live Test Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--bg-card); border-left: 4px solid #6366f1 !important;">
            <div class="card-header border-0 bg-transparent py-3 px-4">
                <h5 class="fw-bold mb-0 text-cute-dark d-flex align-items-center gap-2">
                    <i class="bi bi-lightning-charge-fill text-warning"></i> Uji Koneksi AI (Live Test)
                </h5>
            </div>
            <div class="card-body px-4 pb-4 pt-1">
                <p class="text-muted small mb-3">
                    Uji apakah konfigurasi provider, model, dan API Key berhasil terhubung dan merespons dalam hitungan milidetik secara real-time.
                </p>

                <div class="d-grid mb-3">
                    <button type="button" class="btn btn-outline-primary rounded-pill py-2 fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm" id="btnTestAi" onclick="runAiLiveTest()">
                        <span class="spinner-border spinner-border-sm d-none" id="testAiSpinner" role="status" aria-hidden="true"></span>
                        <i class="bi bi-broadcast" id="testAiIcon"></i>
                        <span id="testAiBtnText">Uji Koneksi Sekarang</span>
                    </button>
                </div>

                <!-- Box Hasil Uji Coba -->
                <div id="testAiResultBox" class="d-none rounded-3 p-3" style="background: var(--bg-card-subtle); border: 1px solid var(--border-color);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge" id="testAiBadgeStatus">Status</span>
                        <span class="small text-muted font-monospace" id="testAiLatency">0 ms</span>
                    </div>
                    <div class="small fw-semibold text-cute-dark mb-1" id="testAiResultTitle">Hasil Pengujian:</div>
                    <div class="small text-muted font-monospace p-2 rounded border bg-white" id="testAiResponseContent" style="max-height: 180px; overflow-y: auto; white-space: pre-wrap; font-size: 0.82rem;">
                        -
                    </div>
                </div>
            </div>
        </div>

        <!-- Panduan Mendapatkan API Key -->
        <div class="card border-0 shadow-sm rounded-4" style="background: var(--bg-card);">
            <div class="card-header border-0 bg-transparent py-3 px-4">
                <h6 class="fw-bold mb-0 text-cute-dark">
                    <i class="bi bi-info-circle text-info me-2"></i>Panduan Mendapatkan API Key
                </h6>
            </div>
            <div class="card-body px-4 pb-4 pt-1 text-muted small">
                <div class="mb-3">
                    <strong class="text-dark d-block">1. Groq (Rekomendasi Cepat & Gratis):</strong>
                    Buka <a href="https://console.groq.com/keys" target="_blank" rel="noopener noreferrer" class="text-primary text-decoration-none">console.groq.com/keys</a>, login, klik <em>Create API Key</em>.
                </div>
                <div class="mb-3">
                    <strong class="text-dark d-block">2. Google Gemini:</strong>
                    Buka <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer" class="text-danger text-decoration-none">aistudio.google.com/app/apikey</a>, buat project dan salin key Anda.
                </div>
                <div>
                    <strong class="text-dark d-block">3. NVIDIA NIM:</strong>
                    Buka <a href="https://build.nvidia.com" target="_blank" rel="noopener noreferrer" class="text-success text-decoration-none">build.nvidia.com</a>, daftar dan dapatkan API key untuk inferensi LLaMA 3.1.
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Modal Konfirmasi Simpan -->
<div class="modal fade" id="modalConfirmSaveAi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-cute-dark">
                    <i class="bi bi-question-circle text-primary me-2"></i>Konfirmasi Perubahan AI
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-0 text-muted">
                    Apakah Anda yakin ingin menyimpan perubahan konfigurasi AI Model ini? Perubahan akan langsung memengaruhi perilaku AI Chatbot asisten belanja toko di seluruh halaman.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" onclick="document.getElementById('formAiSettings').submit()">
                    Ya, Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Model presets per provider
const modelPresets = {
    groq: [
        { id: 'qwen/qwen3.8-27b', label: 'Qwen 2.5 27B (Default Cerdas)' },
        { id: 'llama-3.3-70b-versatile', label: 'LLaMA 3.3 70B Versatile' },
        { id: 'llama-3.1-8b-instant', label: 'LLaMA 3.1 8B Instant (Ringan)' },
        { id: 'mixtral-8x7b-32768', label: 'Mixtral 8x7B' }
    ],
    gemini: [
        { id: 'gemini-1.5-flash', label: 'Gemini 1.5 Flash (Cepat & Akurat)' },
        { id: 'gemini-1.5-pro', label: 'Gemini 1.5 Pro (Kemampuan Analisis Tinggi)' }
    ],
    nvidia: [
        { id: 'nvidia/llama-3.1-nemotron-51b-instruct', label: 'Llama 3.1 Nemotron 51B' },
        { id: 'meta/llama-3.1-70b-instruct', label: 'Meta LLaMA 3.1 70B' }
    ]
};

function onProviderChanged(provider) {
    const container = document.getElementById('modelPresetsContainer');
    container.innerHTML = '';
    const presets = modelPresets[provider] || [];

    presets.forEach(p => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-outline-secondary rounded-pill py-0 px-2';
        btn.style.fontSize = '0.74rem';
        btn.innerText = p.label;
        btn.onclick = () => {
            document.getElementById('ai_model').value = p.id;
        };
        container.appendChild(btn);
    });

    // If current model doesn't match provider, set default
    const currentModelVal = document.getElementById('ai_model').value;
    if (presets.length > 0 && !presets.some(p => p.id === currentModelVal)) {
        document.getElementById('ai_model').value = presets[0].id;
    }
}

function toggleApiKeyVisibility() {
    const input = document.getElementById('ai_api_key');
    const btn = document.getElementById('btnToggleKey');
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="bi bi-eye-slash-fill"></i>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<i class="bi bi-eye-fill"></i>';
    }
}

function confirmSaveAi() {
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmSaveAi'));
    modal.show();
}

async function runAiLiveTest() {
    const btn = document.getElementById('btnTestAi');
    const spinner = document.getElementById('testAiSpinner');
    const icon = document.getElementById('testAiIcon');
    const btnText = document.getElementById('testAiBtnText');

    const resultBox = document.getElementById('testAiResultBox');
    const badgeStatus = document.getElementById('testAiBadgeStatus');
    const latencyDisplay = document.getElementById('testAiLatency');
    const responseBox = document.getElementById('testAiResponseContent');

    const provider = document.querySelector('input[name="ai_provider"]:checked')?.value || 'groq';
    const model = document.getElementById('ai_model').value;
    const apiKey = document.getElementById('ai_api_key').value;
    const temperature = document.getElementById('ai_temperature').value;
    const maxTokens = document.getElementById('ai_max_tokens').value;
    const instruction = document.getElementById('ai_system_instruction').value;
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    btn.disabled = true;
    spinner.classList.remove('d-none');
    icon.classList.add('d-none');
    btnText.innerText = 'Menguji koneksi...';

    try {
        const res = await fetch('<?= BASEURL; ?>/adminSettings/testAi', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                csrf_token: csrfToken,
                provider: provider,
                model: model,
                api_key: apiKey,
                temperature: temperature,
                max_tokens: maxTokens,
                system_instruction: instruction
            })
        });

        const data = await res.json();
        resultBox.classList.remove('d-none');
        latencyDisplay.innerText = (data.latency_ms || 0) + ' ms';

        if (data.status) {
            badgeStatus.className = 'badge bg-success text-white';
            badgeStatus.innerText = 'Koneksi Berhasil (' + (data.provider || provider) + ')';
            responseBox.innerText = data.response || 'Berhasil terhubung.';
            responseBox.className = 'small text-dark font-monospace p-2 rounded border bg-light';
        } else {
            badgeStatus.className = 'badge bg-danger text-white';
            badgeStatus.innerText = 'Gagal';
            responseBox.innerText = data.message || 'Terjadi kesalahan tidak diketahui.';
            responseBox.className = 'small text-danger font-monospace p-2 rounded border bg-light';
        }
    } catch (err) {
        resultBox.classList.remove('d-none');
        badgeStatus.className = 'badge bg-danger text-white';
        badgeStatus.innerText = 'Koneksi Error';
        latencyDisplay.innerText = '-';
        responseBox.innerText = 'Gagal menghubungi server untuk pengetesan: ' + err.message;
        responseBox.className = 'small text-danger font-monospace p-2 rounded border bg-light';
    } finally {
        btn.disabled = false;
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
        btnText.innerText = 'Uji Koneksi Sekarang';
    }
}

// Initialise presets on page load
document.addEventListener('DOMContentLoaded', () => {
    const activeProvider = document.querySelector('input[name="ai_provider"]:checked')?.value || 'groq';
    onProviderChanged(activeProvider);
});
</script>
