/**
 * Sweets Website
 * =============================================================
 * File: profile.js
 * Description: Client-side logic for Admin Profile interactions
 * Author: Antigravity - Principal Front-end Developer
 * =============================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    initProfileManager();
    initChangePassword();
});

function initProfileManager() {
    const profileForm = document.getElementById('profileForm');
    const stickyFooter = document.getElementById('profileStickyFooter');
    const saveBtn = document.getElementById('saveProfileBtn');
    const headerSaveBtn = document.getElementById('headerSaveBtn');
    const cancelBtn = document.getElementById('cancelProfileBtn');
    const headerCancelBtn = document.getElementById('headerCancelBtn');

    // Profile Picture elements — declared early so doSave can reference heroAvatar
    const dropzone    = document.getElementById('avatarDropzone');
    const avatarInput = document.getElementById('avatarInput');
    const editPicBtn  = document.querySelector('.btn-edit-picture');
    const heroAvatar  = document.querySelector('.hero-avatar-wrap img');

    if (!profileForm) return;

    // 1. Initial State Capture
    let initialState = serializeForm(profileForm);
    let avatarChanged = false;

    // 2. Track Changes (text/select inputs)
    profileForm.addEventListener('input', () => {
        const currentState = serializeForm(profileForm);
        const textChanged = JSON.stringify(initialState) !== JSON.stringify(currentState);
        if (textChanged || avatarChanged) {
            stickyFooter.classList.add('visible');
        } else {
            stickyFooter.classList.remove('visible');
        }
    });

    // 3. Reset / Cancel
    const doCancel = () => {
        profileForm.reset();
        avatarChanged = false;
        initialState = serializeForm(profileForm);
        stickyFooter.classList.remove('visible');
        showProfileToast('Changes discarded', 'warning');
    };
    cancelBtn.addEventListener('click', doCancel);
    if (headerCancelBtn) headerCancelBtn.addEventListener('click', doCancel);

    // 4. Save Logic
    const doSave = async () => {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        if (headerSaveBtn) { headerSaveBtn.disabled = true; }

        try {
            const formData = new FormData(profileForm);
            const apiUrl = (window.BASE_URL || '') + 'admin/api/v1/update_profile.php';

            const response = await fetch(apiUrl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (response.ok && result.status === 'success') {
                showProfileToast(result.message, 'success');
                avatarChanged = false;
                initialState = serializeForm(profileForm);
                stickyFooter.classList.remove('visible');

                const displayName = document.getElementById('displayUserName');
                if (displayName) displayName.textContent = result.data.full_name;

                // Update topbar name + avatar if a new picture was uploaded
                const topbarName = document.querySelector('.profile-name');
                if (topbarName) topbarName.textContent = result.data.full_name;
                if (result.data.avatar_url) {
                    const topbarAvatar = document.querySelector('.profile-avatar img');
                    if (topbarAvatar) topbarAvatar.src = result.data.avatar_url;
                    if (heroAvatar)   heroAvatar.src = result.data.avatar_url;
                }
            } else {
                showProfileToast(result.message || 'Failed to update profile.', 'error');
            }
        } catch (error) {
            console.error('[Profile Save Error]', error);
            showProfileToast('A network error occurred. Please try again.', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Save Changes';
            if (headerSaveBtn) { headerSaveBtn.disabled = false; }
        }
    };

    saveBtn.addEventListener('click', doSave);
    if (headerSaveBtn) headerSaveBtn.addEventListener('click', doSave);

    // 5. Profile Picture — Click to open file picker + Drag & Drop
    const openPicker = () => avatarInput && avatarInput.click();

    // Clicking the dropzone or "Edit Picture" opens the file picker
    if (dropzone)   dropzone.addEventListener('click', openPicker);
    if (editPicBtn) editPicBtn.addEventListener('click', openPicker);

    if (avatarInput) {
        avatarInput.addEventListener('change', () => {
            const file = avatarInput.files[0];
            if (!file) return;

            // Validate size (max 2 MB)
            if (file.size > 2 * 1024 * 1024) {
                showProfileToast('File is too large. Max 2 MB allowed.', 'error');
                avatarInput.value = '';
                return;
            }

            // Preview the image immediately
            const reader = new FileReader();
            reader.onload = (e) => {
                if (heroAvatar) heroAvatar.src = e.target.result;
                // Update dropzone text to show filename
                const dropText = dropzone ? dropzone.querySelector('.dropzone-text p') : null;
                if (dropText) dropText.textContent = `Selected: ${file.name}`;
            };
            reader.readAsDataURL(file);

            avatarChanged = true;
            showProfileToast(`Image selected: ${file.name}`, 'info');
            stickyFooter.classList.add('visible');
        });
    }

    if (dropzone) {
        // Prevent default browser file-open on drag events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(evt => {
            dropzone.addEventListener(evt, e => { e.preventDefault(); e.stopPropagation(); });
        });

        ['dragenter', 'dragover'].forEach(evt => {
            dropzone.addEventListener(evt, () => dropzone.style.borderColor = '#AE4B3A');
        });

        ['dragleave', 'drop'].forEach(evt => {
            dropzone.addEventListener(evt, () => dropzone.style.borderColor = '#ECCCBC');
        });

        dropzone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length && avatarInput) {
                const dt = new DataTransfer();
                dt.items.add(files[0]);
                avatarInput.files = dt.files;
                avatarInput.dispatchEvent(new Event('change'));
            }
        });
    }

    // Helper: serialize only non-file fields
    function serializeForm(form) {
        const data = {};
        new FormData(form).forEach((value, key) => {
            if (key !== 'avatar') data[key] = value; // exclude file inputs
        });
        return data;
    }
}

/* =========================================================
 *  Change Password Modal
 * ========================================================= */
