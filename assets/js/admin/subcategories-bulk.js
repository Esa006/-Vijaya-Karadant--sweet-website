/**
 * Sweets Website - Admin
 * =============================================================
 * File: assets/js/admin/subcategories-bulk.js
 * Description: High-performance Bulk Update logic for Subcategories
 * Author: Antigravity - Senior Full-Stack Architect
 * Version: 3.0.0
 * =============================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    const BulkEngine = {
        elements: {
            selectAll: document.getElementById('selectAllSubcats'),
            rowCheckboxes: document.querySelectorAll('.products-row-checkbox'),
            bulkBtn: document.querySelector('.products-bulk-btn'),
            modal: new bootstrap.Modal(document.getElementById('subcategoryBulkModal')),
            form: document.getElementById('subcategoryBulkForm'),
            actionType: document.getElementById('bulkActionType'),
            submitBtn: document.getElementById('btnSubmitBulk'),
            countText: document.getElementById('selectedCountText'),
            valueContainer: document.getElementById('bulkValueContainer'),
            wrappers: {
                status: document.getElementById('statusValueWrapper'),
                category: document.getElementById('categoryValueWrapper'),
                delete: document.getElementById('deleteValueWrapper')
            }
        },

        state: {
            selectedIds: [],
            loading: false
        },

        init() {
            if (!this.elements.selectAll) return;
            this.bindEvents();
            this.updateBtnState();
        },

        bindEvents() {
            // 1. Select All Logic
            this.elements.selectAll.addEventListener('change', (e) => {
                const isChecked = e.target.checked;
                document.querySelectorAll('.products-row-checkbox:not(#selectAllSubcats)').forEach(cb => {
                    cb.checked = isChecked;
                });
                this.updateSelectedIds();
            });

            // 2. Individual Checkbox Logic
            document.querySelectorAll('.products-row-checkbox:not(#selectAllSubcats)').forEach(cb => {
                cb.addEventListener('change', () => {
                    this.updateSelectedIds();
                    this.updateSelectAllState();
                });
            });

            // 3. Bulk Button Click
            this.elements.bulkBtn.addEventListener('click', () => {
                if (this.state.selectedIds.length > 0) {
                    this.elements.countText.textContent = `${this.state.selectedIds.length} items selected`;
                    this.elements.modal.show();
                }
            });

            // 4. Action Type Change
            this.elements.actionType.addEventListener('change', (e) => {
                this.toggleActionValue(e.target.value);
            });

            // 5. Submit Logic
            this.elements.submitBtn.addEventListener('click', () => this.handleSubmit());
        },

        updateSelectedIds() {
            this.state.selectedIds = Array.from(document.querySelectorAll('.products-row-checkbox:not(#selectAllSubcats):checked'))
                .map(cb => cb.value);
            this.updateBtnState();
        },

        updateSelectAllState() {
            const allCbs = document.querySelectorAll('.products-row-checkbox:not(#selectAllSubcats)');
            const checkedCbs = document.querySelectorAll('.products-row-checkbox:not(#selectAllSubcats):checked');
            this.elements.selectAll.checked = allCbs.length === checkedCbs.length && allCbs.length > 0;
            this.elements.selectAll.indeterminate = checkedCbs.length > 0 && checkedCbs.length < allCbs.length;
        },

        updateBtnState() {
            if (this.state.selectedIds.length > 0) {
                this.elements.bulkBtn.disabled = false;
                this.elements.bulkBtn.classList.add('btn-primary');
                this.elements.bulkBtn.style.opacity = '1';
            } else {
                this.elements.bulkBtn.disabled = true;
                this.elements.bulkBtn.style.opacity = '0.5';
            }
        },

        toggleActionValue(action) {
            this.elements.valueContainer.classList.toggle('d-none', !action);
            Object.values(this.elements.wrappers).forEach(w => w.classList.add('d-none'));
            this.elements.submitBtn.disabled = !action;

            if (action && this.elements.wrappers[action]) {
                this.elements.wrappers[action].classList.remove('d-none');
            }
        },

        async handleSubmit() {
            const action = this.elements.actionType.value;
            let value = null;

            if (action === 'status') value = document.getElementById('bulkStatusValue').value;
            if (action === 'category') value = document.getElementById('bulkCategoryValue').value;

            this.setLoading(true);

            try {
                const response = await fetch(`${window.BASE_URL}admin/api/v1/subcategory-bulk.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        ids: this.state.selectedIds,
                        action: action,
                        value: value
                    })
                });

                const result = await response.json();

                if (result.success) {
                    this.elements.modal.hide();
                    // Success Feedback
                    const toast = document.createElement('div');
                    toast.className = 'position-fixed bottom-0 end-0 p-3';
                    toast.style.zIndex = '9999';
                    toast.innerHTML = `
                        <div class="alert alert-success border-0 shadow-lg mb-0" style="border-radius: 12px;">
                            <i class="bi bi-check-circle-fill me-2"></i> ${result.message}
                        </div>
                    `;
                    document.body.appendChild(toast);
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    alert('Bulk Update Failed: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Bulk API Error:', error);
                alert('Network error during bulk update');
            } finally {
                this.setLoading(false);
            }
        },

        setLoading(isLoading) {
            this.state.loading = isLoading;
            this.elements.submitBtn.disabled = isLoading;
            this.elements.submitBtn.innerHTML = isLoading 
                ? '<span class="spinner-border spinner-border-sm me-2"></span>Processing...' 
                : 'Apply Changes';
        }
    };

    BulkEngine.init();
});
