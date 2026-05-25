<?php
/**
 * Sweets Website
 * =============================================================
 * File: branches.php
 * Description: Branches listing page
 * =============================================================
 */

require_once 'config/config.php';

// Header Meta overrides
$pageTitle = "Branches | " . SITE_NAME;

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/branches.css?v=<?php echo SITE_VERSION; ?>">

<main class="p-branches">
    <!-- Hero: Kiosk Model -->
    <section class="c-branches-hero">
        <div class="c-branches-hero__frame">
            <!-- <span class="c-branches-hero__kicker">KIOSK MODEL</span> -->
        </div>
        <div class="container c-branches-hero__container">

            <div class="row g-4 c-branches-hero__stats">
                <div class="col-md-4">
                    <div class="c-branch-stat c-branch-stat--locations">
                        <span class="c-branch-stat__icon"><i class="bi bi-geo-alt"></i></span>
                        <div>
                            <p class="c-branch-stat__label">19+ Location</p>
                            <p class="c-branch-stat__value">Serving Across South India</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="c-branch-stat c-branch-stat--presence">
                        <span class="c-branch-stat__icon"><i class="bi bi-compass"></i></span>
                        <div>
                            <p class="c-branch-stat__label">Pan India Presence</p>
                            <p class="c-branch-stat__value">Online & Store Delivery</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="c-branch-stat c-branch-stat--trusted">
                        <span class="c-branch-stat__icon"><i class="bi bi-shield-check"></i></span>
                        <div>
                            <p class="c-branch-stat__label">Trusted Since 1907</p>
                            <p class="c-branch-stat__value">Heritage of Quality & Taste</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Intro -->
    <section class="c-branches-intro">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    <h2 class="c-branches-intro__title">Healthy &amp; delicious to grow your fortune.</h2>
                    <p class="c-branches-intro__text">
                        Vijaya Karadant aims to bring traditional Indian flavors to the forefront, offering a wide
                        variety of
                        authentic Indian sweets and snacks. Our retail and kiosk models capture the essence of Indian
                        festivities and everyday indulgence.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="c-branches-intro__card">
                        <img src="assets/images/banners/branches/branches-img.png" alt="Retail store model"
                            class="img-fluid w-100">
                        <!-- <span class="c-branches-intro__card-title">Retail Store Model</span> -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bangalore Branches -->
    <section class="c-branches-list">
        <div class="container">
            <h3 class="c-branches-list__title">Bangalore Branches</h3>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Vijayanagar</h4>
                        <p><i class="bi bi-geo-alt"></i> Near Maruti Mandir, Vijayanagar, 2nd Cross</p>
                        <p><i class="bi bi-telephone"></i> +91 99808 80111</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Vijayanagar" data-address="Near Maruti Mandir, Vijayanagar, 2nd Cross, Bangalore">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Basavanagudi</h4>
                        <p><i class="bi bi-geo-alt"></i> 58 Dr. DVG Road, Basavanagudi, Bangalore-560004</p>
                        <p><i class="bi bi-telephone"></i> +91 98804 52871</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Basavanagudi" data-address="58 Dr. DVG Road, Basavanagudi, Bangalore-560004">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Malleshwaram</h4>
                        <p><i class="bi bi-geo-alt"></i> Malleshwaram, Sampige Road 7 Cross</p>
                        <p><i class="bi bi-telephone"></i> +91 94494 48172</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Malleshwaram" data-address="Malleshwaram, Sampige Road 7 Cross, Bangalore">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Basaveshwar Nagar</h4>
                        <p><i class="bi bi-geo-alt"></i> 322, Siddheshwar Nagar, 3rd Stage Basaveshwar Nagar, Bangalore-560079</p>
                        <p><i class="bi bi-telephone"></i> +91 98636 53222</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Basaveshwar Nagar" data-address="322, Siddheshwar Nagar, 3rd Stage Basaveshwar Nagar, Bangalore-560079">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Rajaji Nagar</h4>
                        <p><i class="bi bi-geo-alt"></i> 706/4/5, 42nd Cross, 3rd Block, Rajaji Nagar Near CID Police Station, Bangalore-10</p>
                        <p><i class="bi bi-telephone"></i> +91 98866 16614</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Rajaji Nagar" data-address="706/4/5, 42nd Cross, 3rd Block, Rajaji Nagar Near CID Police Station, Bangalore-10">View on Map</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Other Cities Branches -->
    <section class="c-branches-list c-branches-list--alt">
        <div class="container">
            <h3 class="c-branches-list__title">Other Cities Branches</h3>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Mangalore</h4>
                        <p><i class="bi bi-geo-alt"></i> Near Forum Mall Mangaluru-575001</p>
                        <p><i class="bi bi-telephone"></i> +91 96638 38777</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Mangalore" data-address="Near Forum Mall Mangaluru-575001">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Gangavati</h4>
                        <p><i class="bi bi-geo-alt"></i> Bus Stand Road, Opp. Hotel Sarvesh</p>
                        <p><i class="bi bi-telephone"></i> +91 99664 34069</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Gangavati" data-address="Bus Stand Road, Opp. Hotel Sarvesh, Gangavati">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Badami</h4>
                        <p><i class="bi bi-geo-alt"></i> Beside Palace Hotel, Main Road</p>
                        <p><i class="bi bi-telephone"></i> +91 97316 77915</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Badami" data-address="Beside Palace Hotel, Main Road, Badami">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Lingasagur</h4>
                        <p><i class="bi bi-geo-alt"></i> Opp. Jamakandi Hospital, Bangalore Bypass Road, Lingasagur-584122</p>
                        <p><i class="bi bi-telephone"></i> +91 99456 05488</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Lingasagur" data-address="Opp. Jamakandi Hospital, Bangalore Bypass Road, Lingasagur-584122">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Bagalkot</h4>
                        <p><i class="bi bi-geo-alt"></i> Shop No 12, Kalidas Circle, Vidyagiri, Bagalkot</p>
                        <p><i class="bi bi-telephone"></i> +91 98801 12002</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Bagalkot" data-address="Shop No 12, Kalidas Circle, Vidyagiri, Bagalkot">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Karatagi</h4>
                        <p><i class="bi bi-geo-alt"></i> Near Canara Bank, Navali Road</p>
                        <p><i class="bi bi-telephone"></i> +91 95384 07846</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Karatagi" data-address="Near Canara Bank, Navali Road, Karatagi">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Ilkal</h4>
                        <p><i class="bi bi-geo-alt"></i> KSRTC Bus Stand, Complex Ilkal</p>
                        <p><i class="bi bi-telephone"></i> +91 99018 26590</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Ilkal" data-address="KSRTC Bus Stand, Complex Ilkal">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Hubli</h4>
                        <p><i class="bi bi-geo-alt"></i> Opp. Mavazhute Gul Picee, Moorsavannath Road, Dajiban Peth Cross</p>
                        <p><i class="bi bi-telephone"></i> +91 90083 21117</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Hubli" data-address="Opp. Mavazhute Gul Picee, Moorsavannath Road, Dajiban Peth Cross, Hubli">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Raichur</h4>
                        <p><i class="bi bi-geo-alt"></i> Shop No. MPL No-1 & 2A, Opp. Municipal Dressers, Mahaveer Circle</p>
                        <p><i class="bi bi-telephone"></i> +91 98862 18639</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Raichur" data-address="Shop No. MPL No-1 & 2A, Opp. Municipal Dressers, Mahaveer Circle, Raichur">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Sindhanur</h4>
                        <p><i class="bi bi-geo-alt"></i> KSRTC New Commercial Complex</p>
                        <p><i class="bi bi-telephone"></i> +91 78290 03480</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Sindhanur" data-address="KSRTC New Commercial Complex, Sindhanur">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Manvi</h4>
                        <p><i class="bi bi-geo-alt"></i> Harsha Plaza Complex, Opp. HDFC Bank</p>
                        <p><i class="bi bi-telephone"></i> +91 80731 28165</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Manvi" data-address="Harsha Plaza Complex, Opp. HDFC Bank, Manvi">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Banahatti</h4>
                        <p><i class="bi bi-geo-alt"></i> Bhadrannavar Complex, Mangalavar Pet Road</p>
                        <p><i class="bi bi-telephone"></i> +91 82982 18538</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Banahatti" data-address="Bhadrannavar Complex, Mangalavar Pet Road, Banahatti">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Aiholli</h4>
                        <p><i class="bi bi-geo-alt"></i> Opp. to Durga Temple Aiholli</p>
                        <p><i class="bi bi-telephone"></i> +91 99009 25971</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Aiholli" data-address="Opp. to Durga Temple Aiholli">View on Map</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="c-branch-card">
                        <h4>Hungund</h4>
                        <p><i class="bi bi-geo-alt"></i> Siddappa Kadapati Shopping Complex Plus</p>
                        <p><i class="bi bi-telephone"></i> +91 89004 92259 / 86094 54387</p>
                        <p><i class="bi bi-clock"></i> 10 AM - 10 PM</p>
                        <a class="c-branch-card__btn js-view-map" href="#" data-branch="Hungund" data-address="Siddappa Kadapati Shopping Complex Plus, Hungund">View on Map</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Factory -->
    <section class="c-branches-factory">
        <div class="container">
            <h3 class="c-branches-factory__title">Our Factory</h3>
            <p class="c-branches-factory__name">AMINGAD</p>
            <p class="c-branches-factory__address">
                Raichur Bagalkot Road, Near Hadi Basaveshwar Temple, Mullur Road, Amingad, Hungund Taluk,
                Bagalkot-587112
            </p>
        </div>
    </section>
