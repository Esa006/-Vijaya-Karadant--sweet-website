/**
 * Sweets Website - Checkout Interactivity
 */

    // Handle Payment Method Card Selection
    const paymentCards = document.querySelectorAll('.c-payment-card');
    paymentCards.forEach(card => {
        card.addEventListener('click', () => {
            paymentCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
        });
    });

    // Handle Delivery Method Selection (Placeholder logic)
    const deliveryCards = document.querySelectorAll('.c-delivery-card');
    deliveryCards.forEach(card => {
        card.addEventListener('click', () => {
            deliveryCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
        });
    });

    // Simple Totals Mock Logic
    function updateTotals() {
        const subtotal = 2070;
        const discount = 100;
        const delivery = 50;
        const total = subtotal + delivery - discount;

        const deliveryEl = document.querySelector('.c-checkout-totals div:nth-child(2) span:nth-child(2)');
        const totalEl = document.querySelector('.c-checkout-totals div:nth-child(5) span:nth-child(2)');

        if (deliveryEl) deliveryEl.textContent = `₹${delivery}`;
        if (totalEl) totalEl.textContent = `₹${total.toLocaleString()}`;
    }

    // Form Submission
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const placeOrderBtn = document.querySelector('.c-checkout__place-order');
            
            // Visual feedback
            placeOrderBtn.disabled = true;
            placeOrderBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

            setTimeout(() => {
                // Show success overlay
                const overlay = document.createElement('div');
                overlay.className = 'c-success-overlay';
                overlay.innerHTML = `
                    <div class="c-success-card">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <h2 class="mb-3">Order Placed!</h2>
                        <p class="text-muted">Thank you for your order. We are preparing your sweets with love. Redirecting you home...</p>
                    </div>
                `;
                document.body.appendChild(overlay);

                setTimeout(() => {
                    location.href = 'index.php';
                }, 3000);
            }, 2000);
        });
    }
});
