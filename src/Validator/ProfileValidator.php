<?php
declare(strict_types=1);

namespace App\Validator;

use InvalidArgumentException;

class ProfileValidator {
    
    private array $errors = [];
    
    /**
     * Validate Profile Form Data
     */
    public function validate(array $data): array {
        $this->errors = [];
        
        // Full Name Validation
        $fullName = trim($data['full_name'] ?? '');
        if (empty($fullName)) {
            $this->errors['full_name'] = 'Full name is required';
        } elseif (strlen($fullName) < 2) {
            $this->errors['full_name'] = 'Name must be at least 2 characters';
        }

        // Email Validation
        $email = trim($data['email'] ?? '');
        if (empty($email)) {
            $this->errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Invalid email format';
        }

        // Phone Validation
        $phone = preg_replace('/[^0-9]/', '', $data['phone'] ?? '');
        if (!empty($phone) && strlen($phone) < 10) {
            $this->errors['phone'] = 'Phone number must be at least 10 digits';
        }

        // New Password Validation (if provided)
        $newPassword = $data['new_password'] ?? '';
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 8) {
                $this->errors['new_password'] = 'Password must be at least 8 characters';
            } elseif (!preg_match('/[A-Z]/', $newPassword)) {
                $this->errors['new_password'] = 'Password must include an uppercase letter';
            } elseif (!preg_match('/[0-9]/', $newPassword)) {
                $this->errors['new_password'] = 'Password must include a number';
            } elseif (!preg_match('/[^A-Za-z0-9]/', $newPassword)) {
                $this->errors['new_password'] = 'Password must include a special character';
            }
            
            $confirmPassword = $data['confirm_password'] ?? '';
            if ($newPassword !== $confirmPassword) {
                $this->errors['confirm_password'] = 'Passwords do not match';
            }
        }

        if (!empty($this->errors)) {
            throw new InvalidArgumentException(json_encode($this->errors));
        }

        return [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'alternate_phone' => preg_replace('/[^0-9]/', '', $data['alternate_phone'] ?? ''),
            'dob' => $data['dob'] ?? '',
            'gender' => $data['gender'] ?? '',
            'marketing_opt_in' => isset($data['marketing_opt_in']) ? 1 : 0,
            'new_password' => $newPassword
        ];
    }
}
