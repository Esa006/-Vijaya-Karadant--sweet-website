<?php
declare(strict_types=1);

namespace App\DTO;

readonly class ContactRequestDTO {
    public function __construct(
        public string $name,
        public string $email,
        public string $phone,
        public string $message,
        public string $ipAddress,
        public string $userAgent
    ) {}
}
