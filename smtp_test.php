<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php'; // adjust if your PHPMailer path is different

$smtpHost = 'mail.newportal.recyclingmanagement.com';
$smtpPort = 465;
$smtpUser = 'customerportal@newportal.recyclingmanagement.com';
$smtpPass = 'RMLScrapman169';
$fromEmail = 'customerportal@newportal.recyclingmanagement.com';
$fromName = 'SMTP Test';
$toEmail = 'peteriteka@gmail.com';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $smtpHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUser;
    $mail->Password   = $smtpPass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // <-- Use SMTPS for port 465
    $mail->Port       = $smtpPort;

    $mail->setFrom($fromEmail, $fromName);
    $mail->addAddress($toEmail);

    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test Email';
    $mail->Body    = '<p>This is a test email from your SMTP server configuration.</p>';

    $mail->send();
    echo "Success: Test email sent to {$toEmail}";
} catch (Exception $e) {
    echo "Error: SMTP test failed. Mailer Error: {$mail->ErrorInfo}";
}

