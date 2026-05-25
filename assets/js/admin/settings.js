/**
 * Sweets Website
 * =============================================================
 * File: settings.js
 * Description: State management and UI logic for the Settings page.
 * Includes unsaved changes detection and section switching.
 * =============================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    // --- State Variables ---
    let hasUnsavedChanges = false;
    let originalValues = {};
    
    const formFields = [
        'storeName', 'tagline', 'emailAddress', 'phoneNumber', 
        'storeAddress', 'gstNumber', 'businessType'
    ];

    // --- Initial Execution ---
    storeOriginalValues();
    setupEventListeners();

    function storeOriginalValues() {
        formFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) originalValues[id] = el.value;
        });
    }

    function setupEventListeners() {
        formFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', markUnsaved);
            }
        });

        // Logo Upload
        const logoUpload = document.getElementById('logoUpload');
        if (logoUpload) {
            logoUpload.addEventListener('change', function() {
                handleLogoUpload(this);
            });
        }
    }

    // --- Unsaved Changes Detection ---
    function markUnsaved() {
        let changedCount = 0;
        
        formFields.forEach(id => {
            const el = document.getElementById(id);
            if (el && el.value !== originalValues[id]) {
                changedCount++;
            }
        });

        hasUnsavedChanges = (changedCount > 0);
        
        const unsavedDesc = document.querySelector('.unsaved-alert-desc');
        const footerUnsaved = document.querySelector('.footer-unsaved-desc');
        
        const message = changedCount + ' field' + (changedCount !== 1 ? 's' : '') + ' updated since last publish';
        
        if (unsavedDesc) unsavedDesc.textContent = message;
        if (footerUnsaved) footerUnsaved.textContent = message;

        // Toggle visibility of unsaved alerts if needed
        const unsavedBadge = document.querySelector('.unsaved-alert-badge');
        if (unsavedBadge) {
            unsavedBadge.style.opacity = hasUnsavedChanges ? '1' : '0.5';
        }
    }

    // --- Public Functions (Attached to window for inline HTML calls) ---
    
    window.switchSettingsSection = function(element, event) {
        event.preventDefault();
        
        // Navigation UI
        document.querySelectorAll('.settings-nav-link').forEach(link => {
            link.classList.remove('active');
        });
        element.classList.add('active');

        // Update selected section badge
        const sectionName = element.querySelector('.nav-settings-label').textContent;
        const selectionBadge = document.querySelector('.section-selection-badge');
        if (selectionBadge) {
            selectionBadge.innerHTML = '<i class="bi bi-gear-wide-connected"></i> Selected section: ' + sectionName;
        }

        showSettingsToast('Navigated to ' + sectionName);
    };

    window.saveSettings = function() {
        const storeName = document.getElementById('storeName').value.trim();
        if (!storeName) {
            showSettingsToast('Store Name is required!', true);
            document.getElementById('storeName').focus();
            return;
        }

        // Simulate Save
        const saveBtns = document.querySelectorAll('.btn-settings-save');
        saveBtns.forEach(btn => {
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
            btn.disabled = true;
            
            setTimeout(() => {
                hasUnsavedChanges = false;
                storeOriginalValues();
                markUnsaved(); // Reset display
                
                btn.innerHTML = originalText;
                btn.disabled = false;
                showSettingsToast('Changes saved successfully!');
            }, 1000);
        });
    };

    window.cancelSettings = function() {
        formFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = originalValues[id];
        });

        hasUnsavedChanges = false;
        markUnsaved();
        showSettingsToast('Changes discarded');
    };

    function handleLogoUpload(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const logoBox = document.querySelector('.logo-display-box');
                if (logoBox) {
                    logoBox.innerHTML = '<img src="' + e.target.result + '" alt="Logo Preview">';
                }
                showSettingsToast('Logo uploaded successfully!');
                hasUnsavedChanges = true;
                markUnsaved();
            };
            reader.readAsDataURL(file);
        }
    }

    window.removeSettingsLogo = function() {
        const logoBox = document.querySelector('.logo-display-box');
        if (logoBox) {
            logoBox.innerHTML = '<i class="bi bi-box-seam-fill"></i>';
        }
        document.getElementById('logoUpload').value = '';
        showSettingsToast('Logo removed');
        hasUnsavedChanges = true;
        markUnsaved();
    };

    function showSettingsToast(message, isError = false) {
        // Use a simple notification or toast
        const toast = document.createElement('div');
        toast.className = 'settings-toast ' + (isError ? 'error' : 'success');
        toast.innerHTML = `
            <div style="position: fixed; bottom: 30px; right: 30px; background: ${isError ? '#C0392B' : '#6B2F0A'}; 
                        color: white; padding: 12px 24px; border-radius: 10px; z-index: 9999; 
                        box-shadow: 0 5px 15px rgba(0,0,0,0.2); animation: slideUp 0.3s ease;">
                <i class="bi bi-${isError ? 'exclamation-circle' : 'check-circle'} me-2"></i>
                ${message}
            </div>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.5s ease';
            setTimeout(() => document.body.removeChild(toast), 500);
        }, 3000);
    }

    // --- Warn on Leave ---
    window.addEventListener('beforeunload', function(e) {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});
