/**
 * Sweets Website — UPI QR Payment Module
 * ============================================================
 * Handles the entire UPI in-page payment flow:
 *   initiate → QR display → polling → success/failed/timeout
 * ============================================================
 */

const UpiPayment = (() => {
    /* ── State ─────────────────────────────────────────────── */
    let state = {
        txnId: null,
        amount: 0,
        expiresIn: 300,
        pollInterval: null,
        countdownInterval: null,
        pollCount: 0,
        checkoutData: {},
        shopQrUrl: '',
    };

    const API = `${window.BASE_URL}api/upi/upi-payment.php`;

    /* ── DOM refs (lazy) ────────────────────────────────────── */
    const $ = id => document.getElementById(id);

    /* ── Screen switcher ────────────────────────────────────── */
    function showScreen(name) {
        ['upi-scanning', 'upi-pending', 'upi-success', 'upi-failed', 'upi-expired'].forEach(s => {
            const el = $(s);
            if (el) el.style.display = 'none';
        });
        const target = $(name);
        if (target) target.style.display = 'block';
    }

    /* ── QR code renderer ───────────────────────────────────── */
    /**
     * Render QR: if staticUrl is provided (admin-uploaded image), show that.
     * Otherwise fall back to qrcode.js / Google Charts.
     */
    function renderQR(uri, containerId, staticUrl) {
        const el = $(containerId);
        if (!el) return;
        el.innerHTML = '';

        if (staticUrl) {
            // Show the admin-configured shop QR image
            el.innerHTML = `<img src="${staticUrl}"
                style="width:200px;height:200px;object-fit:contain;border-radius:10px;display:block;"
                alt="UPI QR Code">`;
            return;
        }

        // Dynamic fallback
        if (window.QRCode) {
            new QRCode(el, {
                text: uri,
                width: 200,
                height: 200,
                colorDark: '#1a1a1a',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H,
            });
        } else {
            // Fallback: Google Charts QR
            el.innerHTML = `<img src="https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=${encodeURIComponent(uri)}&choe=UTF-8" width="200" height="200" alt="UPI QR Code" style="border-radius:8px;">`;
        }
    }

    /* ── Countdown timer ────────────────────────────────────── */
    function startCountdown(seconds) {
        stopCountdown();
        let remaining = seconds;
        const updateDisplay = () => {
            const m = String(Math.floor(remaining / 60)).padStart(2, '0');
            const s = String(remaining % 60).padStart(2, '0');
            const el = $('upi-timer');
            if (el) {
                el.textContent = `${m}:${s}`;
                el.style.color = remaining <= 30 ? '#dc3545' : '#7a1f1f';
            }
            // Progress ring
            const ring = $('upi-timer-ring');
            if (ring) {
                const pct = (remaining / state.expiresIn) * 283;
                ring.style.strokeDashoffset = 283 - pct;
            }
        };
        updateDisplay();
        state.countdownInterval = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                stopCountdown();
                stopPolling();
                onExpired();
                return;
            }
            updateDisplay();
        }, 1000);
    }

    function stopCountdown() {
        if (state.countdownInterval) { clearInterval(state.countdownInterval); state.countdownInterval = null; }
    }

    /* ── Polling ────────────────────────────────────────────── */
    function startPolling() {
        stopPolling();
        state.pollInterval = setInterval(async () => {
            state.pollCount++;
            try {
                const res  = await fetch(`${API}?action=poll`);
                const data = await res.json();
                if (!data.success) return;

                if (data.status === 'success') {
                    stopPolling(); stopCountdown();
                    onSuccess(data.txn_id);
                } else if (data.status === 'failed') {
                    stopPolling(); stopCountdown();
                    onFailed(data.failure_reason || 'Payment was not completed.');
                } else if (data.status === 'expired') {
                    stopPolling(); stopCountdown();
                    onExpired();
                } else if (state.pollCount > 3 && state.pollCount < 6) {
                    // Show pending animation after 3 polls
                    showScreen('upi-pending');
                }
            } catch (_) {}
        }, 3000);
    }

    function stopPolling() {
        if (state.pollInterval) { clearInterval(state.pollInterval); state.pollInterval = null; }
    }

    /* ── Outcome handlers ───────────────────────────────────── */
    function onSuccess(txnId) {
        showScreen('upi-success');
        const el = $('upi-success-txn');
        if (el) el.textContent = txnId || state.txnId;
        // Confetti burst
        if (window.confetti) window.confetti({ particleCount: 120, spread: 80, origin: { y: 0.55 } });
        setTimeout(() => {
            const orderId = $('upiPaymentModal')?.dataset?.orderId;
            if (orderId) window.location.href = `order-success.php?order_id=${encodeURIComponent(orderId)}`;
        }, 4000);
    }

    function onFailed(reason) {
        showScreen('upi-failed');
        const el = $('upi-failed-reason');
        if (el) el.textContent = reason || 'Payment was not completed.';
    }

    function onExpired() {
        showScreen('upi-expired');
    }

    /* ── Public: Initiate ───────────────────────────────────── */
    async function initiate(amount, orderId, checkoutData) {
        state.checkoutData = checkoutData;
        state.pollCount    = 0;

        const modal = $('upiPaymentModal');
        if (!modal) return;

        // Set display values
        const amtEls = document.querySelectorAll('.upi-amount-display');
        amtEls.forEach(el => el.textContent = '₹' + parseFloat(amount).toLocaleString('en-IN'));

        showScreen('upi-scanning');
        if (window.bootstrap) new bootstrap.Modal(modal).show();

        try {
            const res = await fetch(API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'initiate', amount, order_id: orderId }),
            });
            let data;
            try {
                data = await res.json();
            } catch (err) {
                const text = await res.text();
                throw new Error('Server returned invalid JSON. Raw response: ' + text.substring(0, 100));
            }
            if (!data.success) throw new Error(data.message);

            state.txnId     = data.txn_id;
            state.expiresIn = data.expires_in;
            state.shopQrUrl = data.shop_qr_url || '';

            // Render QR — use static image if available, else generate dynamically
            renderQR(data.upi_uri, 'upi-qr-container', state.shopQrUrl);

            // Show UPI ID and amount
            const upiIdEl = $('upi-display-id');
            if (upiIdEl) upiIdEl.textContent = data.upi_id;

            // Store orderId on modal for redirect
            modal.dataset.orderId = orderId;

            startCountdown(data.expires_in);
            startPolling();

        } catch (err) {
            if (window.bootstrap) bootstrap.Modal.getInstance(modal)?.hide();
            alert('Failed to initiate UPI payment: ' + err.message);
        }
    }

    /* ── Public: Force outcome (demo controls) ──────────────── */
    async function force(outcome) {
        await fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'force', outcome }),
        });
    }

    /* ── Public: Regenerate QR ──────────────────────────────── */
    async function regenerate() {
        const res  = await fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'regenerate' }),
        });
        const data = await res.json();
        if (!data.success) return;
        state.txnId     = data.txn_id;
        state.expiresIn = data.expires_in;
        renderQR(data.upi_uri, 'upi-qr-container', state.shopQrUrl || '');
        showScreen('upi-scanning');
        startCountdown(data.expires_in);
        startPolling();
    }

    /* ── Public API ─────────────────────────────────────────── */
    return { initiate, force, regenerate };
})();
