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
        // Fallback for missing constant
        if (!defined('RESEND_API_KEY') || empty(RESEND_API_KEY)) {
            error_log("EmailService Error: RESEND_API_KEY is not configured.");
            return false;
        }

        $apiKey = RESEND_API_KEY;
        
        // Sender address construction
        $fromEmail = defined('SMTP_FROM_EMAIL') && !empty(SMTP_FROM_EMAIL) ? SMTP_FROM_EMAIL : 'onboarding@resend.dev';
        $defaultFromName = defined('SMTP_FROM_NAME') && !empty(SMTP_FROM_NAME) ? SMTP_FROM_NAME : 'Sweets Website';
        $senderName = $fromName ?: $defaultFromName;

        // If they haven't verified a domain on Resend, they must use onboarding@resend.dev as the 'from' address
        // Assuming they have verified it, but if they face errors, they might need to change $fromEmail.
        $fromString = "{$senderName} <{$fromEmail}>";

        $url = 'https://api.resend.com/emails';
        
        $data = [
            'from'    => $fromString,
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $htmlBody
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
        
        // Optional: Disable SSL verification for local dev environments if needed, but keeping it secure by default
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if(curl_errno($ch)){
            error_log('EmailService cURL Error: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        } else {
            error_log("EmailService Resend API Error (HTTP {$httpCode}): " . $response);
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
