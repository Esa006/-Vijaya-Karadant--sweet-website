<?php
/**
 * Sweets Website
 * =============================================================
 * File: about.php
 * Description: About Us Page
 * Author: Sweets Website Team
 * Version: 1.0.0
 * =============================================================
 */

require_once 'config/config.php';
require_once 'includes/header.php';
?>

<!-- ═══════════════════════════════════════════════════ -->
<!--  About Hero Section                                 -->
<!-- ═══════════════════════════════════════════════════ -->
<!-- About Hero Section -->
<?php require_once 'sections/about-hero.php'; ?>

<!-- ═══════════════════════════════════════════════════ -->
<!--  The Beginning Story Section                        -->
<!-- ═══════════════════════════════════════════════════ -->

<!-- The Beginning Story Section -->
<?php require_once 'sections/about-story.php'; ?>

<!-- ═══════════════════════════════════════════════════ -->
<!--  Signature Collections Features                     -->
<!-- ═══════════════════════════════════════════════════ -->

<!-- Signature Collections Features -->
<?php require_once 'sections/about-signature-features.php'; ?>



<!-- Journey Timeline -->
<?php require_once 'sections/about-timeline.php'; ?>

<!--  Vision for the Next Century                        -->
<!-- ═══════════════════════════════════════════════════ -->
<!-- Vision for the Next Century -->
<?php require_once 'sections/about-vision.php'; ?>

<!-- Signature Collections Section -->
<?php
$collectionsTitle = "Our Signature Collection";
require_once 'sections/collections.php';

?>

<!-- ═══════════════════════════════════════════════════ -->
<!--  The Vijaya Distinction                             -->
<!-- ═══════════════════════════════════════════════════ -->

<!-- The Vijaya Distinction -->
<?php require_once 'sections/about-distinction.php'; ?>

<!-- ═══════════════════════════════════════════════════ -->
<!--  Who We Serve                                       -->
<!-- ═══════════════════════════════════════════════════ -->

<!-- Who We Serve -->
<?php require_once 'sections/about-serve.php'; ?>

<!-- ═══════════════════════════════════════════════════ -->
<!--  Journey Timeline                                   -->
<!-- ═══════════════════════════════════════════════════ -->
<!-- Empowered by Women Section -->
<?php require_once 'sections/empower-women.php'; ?>

<!-- ═══════════════════════════════════════════════════ -->
<!--  Partner CTA                                        -->
<!-- ══════════════════════════════════════════════════ -->


<!-- Franchise Banner Section -->
<?php require_once 'sections/franchise-banner.php'; ?>

<?php require_once 'includes/footer.php'; ?>