</main>


    <!-- Map Modal -->
    <div class="modal fade" id="branchMapModal" tabindex="-1" aria-labelledby="branchMapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
                <div class="modal-header" style="background: #8a2c22; color: #fff;">
                    <h5 class="modal-title" id="branchMapModalLabel">Branch Location</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="ratio ratio-16x9">
                        <iframe id="branchMapIframe" src="" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" style="border:0;"></iframe>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f1e7; border-top: 1px solid rgba(138, 44, 34, 0.1);">
                    <p id="branchMapAddress" class="mb-0 me-auto text-muted small"></p>
                    <a id="branchMapExternalLink" href="#" target="_blank" class="btn btn-sm" style="background: #8a2c22; color: #fff;">Open in Google Maps</a>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapModalElement = document.getElementById('branchMapModal');
    if (!mapModalElement) return;
    
    const mapModal = new bootstrap.Modal(mapModalElement);
    const mapIframe = document.getElementById('branchMapIframe');
    const modalTitle = document.getElementById('branchMapModalLabel');
    const modalAddress = document.getElementById('branchMapAddress');
    const externalLink = document.getElementById('branchMapExternalLink');

    document.querySelectorAll('.js-view-map').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const branch = this.dataset.branch;
            const address = this.dataset.address;
            const encodedAddress = encodeURIComponent(address + ', Karnataka, India');
            
            // Update modal content
            modalTitle.textContent = branch + ' Branch Location';
            modalAddress.textContent = address;
            externalLink.href = `https://www.google.com/maps/search/?api=1&query=${encodedAddress}`;
            
            // Set dynamic map src using the free search embed (no API key required for basic search)
            mapIframe.src = `https://maps.google.com/maps?q=${encodedAddress}&output=embed`;
            
            // Show modal
            mapModal.show();
        });
    });

    // Clear iframe src when modal closes to stop loading/performance
    mapModalElement.addEventListener('hidden.bs.modal', function () {
        mapIframe.src = '';
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>