function initChangePassword() {
    // Build modal if it doesn't exist
    if (!document.getElementById('changePasswordModal')) {
        const modal = document.createElement('div');
        modal.id = 'changePasswordModal';
        modal.className = 'cp-modal-overlay';
        modal.innerHTML = `
            <div class="cp-modal-box">
                <div class="cp-modal-header">
                    <h5><i class="bi bi-shield-lock me-2"></i>Change Password</h5>
                    <button type="button" class="cp-close-btn" id="cpCloseBtn"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="cp-modal-body">
                    <div class="cp-field-group">
                        <label class="form-label-maroon">Current Password</label>
                        <div class="cp-input-wrap">
                            <input type="password" id="cpCurrentPass" class="form-control-maroon w-100" placeholder="Enter current password">
                            <button type="button" class="cp-eye-btn" data-target="cpCurrentPass"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <div class="cp-field-group">
                        <label class="form-label-maroon">New Password</label>
                        <div class="cp-input-wrap">
                            <input type="password" id="cpNewPass" class="form-control-maroon w-100" placeholder="Min. 8 characters">
                            <button type="button" class="cp-eye-btn" data-target="cpNewPass"><i class="bi bi-eye"></i></button>
                        </div>
                        <div class="cp-strength" id="cpStrengthBar"><div class="cp-strength-fill" id="cpStrengthFill"></div></div>
                        <small id="cpStrengthLabel" class="cp-strength-text"></small>
                    </div>
                    <div class="cp-field-group">
                        <label class="form-label-maroon">Confirm New Password</label>
                        <div class="cp-input-wrap">
                            <input type="password" id="cpConfirmPass" class="form-control-maroon w-100" placeholder="Repeat new password">
                            <button type="button" class="cp-eye-btn" data-target="cpConfirmPass"><i class="bi bi-eye"></i></button>
                        </div>
                        <small id="cpMatchMsg" class="cp-match-text"></small>
                    </div>
                </div>
                <div class="cp-modal-footer">
                    <button type="button" class="btn btn-light px-4 border" id="cpCancelBtn">Cancel</button>
                    <button type="button" class="btn btn-save-maroon px-4" id="cpSaveBtn">Update Password</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Add inline CSS for modal
        const style = document.createElement('style');
        style.textContent = `
            .cp-modal-overlay {
                display: none;
                position: fixed; inset: 0; z-index: 9990;
                background: rgba(30,10,5,0.45);
                align-items: center; justify-content: center;
                backdrop-filter: blur(4px);
            }
            .cp-modal-overlay.active { display: flex; }
            .cp-modal-box {
                background: #fff;
                border-radius: 16px;
                width: 100%; max-width: 460px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.18);
                overflow: hidden;
                animation: cpSlideIn .25s ease;
            }
            @keyframes cpSlideIn {
                from { transform: translateY(-30px); opacity: 0; }
                to   { transform: translateY(0);     opacity: 1; }
            }
            .cp-modal-header {
                background: linear-gradient(135deg, #7B1F1F 0%, #4D1212 100%);
                color: #fff;
                padding: 1.25rem 1.5rem;
                display: flex; align-items: center; justify-content: space-between;
            }
            .cp-modal-header h5 { margin: 0; font-size: 1rem; font-weight: 700; }
            .cp-close-btn {
                background: none; border: none; color: #fff;
                opacity: .7; cursor: pointer; font-size: 1rem;
                line-height: 1; padding: 0;
            }
            .cp-close-btn:hover { opacity: 1; }
            .cp-modal-body { padding: 1.5rem; }
            .cp-field-group { margin-bottom: 1.25rem; }
            .cp-input-wrap { position: relative; }
            .cp-input-wrap .form-control-maroon { padding-right: 42px; }
            .cp-eye-btn {
                position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
                background: none; border: none; color: #AE4B3A; cursor: pointer; padding: 0;
            }
            .cp-strength { height: 4px; background: #ECCCBC; border-radius: 4px; margin-top: 8px; overflow: hidden; }
            .cp-strength-fill { height: 100%; width: 0; border-radius: 4px; transition: width .3s, background .3s; }
            .cp-strength-text { font-size: .8rem; color: #8C8C8C; display: block; margin-top: 4px; }
            .cp-match-text { font-size: .8rem; display: block; margin-top: 4px; }
            .cp-modal-footer {
                padding: 1rem 1.5rem;
                border-top: 1px solid #ECCCBC;
                display: flex; justify-content: flex-end; gap: .75rem;
            }
        `;
        document.head.appendChild(style);
    }

    const modal       = document.getElementById('changePasswordModal');
    const cpSaveBtn   = document.getElementById('cpSaveBtn');
    const cpCancelBtn = document.getElementById('cpCancelBtn');
    const cpCloseBtn  = document.getElementById('cpCloseBtn');
    const cpCurrent   = document.getElementById('cpCurrentPass');
    const cpNew       = document.getElementById('cpNewPass');
    const cpConfirm   = document.getElementById('cpConfirmPass');
    const cpStrFill   = document.getElementById('cpStrengthFill');
    const cpStrLabel  = document.getElementById('cpStrengthLabel');
    const cpMatchMsg  = document.getElementById('cpMatchMsg');

    // Open via the "Change Password" button in Account Settings
    const changePwdBtn = document.querySelector('.btn-setting-action');
    if (changePwdBtn) {
        changePwdBtn.addEventListener('click', () => {
            cpCurrent.value = cpNew.value = cpConfirm.value = '';
            cpStrFill.style.width = '0'; cpStrFill.style.background = ''; cpStrLabel.textContent = '';
            cpMatchMsg.textContent = '';
            modal.classList.add('active');
            setTimeout(() => cpCurrent.focus(), 100);
        });
    }

    const closeModal = () => modal.classList.remove('active');
    cpCloseBtn.addEventListener('click', closeModal);
    cpCancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    // Password strength meter
    cpNew.addEventListener('input', () => {
        const v = cpNew.value;
        let score = 0;
        if (v.length >= 8) score++;
        if (/[A-Z]/.test(v)) score++;
        if (/[0-9]/.test(v)) score++;
        if (/[^A-Za-z0-9]/.test(v)) score++;
        const levels = [
            { w: '0%',   bg: '#e9ecef', lbl: '' },
            { w: '25%',  bg: '#dc3545', lbl: 'Weak' },
            { w: '50%',  bg: '#fd7e14', lbl: 'Fair' },
            { w: '75%',  bg: '#ffc107', lbl: 'Good' },
            { w: '100%', bg: '#198754', lbl: 'Strong' },
        ];
        const lvl = levels[score] || levels[0];
        cpStrFill.style.width = lvl.w; cpStrFill.style.background = lvl.bg;
        cpStrLabel.textContent = lvl.lbl;
        cpStrLabel.style.color = lvl.bg;

        // Check match live
        if (cpConfirm.value) checkMatch();
    });

    cpConfirm.addEventListener('input', checkMatch);

    function checkMatch() {
        if (!cpConfirm.value) { cpMatchMsg.textContent = ''; return; }
        if (cpNew.value === cpConfirm.value) {
            cpMatchMsg.textContent = '✓ Passwords match';
            cpMatchMsg.style.color = '#198754';
        } else {
            cpMatchMsg.textContent = '✗ Passwords do not match';
            cpMatchMsg.style.color = '#dc3545';
        }
    }

    // Eye toggles
    document.querySelectorAll('.cp-eye-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const inp = document.getElementById(btn.dataset.target);
            if (!inp) return;
            const isPass = inp.type === 'password';
            inp.type = isPass ? 'text' : 'password';
            btn.querySelector('i').className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });

    // Submit
    cpSaveBtn.addEventListener('click', async () => {
        const current = cpCurrent.value.trim();
        const newPwd  = cpNew.value.trim();
        const confirm = cpConfirm.value.trim();

        if (!current) { showProfileToast('Please enter your current password.', 'error'); return; }
        if (newPwd.length < 8) { showProfileToast('New password must be at least 8 characters.', 'error'); return; }
        if (newPwd !== confirm) { showProfileToast('Passwords do not match.', 'error'); return; }

        cpSaveBtn.disabled = true;
        cpSaveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

        try {
            const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';
            const fd = new FormData();
            fd.append('csrf_token', csrfToken);
            fd.append('current_password', current);
            fd.append('new_password', newPwd);
            fd.append('confirm_password', confirm);

            const apiUrl = (window.BASE_URL || '') + 'admin/api/v1/change_password.php';
            const res = await fetch(apiUrl, { method: 'POST', body: fd });
            const result = await res.json();

            if (res.ok && result.status === 'success') {
                showProfileToast(result.message || 'Password updated successfully!', 'success');
                closeModal();
            } else {
                showProfileToast(result.message || 'Failed to update password.', 'error');
            }
        } catch (err) {
            console.error('[Change Password Error]', err);
            showProfileToast('A network error occurred. Please try again.', 'error');
        } finally {
            cpSaveBtn.disabled = false;
            cpSaveBtn.innerHTML = 'Update Password';
        }
    });
}

/**
 * High-fidelity Toast Notification
 */
function showProfileToast(msg, type = 'success') {
    const container = document.getElementById('toastContainerMaroon') || createToastContainer();
    const toast = document.createElement('div');
    const iconMap = {
        'success': 'bi-check-circle-fill',
        'error': 'bi-x-circle-fill',
        'warning': 'bi-exclamation-triangle-fill',
        'info': 'bi-info-circle-fill'
    };

    toast.className = `custom-toast-maroon ${type} show`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="bi ${iconMap[type]} fs-5"></i>
            <span>${msg}</span>
        </div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

function createToastContainer() {
    const div = document.createElement('div');
    div.id = 'toastContainerMaroon';
    div.className = 'toast-container-maroon position-fixed top-0 start-50 translate-middle-x mt-4';
    div.style.zIndex = '9999';
    document.body.appendChild(div);
    return div;
}
