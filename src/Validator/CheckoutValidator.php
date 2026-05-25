<?php
declare(strict_types=1);

namespace App\Validator;

use InvalidArgumentException;

class CheckoutValidator {
    
    private array $errors = [];
    
    /**
     * Validate Checkout Form Data
     */
    public function validate(array $data): array {
        $this->errors = [];
        
        // ✅ Email Validation
        $email = trim($data['email'] ?? '');
        if (empty($email)) {
            $this->errors['email'] = 'Email address is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Please enter a valid email (e.g., user@example.com)';
        }
        
        // ✅ First Name Validation
        $firstName = trim($data['first_name'] ?? '');
        if (empty($firstName)) {
            $this->errors['first_name'] = 'First name is required';
        } elseif (strlen($firstName) < 2) {
            $this->errors['first_name'] = 'First name must be at least 2 characters';
        } elseif (strlen($firstName) > 50) {
            $this->errors['first_name'] = 'First name cannot exceed 50 characters';
        } elseif (!preg_match('/^[a-zA-Z\s\'-]+$/', $firstName)) {
            $this->errors['first_name'] = 'First name can only contain letters, spaces, hyphens, and apostrophes';
        }
        
        // ✅ Last Name Validation
        $lastName = trim($data['last_name'] ?? '');
        if (empty($lastName)) {
            $this->errors['last_name'] = 'Last name is required';
        } elseif (strlen($lastName) < 2) {
            $this->errors['last_name'] = 'Last name must be at least 2 characters';
        } elseif (strlen($lastName) > 50) {
            $this->errors['last_name'] = 'Last name cannot exceed 50 characters';
        } elseif (!preg_match('/^[a-zA-Z\s\'-]+$/', $lastName)) {
            $this->errors['last_name'] = 'Last name can only contain letters, spaces, hyphens, and apostrophes';
        }
        
        // ✅ Phone Number Validation
        $phoneInput = $data['phone'] ?? '';
        $phone = preg_replace('/[^0-9]/', '', (string)$phoneInput);
        $country = $data['country'] ?? 'India';

        if (empty($phone)) {
            $this->errors['phone'] = 'Phone number is required';
        } else {
            if ($country === 'India') {
                if (strlen($phone) !== 10) {
                    $this->errors['phone'] = 'Phone number must be exactly 10 digits';
                } elseif (!preg_match('/^[6-9]/', $phone)) {
                    $this->errors['phone'] = 'Phone number must start with 6-9';
                }
            } else {
                if (strlen($phone) < 7 || strlen($phone) > 15) {
                    $this->errors['phone'] = 'Please enter a valid international phone number';
                }
            }
        }
        
        // ✅ Address Validation
        $address = trim($data['address'] ?? '');
        if (empty($address)) {
            $this->errors['address'] = 'Address is required';
        } elseif (strlen($address) < 5) {
            $this->errors['address'] = 'Address must be at least 5 characters';
        }
        
        // ✅ City Validation
        $city = trim($data['city'] ?? '');
        if (empty($city)) {
            $this->errors['city'] = 'City is required';
        }
        
        // ✅ State Validation
        $state = trim($data['state'] ?? '');
        if (empty($state)) {
            $this->errors['state'] = 'State is required';
        }
        
        // ✅ PIN Code Validation
        $pincodeInput = $data['pin_code'] ?? '';
        $pincode = preg_replace('/[^a-zA-Z0-9]/', '', (string)$pincodeInput);
        if (empty($pincode)) {
            $this->errors['pin_code'] = 'PIN/Postal code is required';
        } else {
            if ($country === 'India') {
                if (strlen($pincode) !== 6 || !is_numeric($pincode)) {
                    $this->errors['pin_code'] = 'PIN code must be exactly 6 digits';
                }
            } else {
                if (strlen($pincode) < 3 || strlen($pincode) > 10) {
                    $this->errors['pin_code'] = 'Please enter a valid postal code';
                }
            }
        }
        
        // ✅ Throw error if validation fails
        if (!empty($this->errors)) {
            throw new InvalidArgumentException(
                json_encode($this->errors)
            );
        }
        
        // ✅ Return cleaned data
        return [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'pin_code' => $pincode,
        ];
    }
}
