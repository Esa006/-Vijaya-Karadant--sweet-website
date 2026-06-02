/**
 * Sweets Website - Razorpay Integration
 * =============================================================
 * Handles the checkout flow:
 * 1. Request Order Creation from Backend
 * 2. Launch Razorpay Checkout
 * 3. Verify Payment on Backend
 * =============================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    const payBtn = document.getElementById('payBtn');
    const methodCards = document.querySelectorAll('.c-method-card');
    const methodInput = document.getElementById('selectedPaymentMethod');
    const form = document.querySelector('.needs-validation');

    if (!payBtn) return;

    // ── Payment Method Selection UI ──────────────────────────────
    methodCards.forEach(card => {
        card.addEventListener('click', () => {
            methodCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            const method = card.getAttribute('data-method');
            methodInput.value = method;

            // Update button label
            payBtn.innerHTML = method === 'cod'
                ? 'Place Order (Cash on Delivery)'
                : 'Pay Now';
        });
    });

    payBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        
        // 1. Validation Check: Ensure address form is complete
        if (form) {
            // Trigger browser's native validation UI
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                // Scroll to the first error
                const firstError = form.querySelector(':invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
                return; // Stop here! Don't open Razorpay
            }
        }

            const selectedMethod = methodInput.value;

        const checkoutForm = document.querySelector('form.needs-validation');
        const fd = new FormData(checkoutForm);
        const checkoutFormData = Object.fromEntries(fd.entries());
        
        // Ensure default country if missing
        if (!checkoutFormData.country) checkoutFormData.country = 'India';
        
        // Basic UI feedback
        const originalText = payBtn.innerHTML;
        payBtn.disabled = true;
        payBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

        try {
            // Handle COD separately
            if (selectedMethod === 'cod') {
                try {
                    const codRes = await fetch('api/checkout/place-cod-order.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ checkout_data: checkoutFormData })
                    });
                    const codData = await codRes.json();

                    if (codData.success) {
                        window.location.href = `order-success.php?order_id=${encodeURIComponent(codData.order_id)}`;
                    } else {
                        throw new Error(codData.message || 'Failed to place COD order');
                    }
                } catch (err) {
                    alert('Error: ' + err.message);
                    resetBtn();
                }
                return;
            }

            // 1. Create Order on Server
            const orderRes = await fetch('api/razorpay/create-order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ checkout_data: checkoutFormData })
            });
            const orderText = await orderRes.text();
            let orderData = {};
            try {
                orderData = JSON.parse(orderText);
            } catch (parseError) {
                console.error('Create order returned non-JSON response:', orderText);
                throw new Error('Payment order request failed. Check PHP error log for details.');
            }

            if (!orderData.success) {
                throw new Error(orderData.message || 'Failed to initialize payment');
            }

            // Handle UPI — open our custom QR modal
            if (selectedMethod === 'upi') {
                resetBtn();
                const amount = window._upiAmount || 0;
                UpiPayment.initiate(amount, orderData.db_order_id, checkoutFormData);
                return;
            }

            // 2. Razorpay Options
            const options = {
                "key": orderData.key,
                "amount": orderData.amount,
                "currency": "INR",
                "name": "Sweets Website",
                "description": "Premium Traditional Sweets",
                "order_id": orderData.order_id,
                "handler": async function (response) {
                    // 3. Verify Payment on Server
                    try {
                        console.log('Verifying payment with data:', { ...response, checkout_data: checkoutFormData });
                        const verifyRes = await fetch('api/razorpay/verify-payment.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ ...response, checkout_data: checkoutFormData })
                        });
                        const verifyData = await verifyRes.json();

                        if (verifyData.success) {
                            window.location.href = `order-success.php?order_id=${encodeURIComponent(verifyData.order_id)}`;
                        } else {
                            alert('Payment verification failed: ' + verifyData.message);
                            resetBtn();
                        }
                    } catch (err) {
                        alert('Something went wrong during verification');
                        resetBtn();
                    }
                },
                "prefill": {
                    "name": document.querySelector('[name="first_name"]')?.value + ' ' + document.querySelector('[name="last_name"]')?.value,
                    "email": document.querySelector('[name="email"]')?.value,
                    "contact": document.querySelector('[name="phone"]')?.value,
                    "method": "upi"
                },
                "config": {
                    "display": {
                        "blocks": {
                            "upi": {
                                "name": "Pay using UPI",
                                "instruments": [
                                    {
                                        "method": "upi"
                                    }
                                ]
                            }
                        },
                        "sequence": ["block.upi", "block.card", "block.netbanking"],
                        "preferences": {
                            "show_default_blocks": true
                        }
                    }
                },
                "theme": {
                    "color": "#7b1d1d"
                },
                // Force QR code to show up immediately if UPI is selected
                "retry": {
                    "enabled": true,
                    "max_count": 1
                },
                "modal": {
                    "confirm_close": true,
                    "ondismiss": function() {
                        resetBtn();
                    }
                }
            };

            const rzp = new Razorpay(options);
            rzp.open();

        } catch (error) {
            console.error('Payment error:', error);
            alert('Error: ' + error.message);
            resetBtn();
        }

        function resetBtn() {
            payBtn.disabled = false;
            payBtn.innerHTML = originalText;
        }
    });
});
