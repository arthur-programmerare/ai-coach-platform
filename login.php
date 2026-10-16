<?php


// Striver – Inloggning

session_start();

require_once __DIR__ . '/db.php';

// Om användaren redan är inloggad
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Skapa en CSRF-token
if (empty($_SESSION['csrf_login'])) {
    $_SESSION['csrf_login'] = bin2hex(random_bytes(32));
}

// Funktion för att visa text säkert i HTML
function e(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$fel = '';
$epost = '';

// Kontrollera om formuläret har skickats
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $epost = strtolower(trim($_POST['epost'] ?? ''));
    $losenord = $_POST['losenord'] ?? '';
    $token = $_POST['csrf'] ?? '';

    // Kontrollera CSRF-token
    if (!hash_equals($_SESSION['csrf_login'], $token)) {
        $fel = 'Sessionen gick ut. Försök igen.';
    }

    // Kontrollera att fälten är ifyllda
    elseif (empty($epost) || empty($losenord)) {
        $fel = 'Fyll i både e-post och lösenord.';
    }

    // Kontrollera e-postadressen
    elseif (!filter_var($epost, FILTER_VALIDATE_EMAIL)) {
        $fel = 'Ange en giltig e-postadress.';
    }

    else {

        try {

            // Hämta användaren från databasen
            $stmt = $pdo->prepare(
                'SELECT id, name, password FROM users WHERE email = :email LIMIT 1'
            );

            $stmt->execute([
                ':email' => $epost
            ]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Kontrollera lösenordet
            if ($user && password_verify($losenord, $user['password'])) {

                // Skapa nytt sessions-ID
                session_regenerate_id(true);

                // Spara användarens information i sessionen
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];

                // Ta bort använd CSRF-token
                unset($_SESSION['csrf_login']);

                // Skicka användaren till dashboard
                header('Location: dashboard.php');
                exit;

            } else {
                $fel = 'Fel e-postadress eller lösenord.';
            }

        } catch (PDOException $ex) {
            $fel = 'Något gick fel. Försök igen senare.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Logga in – Striver</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h1>Logga in på Striver</h1>

    <?php if ($fel): ?>
        <p><?= e($fel) ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">

        <input
            type="hidden"
            name="csrf"
            value="<?= e($_SESSION['csrf_login']) ?>"
        >

        <label for="epost">E-postadress</label>
        <input
            type="email"
            id="epost"
            name="epost"
            value="<?= e($epost) ?>"
            required
        >

        <br><br>

        <label for="losenord">Lösenord</label>
        <input
            type="password"
            id="losenord"
            name="losenord"
            required
        >

        <br><br>

        <button type="submit">Logga in</button>

    </form>

    <p>
        Har du inget konto?
        <a href="register.php">Skapa konto</a>
    </p>

</body>
</html>