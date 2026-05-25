<?php
/**
 * Sweets Website - Testimonials Section
 */
$testimonials = $productService->getTestimonials();
?>
<section class="c-testimonials py-5">
    <div class="container text-center">
        <h2 class="c-testimonials__title mb-5">
            <img src="assets/images/icon/el_star-alt.png" alt="" />
            <span class="c-header-text">Legacy in Every Byte, Success in Every City</span>
            <img src="assets/images/icon/el_star-alt.png" alt="" />
        </h2>
        
        <!-- Swiper Container -->
        <div class="swiper c-testimonials__swiper" id="testimonialsSwiper">
            <div class="swiper-wrapper">
                <?php foreach ($testimonials as $item): ?>
                    <div class="swiper-slide">
                        <?php if ($item['type'] === 'text'): ?>
                            <div class="item-card testimonial-card p-4 shadow-sm rounded-4 bg-white">
                                <div class="quote-icon mb-3">
                                    <i class="bi bi-quote fs-1 text-primary"></i>
                                </div>
                                
                                <div class="stars-container mb-3 text-warning">
                                    <?php for ($i = 0; $i < $item['rating']; $i++): ?>
                                        <i class="bi bi-star-fill"></i>
                                    <?php
        endfor; ?>
                                </div>

                                <p class="quote-text fs-5 mb-4 italic">
                                   All-in-one mixture 
                                </p>

                                <div class="author-info border-top pt-3">
                                    <p class="author-name fw-bold mb-0"><?php echo htmlspecialchars($item['author']); ?></p>
                                    <p class="author-date text-muted small"><?php echo htmlspecialchars($item['date']); ?></p>
                                </div>
                            </div>
                        <?php
    else: ?>
                            <div class="item-card video-card rounded-4 overflow-hidden shadow-sm">
                                <video 
                                    src="<?php echo htmlspecialchars($item['video_url']); ?>" 
                                    poster="<?php echo htmlspecialchars($item['poster']); ?>"
                                    class="w-100  object-fit-cover" 
                                    controls 
                                    preload="metadata">
                                    Your browser does not support HTML video.
                                </video>
                            </div>
                        <?php
    endif; ?>
                    </div>
                <?php
endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <div class="swiper-pagination testimonials-pagination mt-5"></div>
        </div>
    </div>
</section>
