<?php
/**
 * Sweets Website
 * =============================================================
 * File: address-handler.php
 * Description: API to handle shipping address CRUD actions
 * =============================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/AddressService.php';

// For prototype, use default current user
$userId = $_SESSION['user_id'] ?? 1;
$addressService = new AddressService();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$requestedRedirect = trim((string)($_POST['redirect'] ?? $_GET['redirect'] ?? 'saved-addresses.php'));
$allowedRedirects = ['saved-addresses.php', 'profile.php'];
$redirectPage = in_array($requestedRedirect, $allowedRedirects, true) ? $requestedRedirect : 'saved-addresses.php';
$redirectUrl = '../' . $redirectPage;

try {
    switch ($action) {
        case 'add':
            $data = [
                'user_id' => $userId,
                'recipient_name' => $_POST['recipient_name'],
                'type' => $_POST['type'] ?? 'shipping',
                'address_line1' => $_POST['address_line1'],
                'address_line2' => $_POST['address_line2'] ?? '',
                'city' => $_POST['city'],
                'state' => $_POST['state'],
                'zip_code' => $_POST['zip_code'],
                'country' => $_POST['country'] ?? 'India',
                'phone' => $_POST['phone'],
                'is_default' => isset($_POST['is_default']) ? 1 : 0
            ];
            
            if ($addressService->addAddress($data)) {
                $_SESSION['address_success'] = "Address added successfully!";
            } else {
                $_SESSION['address_error'] = "Failed to add address.";
            }
            break;

        case 'edit':
            $id = (int)$_POST['id'];
            $data = [
                'user_id' => $userId,
                'recipient_name' => $_POST['recipient_name'],
                'type' => $_POST['type'] ?? 'shipping',
                'address_line1' => $_POST['address_line1'],
                'address_line2' => $_POST['address_line2'] ?? '',
                'city' => $_POST['city'],
                'state' => $_POST['state'],
                'zip_code' => $_POST['zip_code'],
                'country' => $_POST['country'] ?? 'India',
                'phone' => $_POST['phone'],
                'is_default' => isset($_POST['is_default']) ? 1 : 0
            ];

            if ($addressService->updateAddress($id, $data)) {
                $_SESSION['address_success'] = "Address updated successfully!";
            } else {
                $_SESSION['address_error'] = "Failed to update address.";
            }
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? $_GET['id']);
            if ($addressService->deleteAddress($id, $userId)) {
                $_SESSION['address_success'] = "Address deleted successfully!";
            } else {
                $_SESSION['address_error'] = "Failed to delete address.";
            }
            break;

        case 'setDefault':
            $id = (int)($_POST['id'] ?? $_GET['id']);
            if ($addressService->setDefault($id, $userId)) {
                $_SESSION['address_success'] = "Default address updated!";
            } else {
                $_SESSION['address_error'] = "Failed to update default address.";
            }
            break;

        default:
            $_SESSION['address_error'] = "Invalid action.";
            break;
    }
} catch (Exception $e) {
    $_SESSION['address_error'] = "Error: " . $e->getMessage();
}

header("Location: $redirectUrl");
exit;
