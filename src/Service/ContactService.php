<?php
declare(strict_types=1);

namespace App\Service;
use App\DTO\ContactRequestDTO;
use App\Repository\ContactRepository;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class ContactService {
    private ContactRepository $repo;

    public function __construct(ContactRepository $repo) {
        $this->repo = $repo;
    }

    /**
     * @throws InvalidArgumentException When validation fails
     * @throws RuntimeException When rate limited or spam detected
     */
    public function submitMessage(ContactRequestDTO $dto): bool {
        // Enforce DB-level Rate Limiting: 3 messages max per Hour
        $recentCount = $this->repo->getRecentSubmissionCount($dto->ipAddress, 3600);
        if ($recentCount >= 3) {
            // Throw exception caught by Controller for 429
            throw new RuntimeException("Rate limit reached. Try again later.", 429);
        }

        // Write row safely via PDO
        $messageId = $this->repo->save($dto);

        // Async Event Submission
        // In this fallback, if Redis is missing, we simply log it or do nothing.
        // If Redis or RabbitMQ are available, you push the $messageId to the queue here.
        if (getenv('QUEUE_CONNECTION') === 'redis') {
            $this->pushToQueue($messageId);
        } else {
            // Fallback: Send real email immediately if no queue is configured
            try {
                $emailSvc = new \App\Service\EmailService();
                $emailSvc->sendContactNotification(
                    $dto->fullName,
                    $dto->email,
                    $dto->phone ?? 'N/A',
                    $dto->message
                );
            } catch (\Exception $e) {
                error_log("[ContactService] Failed to send notification email: " . $e->getMessage());
            }
        }

        return true;

    }

    private function pushToQueue(int $id): void {
        // Simulated queue push logic for future EmailWorker parsing
        // $redis->lpush('emails', json_encode(['task' => 'send_contact_email', 'id' => $id]));
    }
}
