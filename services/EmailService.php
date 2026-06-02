<?php

namespace App\Services;

/**
 * Enterprise Email Service using Resend API
 */
class EmailService {

    /**
     * Send an email via Resend HTTP API using cURL
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $htmlBody HTML content of the email
     * @param string|null $fromName Optional sender name
     * @return bool True if successful, false otherwise
     */
    public static function send(string $to, string $subject, string $htmlBody, ?string $fromName = null): bool {
        // Use PHPMailer since standard SMTP is configured in .env
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : '';
            $mail->SMTPAuth   = true;
            $mail->Username   = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
            $mail->Password   = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
            
            $secureType = defined('SMTP_SECURE') ? strtolower(SMTP_SECURE) : 'tls';
            if ($secureType === 'tls') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($secureType === 'ssl') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }
            
            $mail->Port = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;

            // Sender address construction
            $fromEmail = defined('SMTP_FROM_EMAIL') && !empty(SMTP_FROM_EMAIL) ? SMTP_FROM_EMAIL : $mail->Username;
            $defaultFromName = defined('SMTP_FROM_NAME') && !empty(SMTP_FROM_NAME) ? SMTP_FROM_NAME : 'Sweets Website';
            $senderName = $fromName ?: $defaultFromName;

            $mail->setFrom($fromEmail, $senderName);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $htmlBody));

            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log("EmailService Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Send an Admin Invitation Email
     * 
     * @param string $to Recipient email address
     * @param string $token The secure invitation token
     * @return bool
     */
    public static function sendAdminInvite(string $to, string $token): bool {
        $loginUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/sweet-website/';
        $inviteLink = $loginUrl . "admin/accept-invite.php?token=" . urlencode($token);
        
        $subject = "You have been invited as an Admin!";
        
        $html = "
            <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                <h2 style='color: #8C3333; border-bottom: 2px solid #8C3333; padding-bottom: 10px;'>Admin Invitation</h2>
                <p>Hello,</p>
                <p>You have been invited to join the Admin team for our platform.</p>
                <p>Please click the button below to set up your secure password and activate your account. This link is valid for 24 hours.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$inviteLink}' style='background-color: #8C3333; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;'>Activate Account</a>
                </div>
                <p>If the button doesn't work, copy and paste this link into your browser:</p>
                <p style='word-break: break-all; color: #666; font-size: 12px;'>{$inviteLink}</p>
                <p style='margin-top: 40px; font-size: 12px; color: #999;'>If you didn't expect this invitation, please ignore this email.</p>
            </div>
        ";
        
        return self::send($to, $subject, $html);
    }
}
