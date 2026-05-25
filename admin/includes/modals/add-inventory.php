<!-- Modal -->
<div class="modal fade" id="addInventoryModal" tabindex="-1" aria-labelledby="addInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="addInventoryModalLabel">Add Inventory</h5>
                <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <form id="inventoryForm">
                    <!-- Product Field -->
                    <div class="mb-3">
                        <label for="product" class="form-label">Product</label>
                        <select class="form-select" id="product" required>
                            <option value="" selected disabled>Select Product*</option>
                            <?php foreach ($inventoryItems as $item): ?>
                                <option value="<?php echo htmlspecialchars($item['id']); ?>"
                                    data-sku="<?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?>"
                                    data-category="<?php echo htmlspecialchars($item['category'] ?? ''); ?>">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Category Field (auto-filled from selected product) -->
                    <div class="mb-3">
                        <label for="categoryDisplay" class="form-label">Category</label>
                        <input type="text" class="form-control" id="categoryDisplay" placeholder="Auto-filled on product selection" readonly>
                    </div>

                    <!-- SKU and Stock Quantity Row -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="sku" class="form-label">SKU</label>
                            <input type="text" class="form-control" id="sku" placeholder="SKU | (Auto-filled)" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="stockQuantity" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" id="stockQuantity" placeholder="Add Quantity" min="1" required>
                        </div>
                    </div>

                    <!-- Status Toggle -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="status" checked>
                            <label class="form-check-label" for="status">Status</label>
                        </div>
                    </div>

                    <!-- Last Update -->
                    <div class="last-update-section mb-3">
                        <i class="bi bi-clock"></i>
                        <span>Last Update</span>
                    </div>

                    <!-- Notes Field -->
                    <div class="mb-0">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" rows="3" placeholder="Add notes"></textarea>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer justify-content-end">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-save" onclick="saveInventory()">Save Inventory</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Form validation and save function
    function saveInventory() {
        const form = document.getElementById('inventoryForm');
        const productId = document.getElementById('product').value;
        const stockQuantity = document.getElementById('stockQuantity').value;
        const notes = document.getElementById('notes').value;

        // Basic validation
        if (!productId || !stockQuantity || stockQuantity <= 0) {
            alert('Please select a product and enter a positive quantity.');
            return;
        }

        const btn = document.querySelector('.btn-save');
        btn.disabled = true;
        const originalBtnText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        // Create data object for the API
        const data = {
            product_id: parseInt(productId),
            quantity: parseInt(stockQuantity),
            action: 'add',
            notes: notes
        };

        fetch('api/v1/inventory.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalBtnText;

            if (data.status === 'success') {
                const updateUI = () => {
                    // Update summary cards
                    if (data.data && data.data.stats) {
                        const stats = data.data.stats;
                        const ids = {
                            'stat-total-products': stats.total,
                            'stat-in-stock': stats.in_stock,
                            'stat-low-stock': stats.low_stock,
                            'stat-out-of-stock': stats.out_of_stock
                        };
                        for (const [id, val] of Object.entries(ids)) {
                            const el = document.getElementById(id);
                            if (el) el.textContent = new Intl.NumberFormat().format(val);
                        }
                    }

                    // Find and update the row in the table
                    const row = document.querySelector(`.product-row[data-id="${productId}"]`);
                    if (row) {
                        // Update stock value in input if it's a base product
                        const qtyInput = row.querySelector('.qty-input');
                        if (qtyInput) {
                            qtyInput.value = data.data.stock;
                        }

                        // Update status pill
                        const statusPill = row.querySelector('.products-status-pill');
                        if (statusPill && data.data.status_label) {
                            statusPill.textContent = data.data.status_label;
                            statusPill.classList.remove('products-status-in', 'products-status-low', 'products-status-out');
                            statusPill.classList.add(data.data.status_class);
                            row.setAttribute('data-status', data.data.status_label);
                        }

                        // If it has variants, we might need a refresh or more complex logic, 
                        // but for now, let's just update the total stock if it's visible
                        const totalStockEl = row.querySelector('.td-stock .fw-bold');
                        if (totalStockEl && data.data.stock !== undefined) {
                            totalStockEl.textContent = data.data.stock;
                        }
                    }
                    
                    // Close modal
                    const modalEl = document.getElementById('addInventoryModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(data.message);
                    }
                };

                updateUI();
            } else {
                alert(data.message || 'Failed to update inventory');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalBtnText;
            console.error(err);
            alert('Network error updating inventory.');
        });
    }

    // Auto-fill SKU and Category when product is selected
    document.getElementById('product').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const skuField = document.getElementById('sku');
        const categoryField = document.getElementById('categoryDisplay');
        if (selectedOption) {
            skuField.value = selectedOption.dataset.sku || '';
            if (categoryField) {
                categoryField.value = selectedOption.dataset.category || '';
            }
        } else {
            skuField.value = '';
            if (categoryField) categoryField.value = '';
        }
    });

    // Modal event listeners
    const addInvModal = document.getElementById('addInventoryModal');
    if (addInvModal) {
        addInvModal.addEventListener('shown.bs.modal', function () {
            document.getElementById('product').focus();
        });

        addInvModal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('inventoryForm').reset();
        });
    }
</script>
