<?php

// Striver – Utloggning

session_start();

// Ta bort all information från sessionen
$_SESSION = [];

// Ta bort sessionscookien från webbläsaren
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax'
    ]);
}

// Avsluta sessionen
session_destroy();

// Skicka användaren till inloggningssidan
header('Location: login.php');
exit;