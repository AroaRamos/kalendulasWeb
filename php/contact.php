<?php
// ============= CONFIGURA AQUÍ TU EMAIL DE RECEPCIÓN =============
$to = 'kalendulas.detalles@gmail.com';
// ================================================================

// 1) Aceptar solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Método no permitido');
}

// 2) Honeypot: si viene relleno, tratamos como bot pero respondemos OK
if (!empty($_POST['website'])) {
  header('Location: thank-you.html');
  exit;
}

// 3) Recoger y limpiar datos
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// 4) Validaciones básicas
$errors = [];
if ($name === '') { $errors[] = 'Falta el nombre.'; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Email no válido.'; }
if ($subject === '') { $errors[] = 'Falta el asunto.'; }
if ($message === '') { $errors[] = 'Falta el mensaje.'; }

if ($errors) {
  // Devuelve 400 + texto simple (suficiente para una práctica)
  http_response_code(400);
  echo implode(' ', $errors);
  exit;
}

// 5) Preparar correo
$subjectLine = "Contacto Kaléndulas · " . $subject;

$body = "Has recibido un mensaje desde el formulario de la web:\n\n"
      . "Nombre: $name\n"
      . "Email: $email\n"
      . "Asunto: $subject\n\n"
      . "Mensaje:\n$message\n\n"
      . "IP: " . ($_SERVER['REMOTE_ADDR'] ?? '') . "\n"
      . "User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? '') . "\n";

// Cabeceras: From del dominio del hosting + Reply-To del usuario
$host = $_SERVER['HTTP_HOST'] ?? 'tu-dominio';
$headers  = "From: Kaléndulas <no-reply@$host>\r\n";
$headers .= "Reply-To: $name <$email>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// 6) Enviar (mail nativo)
$ok = @mail($to, $subjectLine, $body, $headers);

if ($ok) {
  header('Location: thank-you.html'); // crea esta página simple de gracias
  exit;
} else {
  // Si el hosting no permite mail(), mostrar mensaje claro
  http_response_code(500);
  echo "No se pudo enviar el mensaje. Es posible que la función mail() esté desactivada en el hosting.";
  exit;
}
