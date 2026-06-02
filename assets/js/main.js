/**
 * Sweets Website
 * =============================================================
 * File: main.js
 * Description: Main JavaScript entry point for the project
 * Author: Sweets Website Team
 * Version: 1.0.2
 * =============================================================
 */

let isSweetsWebsiteInitialized = false;

// ── Global Error Tracking ─────────────────────────────
window.addEventListener('error', (event) => {
    if (event.error && event.error.hasBeenReported) return;
    
    const errorData = {
        message: event.message,
        url: event.filename,
        line: event.lineno,
        col: event.colno,
        stack: event.error ? event.error.stack : null
    };

    fetch('api/log-handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(errorData)
    }).catch(err => console.warn('Failed to send error log:', err));
});

window.addEventListener('unhandledrejection', (event) => {
    const errorData = {
        message: 'Unhandled Promise Rejection: ' + (event.reason ? event.reason.message || event.reason : 'No reason'),
        stack: event.reason ? event.reason.stack : null,
        url: window.location.href
    };

    fetch('api/log-handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(errorData)
    }).catch(err => console.warn('Failed to send promise rejection log:', err));
});

const initSweetsWebsite = () => {
    if (isSweetsWebsiteInitialized) {
        console.log('Sweets Website already initialized.');
        return;
    }
    isSweetsWebsiteInitialized = true;
    console.log('Sweets Website Initializing (v1.0.2)...');

    // ── Empower Women Video Logic ────────────────────────
    try {
        const videoWraps = document.querySelectorAll('.c-empower__video-wrap');
        if (videoWraps.length) {
            videoWraps.forEach(videoWrap => {
                const video = videoWrap.querySelector('video');
                const playBtn = videoWrap.querySelector('.c-empower__play-btn');

                if (video && playBtn) {
                    const updateState = () => {
                        if (video.paused) {
                            videoWrap.classList.remove('is-playing');
                        } else {
                            videoWrap.classList.add('is-playing');
                        }

                        if (video.muted) {
                            videoWrap.classList.add('is-muted');
                        } else {
                            videoWrap.classList.remove('is-muted');
                        }

                        const icon = playBtn.querySelector('i');
                        if (icon) {
                            if (video.paused) {
                                icon.className = 'bi bi-play-fill';
                                icon.style.marginLeft = '5px';
                            } else if (video.muted) {
                                icon.className = 'bi bi-volume-up-fill';
                                icon.style.marginLeft = '0';
                            } else {
                                icon.className = 'bi bi-pause-fill';
                                icon.style.marginLeft = '0';
                            }
                        }
                    };

                    const togglePlayback = (e) => {
                        e.stopPropagation();
                        if (video.muted) {
                            video.muted = false;
                            video.volume = 1.0; 
                            updateState();
                            if (!video.paused) return;
                        }
                        if (video.paused) {
                            video.play();
                        } else {
                            video.pause();
                        }
                        updateState();
                    };

                    playBtn.addEventListener('click', togglePlayback);
                    video.addEventListener('click', togglePlayback);
                    video.addEventListener('play', updateState);
                    video.addEventListener('pause', updateState);
                    video.addEventListener('volumechange', updateState);

                    updateState();
                }
            });
        }
    } catch (e) { console.error('Empower Video Init Failed:', e); }

    // ── Swiper Helper Function ───────────────────────────
    const initSwiper = (selector, options) => {
        try {
            const el = document.querySelector(selector);
            if (!el) return null;

            const slides = el.querySelectorAll('.swiper-slide');
            const slidesPerView = options.slidesPerView || 1;
            const requiredSlides = Math.floor(slidesPerView) + 1;
            
            if (options.loop && slides.length < requiredSlides) {
                console.warn(`Swiper [${selector}]: Not enough slides (${slides.length}) for loop mode. Disabling loop.`);
                options.loop = false;
            }

            return new Swiper(selector, options);
        } catch (e) {
            console.error(`Swiper [${selector}] Initialization Failed:`, e);
            return null;
        }
    };

    // ── Common Swiper Options ─────────────────────────────
    const productSliderOptions = {
        slidesPerView: 1.2,
        slidesPerGroup: 1,
        spaceBetween: 16,
        loop: true,
        speed: 700,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
            dynamicBullets: true
        },
        breakpoints: {
            576:  { slidesPerView: 2,   spaceBetween: 20 },
            768:  { slidesPerView: 3,   spaceBetween: 24 },
            1024: { slidesPerView: 4,   spaceBetween: 24 }
        }
    };

    // ── Hero Swiper ─────────────────────────
    initSwiper('#homeHeroSwiper', {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: true,
        speed: 1000,
        effect: 'fade',
        fadeEffect: { crossFade: true },
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.hero-pagination',
            clickable: true
        }
    });

    // ── Curated Combos Square Slider ─────────────────────
    initSwiper('.hero-combo-swiper', {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: true,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.hero-combo-swiper .swiper-pagination',
            clickable: true
        }
    });

    // ── Namkeens Slider ─────────────────────────
    initSwiper('#namkeensSwiper', {
        ...productSliderOptions,
        pagination: { 
            el: '#namkeensSwiper .nk-swiper-pagination',
            clickable: true 
        },
        breakpoints: {
            576:  { slidesPerView: 2,   spaceBetween: 20 },
            992:  { slidesPerView: 3.8, spaceBetween: 24 }
        }
    });

    // ── Testimonials Swiper ─────────────────────────
    initSwiper('#testimonialsSwiper', {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true
        },
        pagination: {
            el: '.testimonials-pagination',
            clickable: true,
            dynamicBullets: false,
        },
        breakpoints: {
            768: { slidesPerView: 2, spaceBetween: 30 },
            1200: { slidesPerView: 3, spaceBetween: 40 }
        },
        autoHeight: true,
    });

    // ── Latest News Updates Swiper ────────────────────────
    try {
        const lnSwiper = initSwiper('#latestNewsSwiper', {
            slidesPerView: 1.1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true
            },
            pagination: {
                el: '.latest-news-pagination',
                clickable: true,
                renderBullet: function (index, className) {
                    return '<span class="' + className + '"></span>';
                }
            },
            navigation: {
                nextEl: '.latest-news-next',
                prevEl: '.latest-news-prev'
            },
            breakpoints: {
                576: { slidesPerView: 2, spaceBetween: 24 },
                992: { slidesPerView: 3, spaceBetween: 30 }
            },
            on: {
                autoplayTimeLeft(s, time, progress) {
                    const bullets = document.querySelectorAll('.latest-news-pagination .swiper-pagination-bullet');
                    if (!bullets.length) return;
                    const activeIndex = s.realIndex;
                    bullets.forEach((bullet, index) => {
                        if (index < activeIndex) {
                            bullet.style.setProperty('--progress', '1');
                        } else if (index === activeIndex) {
                            bullet.style.setProperty('--progress', (1 - progress).toFixed(3));
                        } else {
                            bullet.style.setProperty('--progress', '0');
                        }
                    });
                },
                slideChange() {
                    const bullets = document.querySelectorAll('.latest-news-pagination .swiper-pagination-bullet');
                    if (!bullets.length) return;
                    const activeIndex = this.realIndex;
                    bullets.forEach((bullet, index) => {
                        bullet.style.setProperty('--progress', index < activeIndex ? '1' : '0');
                    });
                }
            }
        });

        // Autoplay Toggle Logic
        const lnToggle = document.getElementById('latestNewsToggle');
        if (lnToggle && lnSwiper) {
            const pauseIcon = lnToggle.querySelector('.toggle-icon--pause');
            const playIcon  = lnToggle.querySelector('.toggle-icon--play');
            lnToggle.addEventListener('click', () => {
                if (lnSwiper.autoplay.running) {
                    lnSwiper.autoplay.stop();
                    if (pauseIcon) pauseIcon.classList.add('d-none');
                    if (playIcon) playIcon.classList.remove('d-none');
                } else {
                    lnSwiper.autoplay.start();
                    if (pauseIcon) pauseIcon.classList.remove('d-none');
                    if (playIcon) playIcon.classList.add('d-none');
                }
            });
        }
    } catch (e) { console.error('Latest News Swiper Init Failed:', e); }

    // ── Combo Offers Marquee ──────────────────────────────
    try {
        initSwiper('.c-combo-marquee-swiper', {
            slidesPerView: 'auto',
            spaceBetween: 20,
            loop: true,
            speed: 5000,
            freeMode: true,
            freeModeMomentum: false,
            autoplay: {
                delay: 0,
                disableOnInteraction: false,
                pauseOnMouseEnter: true
            }
        });
    } catch (e) { console.error('Combo Marquee Init Failed:', e); }

    // ── Cart / Product Hero Sliders ────────────────────────
    try {
        const thumbsSwiper = initSwiper(".product-thumbs-v", {
            direction: window.innerWidth > 991 ? "vertical" : "horizontal",
            spaceBetween: 10,
            slidesPerView: 4,
            freeMode: true,
            watchSlidesProgress: true,
            speed: 0,
        });
        
        initSwiper(".product-main-v", {
            spaceBetween: 10,
            speed: 0,
            navigation: {
                nextEl: ".c-product-gallery-next",
                prevEl: ".c-product-gallery-prev",
            },
            thumbs: { swiper: thumbsSwiper },
        });
    } catch (e) { console.error('Product Sliders Init Failed:', e); }

    // ── Intersection Observer ─────────────────────────────
    try {
        const revealOptions = { threshold: 0.15 };
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view', 'sc-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, revealOptions);
        document.querySelectorAll('.js-reveal, .js-step, .sc-reveal').forEach(el => revealObserver.observe(el));
    } catch (e) { console.error('Reveal Observer Init Failed:', e); }

    // ── Wishlist Logic ───────────────────────────────────
    const initWishlist = () => {
        try {
            const wishlistButtons = document.querySelectorAll('.js-wishlist-toggle');
            if (!wishlistButtons.length) return;

            const wishlistBadge = document.querySelector('.c-header-actions__item a[href*="wishlist.php"]');
            let wishlist = [];
            try {
                wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
            } catch (err) { wishlist = []; }

            const updateWishlistBadge = () => {
                if (!wishlistBadge) return;
                let badge = wishlistBadge.querySelector('.badge');
                if (wishlist.length > 0) {
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                        badge.style.fontSize = '10px';
                        wishlistBadge.classList.add('position-relative');
                        wishlistBadge.appendChild(badge);
                    }
                    badge.textContent = wishlist.length;
                } else if (badge) {
                    badge.remove();
                }
            };

            wishlistButtons.forEach(btn => {
                const id = btn.dataset.id;
                const icon = btn.querySelector('i');
                if (wishlist.some(item => item.id === id)) {
                    if (icon) icon.classList.replace('bi-heart', 'bi-heart-fill');
                    btn.classList.add('is-active');
                }
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const product = {
                        id: btn.dataset.id,
                        name: btn.dataset.name,
                        price: btn.dataset.price,
                        image: btn.dataset.image,
                        url: btn.dataset.url
                    };
                    const index = wishlist.findIndex(item => item.id === product.id);
                    if (index === -1) {
                        wishlist.push(product);
                        if (icon) icon.classList.replace('bi-heart', 'bi-heart-fill');
                        btn.classList.add('is-active');
                    } else {
                        wishlist.splice(index, 1);
                        if (icon) icon.classList.replace('bi-heart-fill', 'bi-heart');
                        btn.classList.remove('is-active');
                    }
                    localStorage.setItem('wishlist', JSON.stringify(wishlist));
                    updateWishlistBadge();
                });
            });
            updateWishlistBadge();
        } catch (e) { console.error('Wishlist Init Failed:', e); }
    };

    // ── Mobile Menu ──────────────────────────────────────
    const initMobileMenu = () => {
        try {
            const toggleBtn = document.getElementById('mobileMenuToggle');
            const closeBtn  = document.getElementById('mobileMenuClose');
            const drawer    = document.getElementById('mobileNavDrawer');
            const overlay   = document.getElementById('mobileNavOverlay');
            const body      = document.body;

            if (!toggleBtn || !drawer || !overlay) return;

            const openMenu = () => {
                drawer.classList.add('is-active');
                overlay.classList.add('is-active');
                body.classList.add('nav-is-open');
            };
            const closeMenu = () => {
                drawer.classList.remove('is-active');
                overlay.classList.remove('is-active');
                body.classList.remove('nav-is-open');
            };

            toggleBtn.addEventListener('click', openMenu);
            if (closeBtn) closeBtn.addEventListener('click', closeMenu);
            overlay.addEventListener('click', closeMenu);
            drawer.querySelectorAll('.c-mobile-nav__link').forEach(link => {
                link.addEventListener('click', closeMenu);
            });
        } catch (e) { console.error('Mobile Menu Init Failed:', e); }
    };

    // Header Search Suggestions
    const initHeaderSearch = () => {
        try {
            const forms = document.querySelectorAll('.js-site-search');
            if (!forms.length) return;

            const escapeHtml = (value) => String(value || '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));

            forms.forEach(form => {
                const input = form.querySelector('.js-site-search-input');
                const panel = form.querySelector('.js-site-search-suggestions');
                const endpoint = form.dataset.searchApi;
                let controller = null;
                let debounceTimer = null;

                if (!input || !panel || !endpoint) return;

                const setOpen = (isOpen) => {
                    panel.classList.toggle('is-open', isOpen);
                    input.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                };

                const renderStatus = (message) => {
                    panel.innerHTML = `<div class="c-mobile-search__status">${escapeHtml(message)}</div>`;
                    setOpen(true);
                };

                const searchUrl = (query) => `${form.action}?search=${encodeURIComponent(query)}`;

                const renderResults = (query, results) => {
                    if (!results.length) {
                        renderStatus('No products found');
                        return;
                    }

                    panel.innerHTML = results.map(item => `
                        <a class="c-mobile-search__suggestion" href="${escapeHtml(item.url)}" role="option">
                            <img class="c-mobile-search__suggestion-img" src="${escapeHtml(item.image)}" alt="" loading="lazy">
                            <span class="c-mobile-search__suggestion-copy">
                                <span class="c-mobile-search__suggestion-name">${escapeHtml(item.name)}</span>
                                <span class="c-mobile-search__suggestion-meta">${escapeHtml(item.category || 'Sweet')} - ${escapeHtml(item.price_formatted || '')}</span>
                            </span>
                        </a>
                    `).join('') + `<a class="c-mobile-search__view-all" href="${searchUrl(query)}">View all results</a>`;
                    setOpen(true);
                };

                const fetchResults = async (query) => {
                    if (controller) controller.abort();
                    controller = new AbortController();
                    renderStatus('Searching...');

                    try {
                        const url = `${endpoint}?q=${encodeURIComponent(query)}&limit=6`;
                        const response = await fetch(url, { signal: controller.signal, headers: { 'Accept': 'application/json' } });
                        const data = await response.json();
                        renderResults(query, Array.isArray(data.results) ? data.results : []);
                    } catch (err) {
                        if (err.name === 'AbortError') return;
                        renderStatus('Search is temporarily unavailable');
                    }
                };

                input.addEventListener('input', () => {
                    const query = input.value.trim();
                    window.clearTimeout(debounceTimer);
                    if (query.length < 2) {
                        setOpen(false);
                        panel.innerHTML = '';
                        return;
                    }

                    debounceTimer = window.setTimeout(() => fetchResults(query), 250);
                });

                input.addEventListener('focus', () => {
                    if (panel.innerHTML.trim() && input.value.trim().length >= 2) {
                        setOpen(true);
                    }
                });

                form.addEventListener('submit', (e) => {
                    const query = input.value.trim();
                    if (!query) {
                        e.preventDefault();
                        input.focus();
                    }
                });

                document.addEventListener('click', (e) => {
                    if (!form.contains(e.target)) {
                        setOpen(false);
                    }
                });
            });
        } catch (e) { console.error('Header Search Init Failed:', e); }
    };

    // ── Product Catalog Dropdowns ────────────────────────
    const initCatalogInteraction = () => {
        try {
            const toggles = document.querySelectorAll('.js-dropdown-toggle');
            toggles.forEach(toggle => {
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const menu = toggle.nextElementSibling;
                    const isOpen = menu && menu.classList.contains('show');

                    // Close all others
                    document.querySelectorAll('.js-dropdown-menu.show').forEach(m => {
                        if (m !== menu) m.classList.remove('show');
                    });
                    document.querySelectorAll('.js-dropdown-toggle.is-active').forEach(t => {
                        if (t !== toggle) t.classList.remove('is-active');
                    });

                    // Toggle current
                    if (menu) {
                        menu.classList.toggle('show', !isOpen);
                        toggle.classList.toggle('is-active', !isOpen);
                    }
                });
            });

            document.addEventListener('click', () => {
                document.querySelectorAll('.js-dropdown-menu.show').forEach(m => m.classList.remove('show'));
                document.querySelectorAll('.js-dropdown-toggle.is-active').forEach(t => t.classList.remove('is-active'));
            });
        } catch (e) { console.error('Catalog interaction Init Failed:', e); }
    };

    // ── Weight Selection ────────────────────────────────
    const initWeightSelection = () => {
        try {
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.c-weight-btn, .c-weight-btn--compact, .weight-btn');
                if (!btn) return;
                const weight = btn.dataset.weight;
                const card = btn.closest('.c-product-card-v2') || btn.closest('.c-bestseller-card-wrap') || btn.closest('.h-100.d-flex.flex-column');
                if (!card) return;
                const parent = btn.parentElement;
                if (!parent) return;

                parent.querySelectorAll('.c-weight-btn, .c-weight-btn--compact, .weight-btn').forEach(b => {
                    b.classList.remove('active');
                    if (b.style.background) { b.style.background = 'transparent'; b.style.color = '#888'; }
                });
                btn.classList.add('active');
                if (btn.style.background !== undefined) { btn.style.background = '#EBE4D5'; btn.style.color = '#333'; }

                const priceCurrent = card.querySelector('.c-product-card-v2__price-current');
                const priceOld = card.querySelector('.c-product-card-v2__price-old');
                if (priceCurrent) {
                    const wishlistBtn = card.querySelector('.js-wishlist-toggle');
                    let basePrice = (wishlistBtn && wishlistBtn.dataset.price) ? parseFloat(wishlistBtn.dataset.price) : (parseFloat(priceCurrent.textContent.replace('₹', '').replace(',', '')) || 720);
                    let multiplier = weight === '1kg' ? 3.5 : (weight === '500g' ? 1.85 : 1);
                    const newPrice = Math.round(basePrice * multiplier);
                    priceCurrent.textContent = `₹${newPrice.toLocaleString()}`;
                    if (priceOld) priceOld.textContent = `₹${Math.round(newPrice * 1.3).toLocaleString()}`;
                }
            });
        } catch (e) { console.error('Weight Selection Init Failed:', e); }
    };

    // ── Quick Cart Actions ───────────────────────────────
    const initQuickCartActions = () => {
        try {
            const updateCartBadge = (itemCount) => {
                const cartLink = document.querySelector('.c-header-actions__item a[href*="shopping-cart.php"]');
                if (!cartLink) return;
                let badge = cartLink.querySelector('.badge');
                if (itemCount > 0) {
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                        badge.style.fontSize = '10px';
                        cartLink.appendChild(badge);
                    }
                    badge.textContent = String(itemCount);
                } else if (badge) badge.remove();
            };

            const addToCartWithoutRedirect = async (slug, weight) => {
                if (!slug) return false;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const payload = new FormData();
                payload.append('action', 'add_to_cart');
                payload.append('slug', slug);
                payload.append('quantity', '1');
                payload.append('weight', weight || '500g');
                if (csrfToken) payload.append('csrf_token', csrfToken);
                try {
                    const response = await fetch('api/cart-handler.php', { method: 'POST', body: payload });
                    const data = await response.json();
                    if (data.success) {
                        updateCartBadge(Number(data.itemCount) || 0);
                        localStorage.setItem('cart_sync_time', Date.now().toString());
                        if (window.Swal) Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1800, icon: 'success', title: 'Added to cart' });
                        return true;
                    }
                    return false;
                } catch (err) { return false; }
            };

            document.addEventListener('click', (e) => {
                const trigger = e.target.closest('.c-btn-cart--compact, .c-btn-book--compact, .btn-cart, .btn-book');
                if (!trigger) return;

                const card = trigger.closest('.c-product-card-v2, .c-product-card-premium, .c-bestseller-card-wrap') || document;
                let href = trigger.getAttribute('href');
                let slug = '';
                if (href && href.includes('slug=')) slug = href.split('slug=')[1].split('&')[0];
                if (!slug) {
                    const slugSource = card.querySelector('a[href*="slug="]');
                    if (slugSource) slug = slugSource.getAttribute('href').split('slug=')[1].split('&')[0];
                }
                if (!slug) return;

                const activeWeightBtn = card.querySelector('.weight-btn.active, .c-weight-btn.active');
                const weight = activeWeightBtn ? activeWeightBtn.dataset.weight : '500g';
                const action = (trigger.classList.contains('c-btn-book--compact') || trigger.classList.contains('btn-book')) ? 'buy_now' : 'add_to_cart';

                if (action === 'add_to_cart' && trigger.classList.contains('btn-cart')) {
                    e.preventDefault(); e.stopPropagation();
                    addToCartWithoutRedirect(slug, weight).then(ok => { if (!ok) window.location.href = `cart.php?action=add_to_cart&slug=${slug}&weight=${weight}`; });
                    return;
                }
                window.location.href = `cart.php?action=${action}&slug=${slug}&weight=${weight}`;
            });
        } catch (e) { console.error('Quick Cart Actions Init Failed:', e); }
    };

    // ── Cross-Tab Cart Synchronization ───────────────────
    const initCartSync = () => {
        window.addEventListener('storage', (e) => {
            if (e.key === 'cart_sync_time') {
                fetch('api/v1/cart-sync.php')
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Update global state if on PDP
                            if (typeof window.cartItems !== 'undefined') {
                                // Rebuild cartItems dictionary based on cart contents
                                window.cartItems = {};
                                if (data.cartItems) {
                                    Object.entries(data.cartItems).forEach(([key, item]) => {
                                        window.cartItems[key] = { quantity: item.quantity, slug: item.slug, weight: item.weight };
                                    });
                                }
                                // Re-render PDP UI
                                if (typeof syncQtyDisplay === 'function') syncQtyDisplay();
                            }
                            
                            // Update Header Badge everywhere
                            const cartLink = document.querySelector('.c-header-actions__item a[href*="shopping-cart.php"]');
                            if (cartLink) {
                                let badge = cartLink.querySelector('.badge');
                                if (data.cartCount > 0) {
                                    if (!badge) {
                                        badge = document.createElement('span');
                                        badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger js-cart-count';
                                        badge.style.fontSize = '10px';
                                        cartLink.classList.add('position-relative');
                                        cartLink.appendChild(badge);
                                    }
                                    badge.textContent = String(data.cartCount);
                                } else if (badge) {
                                    badge.remove();
                                }
                            }
                        }
                    })
                    .catch(err => console.warn('Cart sync failed:', err));
            }
        });
    };

    // ── Best Sellers Filtering (Robust) ──────────────────
    const initBestsellerFilter = () => {
        try {
            const tabs = document.querySelectorAll('.js-bestseller-tab');
            if (!tabs.length) return;

            const normalizeCategory = (value) => {
                const raw = (value || '').toString().trim().toLowerCase().replace(/\s+/g, '-');
                const aliasMap = {
                    karant: 'karadant',
                    karadantu: 'karadant',
                    'kharadant': 'karadant',
                    'gift-boxes': 'gift-box'
                };
                return aliasMap[raw] || raw;
            };

            tabs.forEach(tab => {
                tab.addEventListener('click', (e) => {
                    const filterValue = tab.getAttribute('data-filter') || tab.dataset.filter || 'all';
                    const filter = normalizeCategory(filterValue);
                    
                    console.log(`[Bestsellers] Filtering initiated. Target: ${filter}`);

                    const allTabs = document.querySelectorAll('.js-bestseller-tab');
                    const allItems = document.querySelectorAll('.js-bestseller-item');
                    const grid = document.getElementById('bsProductGrid');

                    if (!allItems.length) {
                        console.warn('[Bestsellers] No items found to filter.');
                        return;
                    }

                    // Update UI state
                    allTabs.forEach(t => t.classList.remove('active', 'is-active'));
                    tab.classList.add('active', 'is-active');

                    if (grid) grid.style.opacity = '0.4';
                    
                    setTimeout(() => {
                        let visibleItems = [];
                        allItems.forEach(item => {
                            const categoryValue = item.getAttribute('data-category') || item.dataset.category || '';
                            const category = normalizeCategory(categoryValue);
                            
                            if (filter === 'all' || !filter || category === filter) {
                                item.classList.remove('d-none');
                                item.style.display = '';
                                item.style.opacity = '1';
                                visibleItems.push(item);
                            } else {
                                item.classList.add('d-none');
                                item.style.display = 'none';
                                item.style.opacity = '0';
                            }
                        });
                        
                        console.log(`[Bestsellers] Filter complete. Matches showing: ${visibleItems.length}`);
                        if (grid) grid.style.opacity = '1';
                        
                        // Force a scroll reveal check for visible items
                        if (typeof window.dispatchEvent === 'function') {
                            window.dispatchEvent(new Event('scroll'));
                        }
                    }, 250);
                });
            });
            console.log(`[Bestsellers] Filter initialized with ${tabs.length} tabs.`);
            
            // Trigger filter for the initially active tab
            setTimeout(() => {
                const activeBestsellerTab = document.querySelector('.js-bestseller-tab.active');
                if (activeBestsellerTab) {
                    activeBestsellerTab.click();
                }
            }, 100);
        } catch (e) { console.error('Bestseller Filter Failed:', e); }
    };

    // Run Initializations
    initBestsellerFilter();
    initWeightSelection();
    initQuickCartActions();
    initCartSync();
    initCatalogInteraction();
    initMobileMenu();
    initHeaderSearch();
    initWishlist();
};

// Robust Entry Point
if (document.readyState === 'interactive' || document.readyState === 'complete') {
    initSweetsWebsite();
} else {
    document.addEventListener('DOMContentLoaded', initSweetsWebsite);
}
