<?php
declare(strict_types=1);

namespace App\DTO;

class ContactRequestDTO {
    public string $name;
    public string $email;
    public string $phone;
    public string $message;
    public string $ipAddress;
    public string $userAgent;

    public function __construct(
        string $name,
        string $email,
        string $phone,
        string $message,
        string $ipAddress,
        string $userAgent
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->message = $message;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }
}
