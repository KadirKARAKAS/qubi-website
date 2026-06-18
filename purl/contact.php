<?php
// Purl support form handler
// Sends form submissions to info@qubillc.com

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
       && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function jsonOut(bool $success, string $error = ''): void {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($success ? ['success' => true] : ['success' => false, 'error' => $error]);
    exit;
}

function redirectOut(string $location): void {
    header('Location: ' . $location);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectOut('/purl/support');
}

// Honeypot — bots fill hidden fields, humans don't
if (!empty($_POST['website'])) {
    $isAjax ? jsonOut(true) : redirectOut('/purl/support?sent=1');
}

$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    $isAjax ? jsonOut(false, 'validation') : redirectOut('/purl/support?error=validation');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $isAjax ? jsonOut(false, 'email') : redirectOut('/purl/support?error=email');
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
Sent via qubi.com/purl
EOT;

$headers  = "From: Purl Support <noreply@qubi.com>\r\n";
$headers .= "Reply-To: {$name} <{$email}>\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

if (mail($to, $mailSubject, $body, $headers)) {
    $isAjax ? jsonOut(true) : redirectOut('/purl/support?sent=1');
} else {
    $isAjax ? jsonOut(false, 'mail') : redirectOut('/purl/support?error=mail');
}
