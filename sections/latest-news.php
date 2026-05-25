<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/latest-news.php
 * Description: "Latest News Updates" slider section
 * =============================================================
 */

require_once ROOT_PATH . '/services/NewsService.php';
$newsService = new NewsService();
$latestNews = $newsService->getActiveNews();
?>

<section class="c-latest-news py-5">
    <div class="container">
        <div class="c-latest-news__header text-center mb-5">
            <h2 class="c-latest-news__title">
                <img src="assets/images/icon/explorelogo.png" alt="" class="c-header-icon" aria-hidden="true" />
                <span class="c-header-text">Latest News Updates</span>
                <img src="assets/images/icon/explorelogo.png" alt="" class="c-header-icon" aria-hidden="true" />
            </h2>
        </div>

        <!-- Swiper Slider -->
        <div class="swiper c-latest-news__swiper" id="latestNewsSwiper">
            <div class="swiper-wrapper">

                <?php foreach ($latestNews as $newsItem): ?>
                    <div class="swiper-slide">
                        <div class="c-news-card">
                            <div class="c-news-card__img-wrap">
                                <?php $imgSrc = !empty($newsItem['image_path']) ? BASE_URL . $newsItem['image_path'] : BASE_URL . 'assets/images/placeholders/product-placeholder.png'; ?>
                                <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                    alt="<?php echo htmlspecialchars($newsItem['title']); ?>" class="img-fluid"
                                    loading="lazy">
                            </div>
                            <div class="c-news-card__content">
                                <p class="c-news-card__date">
                                    <?php echo date('d-m-Y', strtotime($newsItem['publish_date'])); ?></p>
                                <h3 class="c-news-card__headline"><?php echo htmlspecialchars($newsItem['title']); ?></h3>
                                <p class="c-news-card__desc">
                                    <?php echo htmlspecialchars($newsItem['description']); ?>
                                </p>
                                <a href="#" class="btn c-news-card__btn" aria-label="Read more about <?php echo htmlspecialchars($newsItem['title']); ?>">Read More</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>


            <!-- Pagination dots with Progress style -->
            <div class="swiper-pagination latest-news-pagination mt-4"></div>
        </div>
    </div>
</section>