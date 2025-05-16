<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

$mail = new PHPMailer(true);

try {
   
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'vbcentrs@gmail.com';            // Your Gmail
    $mail->Password   = 'coyuvcfqcozphndl';              // App password (no spaces)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Encryption
    $mail->Port       = 587;

    
    $mail->setFrom('vbcentrs@gmail.com', 'VBC Website');
    $mail->addAddress('vbcentrs@gmail.com');             // You can add another email here


    $mail->isHTML(true);
    $mail->Subject = 'Test Email from PHPMailer';
    $mail->Body    = 'This is a <b>test</b> email sent using <i>PHPMailer + Gmail SMTP</i>.';

    $mail->send();
    echo '✅ Message has been sent';
} catch (Exception $e) {
    echo "❌ Mailer Error";
}
