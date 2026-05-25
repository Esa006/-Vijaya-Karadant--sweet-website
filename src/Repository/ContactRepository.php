<?php
declare(strict_types=1);

namespace App\Repository;
use App\DTO\ContactRequestDTO;
use PDO;

class ContactRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function save(ContactRequestDTO $dto): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO contact_messages 
            (full_name, email, phone, message, ip_address, user_agent, status) 
            VALUES (:name, :email, :phone, :msg, :ip, :ua, 'unread')
        ");
        
        $stmt->execute([
            ':name' => $dto->name,
            ':email' => $dto->email,
            ':phone' => $dto->phone,
            ':msg' => $dto->message,
            ':ip' => $dto->ipAddress,
            ':ua' => $dto->userAgent
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function getRecentSubmissionCount(string $ip, int $timeframeSeconds): int {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(id) FROM contact_messages 
            WHERE ip_address = :ip AND created_at >= (NOW() - INTERVAL :s SECOND)
        ");
        
        // PARAM_INT tells PDO to sanitize explicitly as numeric
        $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
        $stmt->bindValue(':s', $timeframeSeconds, PDO::PARAM_INT);
        $stmt->execute();
        
        return (int)$stmt->fetchColumn();
    }
}
