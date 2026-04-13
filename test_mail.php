<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/PHPMailer-6.10.0/src/Exception.php';
require __DIR__ . '/PHPMailer/PHPMailer-6.10.0/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/PHPMailer-6.10.0/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'janwalkarsamruddhi@gmail.com';   // 👉 replace
    $mail->Password   = 'apaitqxhutsczdkk';      // 👉 replace
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // ✅ Bypass SSL certificate check (fix for local dev)
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];

    $mail->setFrom('your_email@gmail.com', 'Synergy System');
    $mail->addAddress('recipient@example.com', 'Test User'); // 👉 replace with your email

    $mail->isHTML(true);
    $mail->Subject = 'Test Email from Synergy System';
    $mail->Body    = '<h2>This is a test email 🚀</h2><p>If you see this, PHPMailer is working!</p>';

    $mail->send();
    echo "✅ Test email sent successfully!";
} catch (Exception $e) {
    echo "❌ Failed to send test email. Error: {$mail->ErrorInfo}";
}

