<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/gift-box-form.php
 * Description: Form section for custom gift box inquiries
 * =============================================================
 */
?>
<section class="gift-box-form">
    <div class="container">
        <div class="gift-box-form__header">
            <h2 class="gift-box-form__title">Ready to Create Your Custom Gift Box?</h2>
            <p class="gift-box-form__subtitle">Share your details and we'll curate the perfect sweet hamper</p>
        </div>

        <div class="gift-box-form__container">
            <form action="#" method="POST">
                <div class="row g-3">
                    
                    <!-- Name -->
                    <div class="col-lg-2 col-md-6 col-12">
                        <label class="gift-box-form__label" for="gb_name">Name</label>
                        <input type="text" id="gb_name" name="gb_name" class="gift-box-form__input" placeholder="Enter your Name" required>
                    </div>

                    <!-- Mobile No -->
                    <div class="col-lg-3 col-md-6 col-12">
                        <label class="gift-box-form__label" for="gb_mobile">Mobile No</label>
                        <input type="tel" id="gb_mobile" name="gb_mobile" class="gift-box-form__input" placeholder="Enter your Mobile Number" required>
                    </div>

                    <!-- Occasion -->
                    <div class="col-lg-3 col-md-6 col-12">
                        <label class="gift-box-form__label" for="gb_occasion">Occasion</label>
                        <select id="gb_occasion" name="gb_occasion" class="gift-box-form__select" required>
                            <option value="" disabled selected>Select Occasion</option>
                            <option value="wedding">Wedding</option>
                            <option value="festival">Festival</option>
                            <option value="corporate">Corporate Gifting</option>
                            <option value="birthday">Birthday / Anniv.</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Budget -->
                    <div class="col-lg-2 col-md-6 col-12">
                        <label class="gift-box-form__label" for="gb_budget">Budget</label>
                        <select id="gb_budget" name="gb_budget" class="gift-box-form__select" required>
                            <option value="" disabled selected>Select Your Budget</option>
                            <option value="below_1000">Below ₹1,000</option>
                            <option value="1000_3000">₹1,000 - ₹3,000</option>
                            <option value="3000_5000">₹3,000 - ₹5,000</option>
                            <option value="above_5000">Above ₹5,000</option>
                        </select>
                    </div>

                    <!-- Delivery Date -->
                    <div class="col-lg-2 col-md-12 col-12">
                        <label class="gift-box-form__label" for="gb_date">Delivery Date</label>
                        <input type="date" id="gb_date" name="gb_date" class="gift-box-form__input" required>
                    </div>

                </div>

                <!-- Submit Button -->
                <div class="text-center mt-4 pt-2">
                    <button type="submit" class="gift-box-form__submit">Create My Gift Box</button>
                </div>

            </form>
        </div>
    </div>
</section>
