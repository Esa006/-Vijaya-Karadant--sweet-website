<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/top-bar.php
 * Description: Secondary dark brown banner highlighting order discount
 * =============================================================
 */
?>
<?php 
$is_home_page = (basename($_SERVER['PHP_SELF']) == 'index.php');
?>
<div class="c-top-bar <?php echo $is_home_page ? 'c-top-bar--static' : ''; ?>">
    <div class="container-fluid p-0 overflow-hidden">
        <div class="c-top-bar__marquee">
            <div class="c-top-bar__track">
                <span class="c-top-bar__item">Get ₹200 Off + 10% Cashback* - First Order</span>
                <span class="c-top-bar__item">Get ₹200 Off + 10% Cashback* - First Order</span>
                <span class="c-top-bar__item">Get ₹200 Off + 10% Cashback* - First Order</span>
                <span class="c-top-bar__item">Get ₹200 Off + 10% Cashback* - First Order</span>
                <!-- Duplicate for seamless loop -->
                <span class="c-top-bar__item">Get ₹200 Off + 10% Cashback* - First Order</span>
                <span class="c-top-bar__item">Get ₹200 Off + 10% Cashback* - First Order</span>
                <span class="c-top-bar__item">Get ₹200 Off + 10% Cashback* - First Order</span>
                <span class="c-top-bar__item">Get ₹200 Off + 10% Cashback* - First Order</span>
            </div>
        </div>
    </div>
</div>
