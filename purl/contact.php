<?php
// Purl support form handler
// Sends form submissions to info@qubillc.com

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /purl/support');
    exit;
}

// Honeypot check — bots fill hidden fields, humans don't
if (!empty($_POST['website'])) {
    header('Location: /purl/support?sent=1');
    exit;
}

// Sanitize inputs
$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Basic validation
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    header('Location: /purl/support?error=validation');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: /purl/support?error=email');
    exit;
}

// Sanitize for email headers (prevent header injection)
$name    = str_replace(["\r", "\n"], '', htmlspecialchars($name,    ENT_QUOTES, 'UTF-8'));
$subject = str_replace(["\r", "\n"], '', htmlspecialchars($subject, ENT_QUOTES, 'UTF-8'));
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

$to          = 'info@qubillc.com';
$mailSubject = "Purl Support: {$subject}";

$body = <<<EOT
New support message from the Purl website.

Name:    {$name}
Email:   {$email}
Topic:   {$subject}

Message:
{$message}

---
Sent via qubi.com/purl/support
EOT;

$headers  = "From: Purl Support <noreply@qubi.com>\r\n";
$headers .= "Reply-To: {$name} <{$email}>\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

if (mail($to, $mailSubject, $body, $headers)) {
    header('Location: /purl/support?sent=1');
} else {
    header('Location: /purl/support?error=mail');
}
exit;
