<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/ingredients-process.php
 * Description: From Ingredients To Celebration section
 * =============================================================
 */
?>
<section class="c-ingredients">
    <div class="container">
        <!-- Header -->
        <h2 class="c-ingredients__title text-center ip-reveal" data-delay="0">
            <div class="ingredients-img">
                <img src="assets/images/icon/ingredients.png" alt="ingredients"  />
            </div>
            <span class="c-header-text">From Ingredients To Celebration</span>
          <div class="ingredients-img">
              <img src="assets/images/icon/ingredients.png" alt="ingredients" />
          </div>
        </h2>
        
        <div class="c-ingredients__wrapper row">
            <!-- Step 1 -->
             <!-- Featured Video Area -->
        <div class="c-empower__video-wrap" data-aos="zoom-in">
           <video src="assets/images/homepage/New_Animation_Generated.mp4"  loop playsinline  ></video>
            <button class="c-empower__play-btn"  aria-label="Play Story">
                <i class="bi bi-play-fill"></i>
            </button>
        </div>
        </div>
        
    </div>
</section>

<script>
(function () {
    /* — Scroll Reveal for steps & title — */
    const revealEls = document.querySelectorAll('.ip-reveal');
    const connectors = document.querySelectorAll('.ip-connector');

    const obs = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const delay = parseInt(el.dataset.delay) || 0;
                setTimeout(() => {
                    el.classList.add('ip-reveal--visible');
                }, delay);
                obs.unobserve(el);
            }
        });
    }, { threshold: 0.25 });

    revealEls.forEach(el => obs.observe(el));

    /* — Connector draw animation — */
    const connObs = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const delay = parseInt(el.dataset.delay) || 0;
                setTimeout(() => {
                    el.classList.add('ip-connector--visible');
                }, delay);
                connObs.unobserve(el);
            }
        });
    }, { threshold: 0.3 });

    connectors.forEach(el => connObs.observe(el));
})();
</script>
