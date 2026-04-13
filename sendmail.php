<?php
// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer (update paths if different)
require __DIR__ . '/PHPMailer/PHPMailer-6.10.0/src/Exception.php';
require __DIR__ . '/PHPMailer/PHPMailer-6.10.0/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/PHPMailer-6.10.0/src/SMTP.php';

// Function to send notification emails
function sendNotification($toEmail, $toName, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bandekarharsh124@gmail.com';   // ✅ your Gmail
        $mail->Password   = 'alsrhrmkbohtclcz';             // ✅ App Password (NO space)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // ✅ SSL fix for localhost
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        // Sender & recipient
        $mail->setFrom('bandekarharsh124@gmail.com', 'Synergy System');
        $mail->addAddress($toEmail, $toName);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);

        // Debug (uncomment if needed)
        // $mail->SMTPDebug = 2;

        $mail->send();
        return true;

    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}"; // ✅ show error in browser
        return false;
    }
}
