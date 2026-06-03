<?php
declare(strict_types=1);

namespace App\Service;

/**
 * Sweets Website
 * =============================================================
 * File: EmailService.php
 * Description: Core service for handling outbound communications.
 *              Uses PHPMailer for reliable SMTP delivery.
 * =============================================================
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    
    private string $fromEmail;
    private string $siteName;
    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUsername;
    private string $smtpPassword;
    private string $smtpSecure;
    private int $smtpTimeout = 20;

    public function __construct() {
        $this->fromEmail = defined('SMTP_FROM_EMAIL') && SMTP_FROM_EMAIL !== ''
            ? SMTP_FROM_EMAIL
            : (defined('SITE_EMAIL') ? SITE_EMAIL : 'noreply@vijayakaradant.in');
        $this->siteName = defined('SMTP_FROM_NAME') && SMTP_FROM_NAME !== ''
            ? SMTP_FROM_NAME
            : (defined('SITE_NAME') ? SITE_NAME : 'Vijaya Karadant');
        $this->smtpHost = defined('SMTP_HOST') ? trim((string)SMTP_HOST) : '';
        $this->smtpPort = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;
        $this->smtpUsername = defined('SMTP_USERNAME') ? (string)SMTP_USERNAME : '';
        $this->smtpPassword = defined('SMTP_PASSWORD') ? (string)SMTP_PASSWORD : '';
        $this->smtpSecure = defined('SMTP_SECURE') ? strtolower(trim((string)SMTP_SECURE)) : 'tls';
    }

    /**
     * Send Password Reset OTP
     */
    public function sendPasswordResetOTP(string $toEmail, string $otp, string $resetLink): bool {
        $subject = "Password Reset Code - " . $this->siteName;
        
        $message = "
        <html>
        <head>
            <title>Password Reset</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                <h2 style='color: #6B1515;'>Password Reset Request</h2>
                <p>Hello,</p>
                <p>We received a request to reset your password for your <strong>{$this->siteName}</strong> account.</p>
                <p>Please use the following verification code to proceed:</p>
                <div style='background: #f9f9f9; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #6B1515; border-radius: 5px; margin: 20px 0;'>
                    {$otp}
                </div>
                <p>Alternatively, you can click the link below to reset your password directly:</p>
                <p><a href='{$resetLink}' style='color: #C0392B; text-decoration: underline;'>Reset Password Link</a></p>
                <p>This code and link will expire in 15 minutes.</p>
                <p>If you did not request this, please ignore this email or contact support if you have concerns.</p>
                <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='font-size: 12px; color: #888;'>This is an automated message, please do not reply.</p>
            </div>
        </body>
        </html>
        ";

        return $this->sendHtmlEmail($toEmail, $subject, $message);
    }

    /**
     * Send Contact Form Notification to Admin
     */
    public function sendContactNotification(string $name, string $email, string $phone, string $message): bool {
        $subject = "New Contact Message from " . $name;
        $adminEmail = $this->fromEmail; 
        
        $htmlContent = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                <h2 style='color: #6B1515;'>New Website Inquiry</h2>
                <p>You have received a new message from the contact form:</p>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>Name:</strong></td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>{$name}</td></tr>
                    <tr><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>Email:</strong></td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>{$email}</td></tr>
                    <tr><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>Phone:</strong></td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>{$phone}</td></tr>
                </table>
                <p><strong>Message:</strong></p>
                <div style='background: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid #6B1515;'>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
                <p style='font-size: 12px; color: #888; margin-top: 20px;'>Sent from Vijaya Karadant Contact Form</p>
            </div>
        </body>
        </html>
        ";

        return $this->sendHtmlEmail($adminEmail, $subject, $htmlContent);
    }

    /**
     * Internal helper for sending HTML emails (Made public for general use)
     */
    public function sendHtmlEmail(string $to, string $subject, string $htmlContent): bool {
        // Use Elastic Email API if configured
        if (defined('ELASTIC_EMAIL_API_KEY') && !empty(ELASTIC_EMAIL_API_KEY)) {
            return $this->sendViaElasticEmail($to, $subject, $htmlContent);
        }

        // Use Resend API if API key is configured
        if (defined('RESEND_API_KEY') && !empty(RESEND_API_KEY)) {
            return $this->sendViaResend($to, $subject, $htmlContent);
        }

        if ($this->smtpHost !== '') {
            return $this->sendViaSmtp($to, $subject, $htmlContent);
        }

        return $this->sendViaNativeMail($to, $subject, $htmlContent);
    }

    /**
     * Send email using Resend HTTP API
     */
    private function sendViaResend(string $to, string $subject, string $htmlContent): bool {
        $apiKey = RESEND_API_KEY;
        
        // If domain is not verified in Resend, sending might fail. 
        // Temporarily forcing onboarding@resend.dev so the user can test with their registered email.
        $senderEmail = 'onboarding@resend.dev';
        $fromString = "{$this->siteName} <{$senderEmail}>";

        $url = 'https://api.resend.com/emails';
        
        $data = [
            'from'    => $fromString,
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $htmlContent
        ];

        $ch = curl_init($url);
        $payload = json_encode($data);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if(curl_errno($ch)){
            error_log('[EmailService] Resend cURL Error: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        } else {
            error_log("[EmailService] Resend API Error (HTTP {$httpCode}): " . $response);
            return false;
        }
    }

    /**
     * Send email using Elastic Email HTTP API
     */
    private function sendViaElasticEmail(string $to, string $subject, string $htmlContent): bool {
        $apiKey = ELASTIC_EMAIL_API_KEY;
        $url = 'https://api.elasticemail.com/v2/email/send';
        
        $data = [
            'apikey' => $apiKey,
            'subject' => $subject,
            'from' => $this->fromEmail,
            'fromName' => $this->siteName,
            'to' => $to,
            'bodyHtml' => $htmlContent,
            'isTransactional' => 'true'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            error_log('[EmailService] ElasticEmail cURL Error: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }
        curl_close($ch);

        $result = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300 && isset($result['success']) && $result['success'] == true) {
            return true;
        } else {
            error_log("[EmailService] ElasticEmail API Error: " . $response);
            return false;
        }
    }

    /**
     * Send through configured SMTP using PHPMailer.
     */
    private function sendViaSmtp(string $to, string $subject, string $htmlContent): bool {
        if (!filter_var($this->fromEmail, FILTER_VALIDATE_EMAIL)) {
            error_log('[EmailService] SMTP_FROM_EMAIL is invalid. Email was not sent.');
            return false;
        }

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log('[EmailService] Recipient email is invalid. Email was not sent.');
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->smtpHost;
            $mail->SMTPAuth   = ($this->smtpUsername !== '');
            $mail->Username   = $this->smtpUsername;
            $mail->Password   = $this->smtpPassword;
            
            if ($this->smtpSecure === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($this->smtpSecure === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }
            
            $mail->Port       = $this->smtpPort;
            $mail->Timeout    = $this->smtpTimeout;

            // Recipients
            $mail->setFrom($this->fromEmail, $this->siteName);
            $mail->addAddress($to);
            $mail->addReplyTo($this->fromEmail, $this->siteName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlContent;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("[EmailService] PHPMailer Error: {$mail->ErrorInfo}");
            return false;
        } catch (\Throwable $e) {
            error_log('[EmailService] SMTP send failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fallback for environments that have not configured SMTP.
     */
    private function sendViaNativeMail(string $to, string $subject, string $htmlContent): bool {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . $this->siteName . " <" . $this->fromEmail . ">" . "\r\n";
        $headers .= "Reply-To: " . $this->fromEmail . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        try {
            // Note: On live servers, mail() requires a working MTA (like Sendmail/Postfix)
            // or correct configuration in php.ini
            $success = mail($to, $subject, $htmlContent, $headers);
            
            if (!$success) {
                error_log("[EmailService] Failed to send email to $to. Check server mail configuration.");
            }
            
            return $success;
        } catch (\Exception $e) {
            error_log("[EmailService] Exception during email send: " . $e->getMessage());
            return false;
        }
    }

    // Removed legacy native socket helper methods
}
