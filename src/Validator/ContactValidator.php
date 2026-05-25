<?php
declare(strict_types=1);

namespace App\Validator;
use App\DTO\ContactRequestDTO;
use InvalidArgumentException;

class ContactValidator {
    public function validate(array $input): ContactRequestDTO {
        // Enforce Time-To-Fill Rule
        $formLoadTime = (int)($input['form_loaded_at'] ?? 0);
        if (time() - $formLoadTime < 3 && $formLoadTime !== 0) {
            throw new InvalidArgumentException("Form submitted too quickly (Bot suspected).");
        }

        $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new InvalidArgumentException("Invalid email format.");
        }

        // Check DNS MX records safely
        $domain = substr(strrchr($email, "@"), 1);
        if ($domain !== false && function_exists('checkdnsrr')) {
            if (!checkdnsrr($domain, "MX")) {
                throw new InvalidArgumentException("Unreachable email domain specified.");
            }
        }

        $name = trim($input['full_name'] ?? '');
        if (strlen($name) < 2) {
            throw new InvalidArgumentException("Name must be at least 2 characters.");
        }

        return new ContactRequestDTO(
            name: htmlspecialchars($name, ENT_QUOTES),
            email: $email,
            phone: preg_replace('/[^0-9\+]/', '', $input['phone'] ?? ''),
            message: htmlspecialchars(trim($input['message'] ?? ''), ENT_QUOTES),
            ipAddress: $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            userAgent: $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        );
    }
}
