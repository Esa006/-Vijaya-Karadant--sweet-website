<?php
/**
 * Sweets Website
 * =============================================================
 * File: privacy-policy.php
 * Description: Privacy Policy page (converted from index.html)
 * =============================================================
 */

require_once 'config/config.php';

$seoContext = [
    'title'       => 'Privacy Policy | ' . SITE_NAME,
    'description' => 'Read the Privacy Policy of ' . SITE_NAME . '. Learn how we collect, use, and protect your personal information when you shop with us.',
    'canonical'   => BASE_URL . 'privacy-policy.php',
];

require_once 'includes/header.php';
?>

<!-- Custom Privacy Styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/privacy-policy.css?v=<?php echo SITE_VERSION; ?>">

<main class="p-privacy-page py-5">
    <div class="container py-lg-4">
        <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="mb-4 text-center">Privacy Policy</h1>
            
            <p>Your personal information is always kept confidential. The privacy policy is displayed on the website. The type of info collected from the customers and usage of this information is published here. We have a policy of not disclosing any information to third parties. Using our website means you have agreed to the terms and conditions of the website. It applies to the people who have not got any transactions or who have got registered to the site and had business. Personal Information is mainly used to locate or contact a person. Other information like name address, phone number, fax, credit card information, financial profiles, identification number and e-mail address are also available with us and are always confidential</p>

            <h3 class="mt-4">Terms Of Our Privacy Policy</h3>

            <h3 class="mt-4">Personal Information That We collect</h3>
            <p>Necessary information is collected for becoming a subscriber or member of our website. Our system collects the IP address of your computer automatically. But this detail does not give information about any particular person. But VIJAYA KARADANTU website doesn't collect information about children</p>

            <h3 class="mt-4">Use of The information Collected</h3>
            <p>Necessary information is collected for becoming a subscriber or member of our website.</p>
            
            <ul class="list-unstyled ms-3">
                <li><i class="bi bi-check-circle-fill me-2 text-danger"></i>Send news about the website</li>
                <li><i class="bi bi-check-circle-fill me-2 text-danger"></i>Calculate the number of visitors</li>
                <li><i class="bi bi-check-circle-fill me-2 text-danger"></i>Monitor the website</li>
                <li><i class="bi bi-check-circle-fill me-2 text-danger"></i>Know the geographical location of the users</li>
                <li><i class="bi bi-check-circle-fill me-2 text-danger"></i>Contact to give information about the website.</li>
                <li><i class="bi bi-check-circle-fill me-2 text-danger"></i>Give a better shopping experience online</li>
                <li><i class="bi bi-check-circle-fill me-2 text-danger"></i>Update about the recent offers on the website</li>
            </ul>

            <p class="mt-4">Personal Information such as address and contact details may be shared with courier partners and vendors only to process and deliver orders. This information helps VIJAYA KARADANTU fulfill order requirements while ensuring that private data is protected from unauthorized access. However, the company may disclose certain information such as name, city, state, phone number, email address, or user activity if required by law, regulation, or government authorities during an investigation. Cookies may be used to enhance the browsing experience and track website usage, but they do not store personal details like name, email address, phone number, or postal address. VIJAYA KARADANTU may share general website statistics or demographic information with sponsors, advertisers, or partners, but personal user information is never shared with third parties. The website may also contain links to external websites, and once users leave the VIJAYA KARADANTU website, this privacy policy will no longer apply.</p>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
