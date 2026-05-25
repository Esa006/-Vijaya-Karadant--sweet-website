/**
 * Sweets Website
 * =============================================================
 * File: box-builder.js
 * Description: Interactive logic for Dynamic Box Builder
 * Author: Sweets Website Team
 * Version: 1.0.0
 * =============================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    // ── Configuration ──
    const CONFIG = {
        6: { name: 'Classic Box', slots: 6, price: 599 },
        12: { name: 'Grand Box', slots: 12, price: 1199 },
        24: { name: 'Royal Box', slots: 24, price: 2299 }
    };

    // ── State ──
    let state = {
        currentStep: 1,
        boxSize: null,
        slots: 0,
        items: [] // Array of product objects
    };

    // ── DOM Elements ──
    const steps = {
        1: document.getElementById('step-1'),
        2: document.getElementById('step-2'),
        3: document.getElementById('step-3')
    };

    const progressSteps = document.querySelectorAll('.builder-progress__step');
    const visualBox = document.getElementById('visual-box');
    const capacityFill = document.getElementById('capacity-fill');
    const capacityText = document.getElementById('capacity-text');
    const totalPriceDisplay = document.getElementById('total-price');
    const nextBtn = document.getElementById('next-btn');
    const prevBtn = document.getElementById('prev-btn');

    // ── Initialization ──
    init();

    function init() {
        // Box Size Selection
        document.querySelectorAll('.box-size-card').forEach(card => {
            card.addEventListener('click', () => {
                const size = parseInt(card.dataset.size);
                selectBoxSize(size);
                
                // Visual feedback
                document.querySelectorAll('.box-size-card').forEach(c => c.classList.remove('box-size-card--selected'));
                card.classList.add('box-size-card--selected');
                
                nextStep();
            });
        });

        // Item Selection
        document.querySelectorAll('.item-pick-card').forEach(card => {
            card.addEventListener('click', () => {
                const item = {
                    id: card.dataset.id,
                    name: card.dataset.name,
                    image: card.dataset.image,
                    price: parseFloat(card.dataset.price)
                };
                addItem(item);
            });
        });

        // Navigation
        nextBtn.addEventListener('click', nextStep);
        prevBtn.addEventListener('click', prevStep);
    }

    // ── Core Functions ──

    function selectBoxSize(size) {
        state.boxSize = size;
        state.slots = CONFIG[size].slots;
        state.items = [];
        
        // Setup Visual Box Grid
        renderVisualBox();
        updateUI();
    }

    function renderVisualBox() {
        if (!visualBox) return;
        
        visualBox.innerHTML = '';
        visualBox.className = `visual-box visual-box--${state.boxSize}`;
        
        for (let i = 0; i < state.slots; i++) {
            const slot = document.createElement('div');
            slot.className = 'box-slot';
            slot.dataset.index = i;
            
            if (state.items[i]) {
                fillSlot(slot, state.items[i], i);
            } else {
                slot.innerHTML = '<i class="bi bi-plus-circle opacity-25"></i>';
            }
            
            visualBox.appendChild(slot);
        }
    }

    function addItem(item) {
        if (state.items.length >= state.slots) {
            showToast('Box is full!', 'error');
            return;
        }

        state.items.push(item);
        renderVisualBox();
        updateUI();
        
        // Animate the added item
        const lastSlot = visualBox.querySelector(`.box-slot[data-index="${state.items.length - 1}"]`);
        if (lastSlot) lastSlot.classList.add('box-slot--filled');
    }

    function removeItem(index) {
        state.items.splice(index, 1);
        renderVisualBox();
        updateUI();
    }

    function fillSlot(slot, item, index) {
        slot.classList.add('box-slot--filled');
        slot.innerHTML = `
            <img src="${item.image}" alt="${item.name}">
            <button class="box-slot__remove" title="Remove item">
                <i class="bi bi-x"></i>
            </button>
        `;
        
        slot.querySelector('.box-slot__remove').addEventListener('click', (e) => {
            e.stopPropagation();
            removeItem(index);
        });
    }

    function updateUI() {
        const count = state.items.length;
        const pct = (count / state.slots) * 100;
        
        if (capacityFill) capacityFill.style.width = `${pct}%`;
        if (capacityText) capacityText.innerText = `${count} / ${state.slots} items filled`;
        
        if (totalPriceDisplay) {
            const price = CONFIG[state.boxSize] ? CONFIG[state.boxSize].price : 0;
            totalPriceDisplay.innerText = `₹ ${price.toLocaleString()}`;
        }

        // Enable/Disable next button
        if (state.currentStep === 2) {
            nextBtn.disabled = count === 0;
        }
    }

    // ── Navigation Logic ──

    function nextStep() {
        if (state.currentStep === 1 && !state.boxSize) return;
        if (state.currentStep === 2 && state.items.length === 0) return;
        
        if (state.currentStep < 3) {
            goToStep(state.currentStep + 1);
        } else {
            addToCart();
        }
    }

    function prevStep() {
        if (state.currentStep > 1) {
            goToStep(state.currentStep - 1);
        }
    }

    function goToStep(step) {
        state.currentStep = step;
        
        // Hide all steps
        Object.values(steps).forEach(s => s.classList.add('d-none'));
        // Show current
        steps[step].classList.remove('d-none');
        
        // Update Progress Bar
        progressSteps.forEach((s, idx) => {
            if (idx + 1 <= step) s.classList.add('builder-progress__step--active');
            else s.classList.remove('builder-progress__step--active');
        });

        // Update footer buttons
        prevBtn.classList.toggle('d-none', step === 1);
        nextBtn.innerText = step === 3 ? 'Add Box to Cart' : 'Next Step';
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ── Final Action ──

    function addToCart() {
        const personalNote = document.getElementById('personal-note').value;
        const data = {
            action: 'add_custom_box',
            box_size: state.boxSize,
            items: state.items.map(i => i.id),
            note: personalNote,
            price: CONFIG[state.boxSize].price
        };

        // Send via AJAX to cart handler
        fetch('cart-ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                window.location.href = 'shopping-cart.php';
            } else {
                showToast(res.message || 'Error adding to cart', 'error');
            }
        });
    }

    function showToast(msg, type = 'success') {
        // Simple alert for now, can be replaced with premium toast
        alert(msg);
    }
});
