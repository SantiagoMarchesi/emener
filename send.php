<?php
// send.php
header('Content-Type: application/json; charset=utf-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

// ---- Recibir y validar ----
$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$phone   = trim($_POST['phone']   ?? '');
$message = trim($_POST['message'] ?? '');
$consent = isset($_POST['consent']) ? 'Sí' : 'No';

if ($name === '' || $email === '') {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'Completá tu nombre y email.']);
  exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'El email no es válido.']);
  exit;
}
if ($consent !== 'Sí') {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'Debés aceptar el uso de datos para enviar.']);
  exit;
}

// ---- SMTP Gmail (recomendado usar Contraseña de aplicación) ----
$SMTP_HOST = 'smtp.gmail.com';
$SMTP_USER = 'ventas.emenersrl@gmail.com';     // cuenta emisora (Gmail)
$SMTP_PASS = 'TU_CONTRASENA_DE_APLICACION';    // <-- reemplazar por la contraseña de aplicación
$SMTP_PORT = 587;

$TO_EMAIL  = 'ventas.emenersrl@gmail.com';     // destinatario final
$TO_NAME   = 'Ventas EMENER';

$mail = new PHPMailer(true);

try {
  $mail->isSMTP();
  $mail->Host       = $SMTP_HOST;
  $mail->SMTPAuth   = true;
  $mail->Username   = $SMTP_USER;
  $mail->Password   = $SMTP_PASS;
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port       = $SMTP_PORT;
  $mail->CharSet    = 'UTF-8';

  $mail->setFrom($SMTP_USER, 'Web EMENER');
  $mail->addAddress($TO_EMAIL, $TO_NAME);
  $mail->addReplyTo($email, $name);

  $mail->isHTML(true);
  $mail->Subject = 'Consulta desde la web';

  $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
  $safeName    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
  $safeEmail   = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
  $safePhone   = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');

  $mail->Body = "
    <h2>Nueva consulta del sitio</h2>
    <p><strong>Nombre:</strong> {$safeName}</p>
    <p><strong>Email:</strong> {$safeEmail}</p>
    <p><strong>Teléfono:</strong> {$safePhone}</p>
    <p><strong>Consentimiento:</strong> Sí</p>
    <hr>
    <p><strong>Mensaje:</strong><br>{$safeMessage}</p>
  ";

  $mail->AltBody = "Nueva consulta del sitio\n\n"
                 . "Nombre: {$name}\n"
                 . "Email: {$email}\n"
                 . "Teléfono: {$phone}\n"
                 . "Consentimiento: Sí\n\n"
                 . "Mensaje:\n{$message}\n";

  $mail->send();
  echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => 'No se pudo enviar el mensaje.']);
}

