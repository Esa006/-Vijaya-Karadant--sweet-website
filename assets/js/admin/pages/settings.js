/**
 * Sweets Website
 * =============================================================
 * File: settings.js
 * Description: High-fidelity logic for settings navigation, 
 * change tracking, and security feedback.
 * =============================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // --- STATE MANAGEMENT ---
    const state = {
        activeSection: 'store-info',
        hasChanges: false,
        initialValues: {},
        currentValues: {}
    };

    // --- ELEMENT SELECTORS ---
    const navButtons = document.querySelectorAll('.nav-item-box');
    const sections = document.querySelectorAll('.settings-section');
    const floatingBar = document.getElementById('floatingActionBar');
    const unsavedChips = document.querySelectorAll('.dashboard-status-indicator');
    const countLabels = document.querySelectorAll('.changed-fields-count');
    const inputs = document.querySelectorAll('.form-control-maroon, .form-control, .form-check-input');
    const newPassInput = document.getElementById('newPasswordInput');
    const strengthBar = document.getElementById('passStrengthBar');
    const strengthLabel = document.getElementById('passStrengthLabel');
    const saveButtons = document.querySelectorAll('.btn-save-settings');
    const cancelButtons = document.querySelectorAll('.btn-cancel-settings');

    // --- INITIALIZATION ---
    function init() {
        // Capture initial values for change detection
        inputs.forEach(input => {
            const id = input.id || input.name;
            if (!id) return;
            
            const val = input.type === 'checkbox' ? input.checked : input.value;
            state.initialValues[id] = val;
            state.currentValues[id] = val;
            
            // Listen for changes
            input.addEventListener('input', handleInputChange);
            input.addEventListener('change', handleInputChange);
        });

        // Setup Navigation
        navButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const sectionId = btn.getAttribute('data-section');
                switchSection(sectionId);
            });
        });

        // Global Actions
        saveButtons.forEach(btn => btn.addEventListener('click', saveSettings));
        cancelButtons.forEach(btn => btn.addEventListener('click', resetSettings));

        // Security Actions
        if (newPassInput) {
            newPassInput.addEventListener('input', (e) => {
                updatePasswordStrength(e.target.value);
                handleInputChange(e); // Also track it for changes
            });
        }
        
        // Mock buttons
        document.querySelectorAll('.btn-outline-action').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if(e.target.textContent.includes('Setup QR')) {
                    showToast('QR Code generation initiated.', 'info');
                } else if(e.target.textContent.includes('Logout')) {
                    showToast('Logged out of all other devices.', 'success');
                }
            });
        });
        
        document.querySelectorAll('.action-link-maroon').forEach(link => {
            link.addEventListener('click', (e) => {
                showToast('Action logged.', 'info');
            });
        });

        // The flagged button
        document.querySelectorAll('.cursor-pointer.text-danger').forEach(link => {
            if(link.textContent.includes('Flagged')) {
                link.addEventListener('click', () => {
                    showToast('IP Address blocked temporarily.', 'info');
                });
            }
        });

        // Password Confirm Match checking
        const confirmPass = document.getElementById('confirmPasswordInput');
        if (confirmPass && newPassInput) {
            confirmPass.addEventListener('input', () => {
                if (confirmPass.value && confirmPass.value !== newPassInput.value) {
                    confirmPass.style.borderColor = 'red';
                } else {
                    confirmPass.style.borderColor = '';
                }
            });
        }

        // Color Sync
        initColorSync('ui_primary_color', 'ui_primary_color_text');
        initColorSync('ui_secondary_color', 'ui_secondary_color_text');

        // File Previews
        initFilePreview('store_logo_file', 'logoPreview');
        initFilePreview('ui_logo_file', 'logoPreview');
        initFilePreview('ui_favicon_file', 'faviconPreview');
        initFilePreview('shop_qr_file', 'qrPreview');
    }

    function initColorSync(pickerId, textId) {
        const picker = document.getElementById(pickerId);
        const text = document.getElementById(textId);
        if (!picker || !text) return;

        picker.addEventListener('input', () => {
            text.value = picker.value.toUpperCase();
            handleInputChange({ target: text });
        });

        text.addEventListener('input', () => {
            if (/^#[0-9A-F]{6}$/i.test(text.value)) {
                picker.value = text.value;
                handleInputChange({ target: picker });
            }
        });
    }

    function initFilePreview(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (!input || !preview) return;

        input.addEventListener('change', e => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    const img = preview.querySelector('img');
                    if (img) img.src = e.target.result;
                    
                    // Show container (useful for initially hidden previews like QR)
                    preview.style.display = 'block';
                    
                    // Track file change in state
                    state.currentValues[inputId] = file;
                    calculateDiff();
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // --- NAVIGATION LOGIC ---
    function switchSection(sectionId) {
        if (state.activeSection === sectionId) return;

        // Update Nav UI
        navButtons.forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-section') === sectionId);
        });

        // Update Content UI
        sections.forEach(sec => {
            sec.classList.toggle('active', sec.id === sectionId);
        });

        state.activeSection = sectionId;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // --- CHANGE DETECTION ---
    function handleInputChange(e) {
        const input = e.target;
        const id = input.id || input.name;
        if (!id) return;

        const val = input.type === 'checkbox' ? input.checked : input.value;
        state.currentValues[id] = val;
        
        calculateDiff();
    }

    function calculateDiff() {
        let diffCount = 0;
        
        // Check text/checkbox fields
        for (const key in state.initialValues) {
            if (state.initialValues[key] !== state.currentValues[key]) {
                diffCount++;
            }
        }

        // Check file fields (anything in currentValues that isn't in initialValues)
        for (const key in state.currentValues) {
            if (state.currentValues[key] instanceof File) {
                diffCount++;
            }
        }

        state.hasChanges = diffCount > 0;
        updateUIFeedback(diffCount);
    }

    function updateUIFeedback(count) {
        if (count > 0) {
            if (floatingBar) floatingBar.style.display = 'block';
            unsavedChips.forEach(chip => chip.style.display = 'flex');
            countLabels.forEach(label => label.textContent = count);
        } else {
            if (floatingBar) floatingBar.style.display = 'none';
            unsavedChips.forEach(chip => chip.style.display = 'none');
            countLabels.forEach(label => label.textContent = '0');
        }
    }

    // --- SECURITY LOGIC ---
    function updatePasswordStrength(val) {
        if (!val) {
            strengthBar.style.width = '0%';
            strengthLabel.textContent = '';
            return;
        }

        let strength = 0;
        if (val.length >= 8) strength += 25;
        if (/[A-Z]/.test(val)) strength += 25;
        if (/[0-9]/.test(val)) strength += 25;
        if (/[^A-Za-z0-9]/.test(val)) strength += 25;

        strengthBar.style.width = strength + '%';
        strengthBar.className = 'strength-bar-fill';

        if (strength <= 25) {
            strengthBar.classList.add('weak');
            strengthLabel.textContent = 'Weak password';
            strengthLabel.className = 'strength-label text-danger fw-bold small';
        } else if (strength <= 75) {
            strengthBar.classList.add('medium');
            strengthLabel.textContent = 'Fairly secure';
            strengthLabel.className = 'strength-label text-warning fw-bold small';
        } else {
            strengthBar.classList.add('strong');
            strengthLabel.textContent = 'Very strong password';
            strengthLabel.className = 'strength-label text-success fw-bold small';
        }
    }

    // --- SAVE & RESET ACTIONS ---
    function saveSettings() {
        const btn = this;
        const originalHtml = btn.innerHTML;
        
        // Find all changes including files
        const formData = new FormData();
        let changeFound = false;

        // 1. Check tracked inputs
        for (const key in state.initialValues) {
            if (state.initialValues[key] !== state.currentValues[key]) {
                const val = state.currentValues[key];
                // Convert boolean to 1/0 for PHP
                formData.append(key, typeof val === 'boolean' ? (val ? '1' : '0') : val);
                changeFound = true;
            }
        }

        // 2. Check for files
        for (const key in state.currentValues) {
            if (state.currentValues[key] instanceof File) {
                formData.append(key, state.currentValues[key]);
                changeFound = true;
            }
        }

        if (!changeFound) {
            showToast('No changes detected.', 'info');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';

        // Real API Call
        fetch('api/v1/settings.php', {
            method: 'POST',
            body: formData // Use FormData for multipart support
        })
        .then(async response => {
            const isJson = response.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await response.json() : null;

            if (!response.ok || (data && !data.success)) {
                throw new Error(data?.message || `Server error: ${response.status}`);
            }
            return data;
        })
        .then(data => {
            if (data && data.success) {
                btn.innerHTML = '<i class="bi bi-check-lg me-2"></i> Saved';
                btn.classList.replace('btn-save-maroon', 'btn-success');
                
                showToast(data.message || 'Changes saved successfully!', 'success');

                // Commit changes to initial state
                // Deep copy current values (excluding files which are handled by server)
                for (const key in state.currentValues) {
                    if (!(state.currentValues[key] instanceof File)) {
                        state.initialValues[key] = state.currentValues[key];
                    } else {
                        // After upload, the file is no longer "new" in the UI
                        delete state.currentValues[key];
                    }
                }
                
                state.hasChanges = false;
                updateUIFeedback(0);

                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    btn.classList.replace('btn-success', 'btn-save-maroon');
                }, 2000);
            } else {
                throw new Error(data.message || 'Saving failed.');
            }
        })
        .catch(error => {
            console.error('Error saving settings:', error);
            showToast(error.message || 'An error occurred while saving.', 'error');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    }

    function resetSettings() {
        if (confirm('Discard all unsaved changes?')) {
            inputs.forEach(input => {
                const id = input.id || input.name;
                if (!id) return;

                if (input.type === 'checkbox') {
                    input.checked = state.initialValues[id];
                } else {
                    input.value = state.initialValues[id];
                }
                state.currentValues[id] = state.initialValues[id];
            });

            // Clear file references
            for (const key in state.currentValues) {
                if (state.currentValues[key] instanceof File) {
                    delete state.currentValues[key];
                }
            }

            updateUIFeedback(0);
        }
    }

    function showToast(msg, type = 'success') {
        const toastContainer = document.getElementById('settingsToastContainer');
        const target = toastContainer || document.body;

        const toast = document.createElement('div');
        toast.className = `alert shadow border-0 py-3 px-4 mb-2`;
        
        let bgColor = '#1f9c43'; // Success
        if (type === 'error') bgColor = '#dc2626';
        if (type === 'info') bgColor = '#0ea5e9'; // Blue for info

        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            border-radius: 12px;
            animation: toastIn 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            background: ${bgColor};
        `;
        
        let icon = 'check-circle';
        if (type === 'error') icon = 'exclamation-circle';
        if (type === 'info') icon = 'info-circle';

        toast.innerHTML = `
            <i class="bi bi-${icon} fs-5"></i>
            <div class="fw-bold">${msg}</div>
        `;

        target.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px)';
            toast.style.transition = '0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Export globally for inline HTML onclick handlers
    window.showToast = showToast;

    // Add Animation
    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }
    `;
    document.head.appendChild(style);

    // Start
    init();

});
