<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/hero-slides.php
 * Description: Admin panel for managing homepage hero slider
 * =============================================================
 */

require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
require_once SERVICES_PATH . '/HeroSlideService.php';

$heroService = new HeroSlideService();
$slides      = $heroService->getAllSlides();
?>

<style>
:root {
    --brand: #7a1f1f;
    --brand-dark: #5a1414;
    --orange: #e8782c;
    --cream: #fff5e6;
    --border: #f1e6d6;
}

/* ── PAGE LAYOUT ── */
.hs-content { 
    padding: 20px 24px; 
    overflow-x: hidden;
    width: 100%;
    box-sizing: border-box;
    min-width: 0;
}
@media (max-width: 575.98px) {
    .hs-content { padding: 12px 10px; }
}
@media (min-width: 576px) and (max-width: 991.98px) {
    .hs-content { padding: 16px 14px; }
}
.hs-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 24px; flex-wrap: wrap; gap: 10px;
}
.hs-header h1 { font-size: clamp(1.3rem, 3vw, 1.7rem); font-weight: 800; color: var(--brand); margin: 0; }
.hs-header p  { color: #666; font-size: 0.9rem; margin: 4px 0 0; }

/* ── SLIDE CARDS GRID ── */
/* Mobile first: 1 column */
.hs-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 16px;
    margin-bottom: 32px;
    width: 100%;
    box-sizing: border-box;
}
/* Tablet 576px+: 2 columns */
@media (min-width: 576px) {
    .hs-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }
}
/* Desktop 1200px+: 3 columns */
@media (min-width: 1200px) {
    .hs-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
.hs-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    transition: box-shadow 0.2s ease, transform 0.2s ease;
    cursor: grab;
}
.hs-card:hover { box-shadow: 0 8px 30px rgba(122,31,31,0.1); transform: translateY(-3px); }
.hs-card__img {
    width: 100%; height: 160px;
    object-fit: cover;
    background: #f9f3ec;
    display: block;
    position: relative;
}
.hs-card__img img {
    width: 100%; height: 160px; object-fit: cover; display: block;
}
.hs-card__img-placeholder {
    width: 100%; height: 160px;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #f8ece0, #fdf4ec);
    color: #c26510; font-size: 2.5rem;
}
.hs-card__body { padding: 16px 18px; }
.hs-card__order {
    display: inline-block;
    background: var(--cream);
    color: var(--brand);
    font-size: 0.75rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 999px;
    margin-bottom: 8px;
    border: 1px solid var(--border);
}
.hs-card__title { font-size: 1rem; font-weight: 700; color: #2a1a1a; margin-bottom: 2px; }
.hs-card__accent { color: var(--orange); font-size: 0.9rem; font-weight: 600; margin-bottom: 6px; }
.hs-card__tagline { font-size: 0.8rem; color: #888; margin-bottom: 10px; }
.hs-card__btn-preview {
    display: inline-block;
    padding: 4px 12px;
    background: linear-gradient(90deg, var(--brand), var(--orange));
    color: #fff;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    margin-bottom: 12px;
}
.hs-card__status {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    margin-left: 6px;
}
.hs-card__status--active   { background: #d1fae5; color: #065f46; }
.hs-card__status--inactive { background: #fee2e2; color: #991b1b; }

.hs-card__actions {
    display: flex; gap: 8px; margin-top: 12px;
    padding-top: 12px; border-top: 1px solid var(--border);
    flex-wrap: wrap;
}
.btn-hs-edit {
    /* flex: 1; */
    background: var(--brand);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-hs-edit:hover { background: var(--brand-dark); }
.btn-hs-toggle {
    background: #f1e6d6;
    color: var(--brand);
    border: none;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-hs-toggle:hover { background: #e8d5c0; }
.btn-hs-delete {
    background: #fee2e2;
    color: #991b1b;
    border: none;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-hs-delete:hover { background: #fecaca; }

/* ── MODAL ── */
.hs-modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.55);
    z-index: 9000;
    align-items: center; justify-content: center;
}
.hs-modal-overlay.open { display: flex; }
.hs-modal {
    background: #fff;
    border-radius: 20px;
    width: 100%;
    max-width: 640px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 32px;
    position: relative;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    animation: hsModalIn 0.2s ease;
}
@keyframes hsModalIn {
    from { opacity: 0; transform: translateY(-16px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0)    scale(1); }
}
.hs-modal__close {
    position: absolute; top: 16px; right: 16px;
    background: #f1e6d6;
    border: none; border-radius: 50%;
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 1.1rem; color: var(--brand);
}
.hs-modal__close:hover { background: #e8d5c0; }
.hs-modal h2 { font-size: 1.3rem; font-weight: 800; color: var(--brand); margin-bottom: 20px; }

.hs-form-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 700;
    color: #444;
    margin-bottom: 6px;
}
.hs-form-input, .hs-form-select {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid #e5d5c5;
    border-radius: 10px;
    font-size: 0.9rem;
    color: #333;
    background: #fff;
    transition: border-color 0.15s;
}
.hs-form-input:focus, .hs-form-select:focus {
    outline: none;
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(122,31,31,0.08);
}
.hs-form-group { margin-bottom: 16px; }

.img-preview-wrap {
    width: 100%; height: 120px;
    border: 2px dashed #e5d5c5;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; cursor: pointer; margin-bottom: 8px;
    background: #fdf8f3;
    transition: border-color 0.15s;
    position: relative;
}
.img-preview-wrap:hover { border-color: var(--brand); }
.img-preview-wrap img { width: 100%; height: 100%; object-fit: cover; }
.img-preview-wrap .upload-hint {
    text-align: center; color: #aaa; font-size: 0.85rem;
    pointer-events: none;
}
.img-preview-wrap .upload-hint i { font-size: 2rem; display: block; margin-bottom: 4px; }

.hs-modal__footer {
    display: flex; gap: 10px; justify-content: flex-end;
    margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);
}
.btn-modal-save {
    background: var(--brand); color: #fff;
    border: none; border-radius: 10px;
    padding: 12px 28px; font-size: 0.95rem; font-weight: 700;
    cursor: pointer; transition: background 0.15s;
}
.btn-modal-save:hover { background: var(--brand-dark); }
.btn-modal-cancel {
    background: #f1e6d6; color: #555;
    border: none; border-radius: 10px;
    padding: 12px 22px; font-size: 0.95rem; font-weight: 600;
    cursor: pointer;
}

/* ── TOAST ── */
.hs-toast-box { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
.hs-toast {
    background: var(--brand); color: #fff;
    padding: 12px 20px; border-radius: 10px;
    font-size: 0.9rem; font-weight: 600;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    animation: hsTIn 0.2s ease;
}
@keyframes hsTIn { from { opacity:0; transform: translateX(20px); } to { opacity:1; transform: translateX(0); } }
.hs-toast.success { background: #065f46; }
.hs-toast.error   { background: #991b1b; }

/* ── EMPTY STATE ── */
.hs-empty {
    text-align: center; padding: 60px 20px;
    color: #aaa;
}
.hs-empty i { font-size: 3rem; margin-bottom: 12px; display: block; }
</style>

<div class="main-content">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="hs-content">
        <!-- Header -->
        <div class="hs-header">
            <div>
                <h1><i class="bi bi-images me-2"></i>Hero Slider</h1>
                <p>Manage homepage hero slides — drag to reorder, click to edit.</p>
            </div>
            <button class="btn-hs-edit" onclick="openAddModal()" id="btnAddSlide">
                <i class="bi bi-plus-lg me-1"></i> Add New Slide
            </button>
        </div>

        <!-- Slides Grid -->
        <div class="hs-grid" id="hsGrid">
            <?php if (empty($slides)): ?>
            <div class="hs-empty">
                <i class="bi bi-images"></i>
                <p>No slides yet. Click <strong>Add New Slide</strong> to get started.</p>
            </div>
            <?php else: ?>
            <?php foreach ($slides as $slide): ?>
            <div class="hs-card" data-id="<?php echo (int)$slide['id']; ?>">
                <div class="hs-card__img">
                    <?php if (!empty($slide['desktop_image'])): ?>
                    <img src="<?php echo BASE_URL . htmlspecialchars($slide['desktop_image']); ?>"
                         alt="Slide <?php echo (int)$slide['sort_order']; ?>"
                         onerror="this.parentElement.innerHTML='<div class=\'hs-card__img-placeholder\'><i class=\'bi bi-image\'></i></div>'">
                    <?php else: ?>
                    <div class="hs-card__img-placeholder"><i class="bi bi-image"></i></div>
                    <?php endif; ?>
                </div>
                <div class="hs-card__body">
                    <span class="hs-card__order">Slide #<?php echo (int)$slide['sort_order']; ?></span>
                    <span class="hs-card__status <?php echo $slide['is_active'] ? 'hs-card__status--active' : 'hs-card__status--inactive'; ?>">
                        <?php echo $slide['is_active'] ? 'Active' : 'Hidden'; ?>
                    </span>
                    <div class="hs-card__title"><?php echo htmlspecialchars($slide['title_line1']); ?></div>
                    <div class="hs-card__accent"><?php echo htmlspecialchars($slide['title_accent']); ?></div>
                    <div class="hs-card__tagline"><?php echo htmlspecialchars($slide['tagline']); ?></div>
                    <a href="<?php echo htmlspecialchars($slide['button_url']); ?>"
                       class="hs-card__btn-preview" target="_blank">
                        <?php echo htmlspecialchars($slide['button_text']); ?> →
                    </a>
                    <div class="hs-card__actions">
                        <button class="btn-hs-edit"   onclick="openEditModal(<?php echo (int)$slide['id']; ?>)">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                        <button class="btn-hs-toggle" onclick="toggleSlide(<?php echo (int)$slide['id']; ?>, this)" 
                                data-active="<?php echo (int)$slide['is_active']; ?>">
                            <i class="bi bi-<?php echo $slide['is_active'] ? 'eye-slash' : 'eye'; ?> me-1"></i><?php echo $slide['is_active'] ? 'Hide' : 'Show'; ?>
                        </button>
                        <button class="btn-hs-delete" onclick="deleteSlide(<?php echo (int)$slide['id']; ?>)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Tips -->
        <div class="alert alert-info" style="border-radius: 12px; border: 1px solid #bee3f8; background: #ebf8ff; color: #2c5282; font-size: 0.88rem;">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Tips:</strong> Changes are applied instantly on the homepage. Upload desktop (1920×600) and mobile (750×400) versions of each image for best results.
        </div>
    </div>
</div>

<!-- ── SLIDE EDITOR MODAL ── -->
<div class="hs-modal-overlay" id="hsModal">
    <div class="hs-modal">
        <button class="hs-modal__close" onclick="closeModal()"><i class="bi bi-x"></i></button>
        <h2 id="hsModalTitle">Add New Slide</h2>
        <form id="hsForm" enctype="multipart/form-data">
            <input type="hidden" id="hs_id" name="id" value="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

            <div class="row g-3">
                <div class="col-12">
                    <div class="hs-form-group">
                        <label class="hs-form-label">Tagline <span class="text-muted fw-normal">(e.g. 100% NATURAL & PURE)</span></label>
                        <input type="text" class="hs-form-input" id="hs_tagline" name="tagline" placeholder="ARTISANAL & TRADITIONAL" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="hs-form-group">
                        <label class="hs-form-label">Title Line 1</label>
                        <input type="text" class="hs-form-input" id="hs_title_line1" name="title_line1" placeholder="Made with Pure Ghee" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="hs-form-group">
                        <label class="hs-form-label">Title Accent <span class="text-muted fw-normal">(highlighted)</span></label>
                        <input type="text" class="hs-form-input" id="hs_title_accent" name="title_accent" placeholder="Handcrafted with Love" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="hs-form-group">
                        <label class="hs-form-label">Button Text</label>
                        <input type="text" class="hs-form-input" id="hs_button_text" name="button_text" placeholder="Shop Now">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="hs-form-group">
                        <label class="hs-form-label">Button URL / Anchor</label>
                        <input type="text" class="hs-form-input" id="hs_button_url" name="button_url" placeholder="#bestsellers">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="hs-form-group">
                        <label class="hs-form-label">Desktop Image <span class="text-muted fw-normal">(1920×600 recommended)</span></label>
                        <div class="img-preview-wrap" onclick="document.getElementById('hs_desktop_file').click()">
                            <div class="upload-hint" id="desktopPreview">
                                <i class="bi bi-cloud-arrow-up"></i>Click to upload
                            </div>
                        </div>
                        <input type="file" id="hs_desktop_file" name="desktop_file" accept="image/*" style="display:none" onchange="previewImg(this, 'desktopPreview')">
                        <input type="text" class="hs-form-input mt-1" id="hs_desktop_image" name="desktop_image" placeholder="Or enter existing path: assets/images/banners/...">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="hs-form-group">
                        <label class="hs-form-label">Mobile Image <span class="text-muted fw-normal">(750×400 recommended)</span></label>
                        <div class="img-preview-wrap" onclick="document.getElementById('hs_mobile_file').click()">
                            <div class="upload-hint" id="mobilePreview">
                                <i class="bi bi-cloud-arrow-up"></i>Click to upload
                            </div>
                        </div>
                        <input type="file" id="hs_mobile_file" name="mobile_file" accept="image/*" style="display:none" onchange="previewImg(this, 'mobilePreview')">
                        <input type="text" class="hs-form-input mt-1" id="hs_mobile_image" name="mobile_image" placeholder="Or enter existing path: assets/images/banners/...">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="hs-form-group">
                        <label class="hs-form-label">Sort Order</label>
                        <input type="number" class="hs-form-input" id="hs_sort_order" name="sort_order" value="1" min="1">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="hs-form-group">
                        <label class="hs-form-label">Status</label>
                        <select class="hs-form-select" id="hs_is_active" name="is_active">
                            <option value="1">Active (visible)</option>
                            <option value="0">Hidden</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="hs-modal__footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-modal-save" id="hsSaveBtn">
                    <i class="bi bi-save me-1"></i> Save Slide
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Box -->
<div class="hs-toast-box" id="hsToastBox"></div>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';
const apiUrl   = BASE_URL + 'admin/api/hero-slides.php';

/* ── TOAST ── */
function hsToast(msg, type = '') {
    const box  = document.getElementById('hsToastBox');
    const div  = document.createElement('div');
    div.className = 'hs-toast ' + type;
    div.textContent = msg;
    box.appendChild(div);
    setTimeout(() => div.remove(), 3000);
}

/* ── IMAGE PREVIEW ── */
function previewImg(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/* ── MODAL OPEN/CLOSE ── */
function openAddModal() {
    document.getElementById('hsModalTitle').textContent = 'Add New Slide';
    document.getElementById('hsForm').reset();
    document.getElementById('hs_id').value = '';
    document.getElementById('desktopPreview').innerHTML = '<i class="bi bi-cloud-arrow-up"></i>Click to upload';
    document.getElementById('mobilePreview').innerHTML  = '<i class="bi bi-cloud-arrow-up"></i>Click to upload';
    document.getElementById('hsModal').classList.add('open');
}

function openEditModal(id) {
    fetch(apiUrl + '?action=get&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { hsToast('Failed to load slide', 'error'); return; }
            const s = data.slide;
            document.getElementById('hsModalTitle').textContent    = 'Edit Slide';
            document.getElementById('hs_id').value                 = s.id;
            document.getElementById('hs_tagline').value            = s.tagline || '';
            document.getElementById('hs_title_line1').value        = s.title_line1 || '';
            document.getElementById('hs_title_accent').value       = s.title_accent || '';
            document.getElementById('hs_button_text').value        = s.button_text || 'Shop Now';
            document.getElementById('hs_button_url').value         = s.button_url  || '#bestsellers';
            document.getElementById('hs_desktop_image').value      = s.desktop_image || '';
            document.getElementById('hs_mobile_image').value       = s.mobile_image  || '';
            document.getElementById('hs_sort_order').value         = s.sort_order  || 1;
            document.getElementById('hs_is_active').value          = s.is_active   || 1;

            // Show existing images in preview areas
            const dp = document.getElementById('desktopPreview');
            if (s.desktop_image) {
                dp.innerHTML = `<img src="${BASE_URL + s.desktop_image}" style="width:100%;height:100%;object-fit:cover;">`;
            } else {
                dp.innerHTML = '<i class="bi bi-cloud-arrow-up"></i>Click to upload';
            }
            const mp = document.getElementById('mobilePreview');
            if (s.mobile_image) {
                mp.innerHTML = `<img src="${BASE_URL + s.mobile_image}" style="width:100%;height:100%;object-fit:cover;">`;
            } else {
                mp.innerHTML = '<i class="bi bi-cloud-arrow-up"></i>Click to upload';
            }

            document.getElementById('hsModal').classList.add('open');
        })
        .catch(() => hsToast('Network error', 'error'));
}

function closeModal() {
    document.getElementById('hsModal').classList.remove('open');
}

document.getElementById('hsModal').addEventListener('click', e => {
    if (e.target === document.getElementById('hsModal')) closeModal();
});

/* ── FORM SUBMIT ── */
document.getElementById('hsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('hsSaveBtn');
    btn.disabled = true;
    btn.textContent = 'Saving…';

    const formData = new FormData(this);
    const id = document.getElementById('hs_id').value;
    formData.append('action', id ? 'update' : 'create');

    fetch(apiUrl, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                hsToast(data.message || 'Saved!', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                hsToast(data.message || 'Save failed', 'error');
            }
        })
        .catch(() => hsToast('Network error', 'error'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save me-1"></i> Save Slide';
        });
});

/* ── TOGGLE ACTIVE ── */
function toggleSlide(id, btn) {
    const active = parseInt(btn.dataset.active);
    const newActive = active ? 0 : 1;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const fd = new FormData();
    fd.append('action', 'toggle');
    fd.append('id', id);
    fd.append('is_active', newActive);
    fd.append('csrf_token', csrfToken);

    fetch(apiUrl, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                hsToast(newActive ? 'Slide is now visible' : 'Slide hidden', 'success');
                setTimeout(() => location.reload(), 600);
            } else {
                hsToast('Failed to update', 'error');
            }
        })
        .catch(() => hsToast('Network error', 'error'));
}

/* ── DELETE ── */
function deleteSlide(id) {
    if (!confirm('Delete this slide? This cannot be undone.')) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    fd.append('csrf_token', csrfToken);

    fetch(apiUrl, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                hsToast('Slide deleted', 'success');
                setTimeout(() => location.reload(), 600);
            } else {
                hsToast(data.message || 'Delete failed', 'error');
            }
        })
        .catch(() => hsToast('Network error', 'error'));
}
</script>

<?php require_once 'includes/footer.php'; ?>
