<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../functions/phpmailer/Exception.php';
require '../functions/phpmailer/PHPMailer.php';
require '../functions/phpmailer/SMTP.php';

$response = [
    'success' => false,
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $surname = htmlspecialchars(trim($_POST['surname'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    if ($name && $surname && $email && $message) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.sendgrid.net';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'apikey';
            $mail->Password   = '';
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 2525 ;

            $mail->setFrom('vbcentrs@gmail.com', 'VB Centrs Website');
            $mail->addAddress('vbcentrs@gmail.com');
            $mail->addReplyTo($email, $name . ' ' . $surname);

            $mail->isHTML(true);
            $mail->Subject = 'Jauns ziņojums no kontaktformas';
            $mail->Body = "
                <h2>Jauns ziņojums no vietnes</h2>
                <p><strong>Vārds:</strong> $name</p>
                <p><strong>Uzvārds:</strong> $surname</p>
                <p><strong>E-pasts:</strong> $email</p>
                <p><strong>Ziņojums:</strong><br>" . nl2br($message) . "</p>
            ";

            $mail->send();
            $response['success'] = true;
            $response['message'] = 'Paldies! Ziņojums tika nosūtīts.';
        } catch (Exception $e) {
            $response['message'] = "Neizdevās nosūtīt ziņojumu";
        }
    } else {
        $response['message'] = 'Lūdzu, aizpildiet visus laukus pareizi.';
    }
} else {
    $response['message'] = 'Nederīgs pieprasījuma veids.';
}


header('Content-Type: application/json');
echo json_encode($response);
exit;
