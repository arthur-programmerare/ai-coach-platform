<?php
// Striver – registrering
session_start();
require_once __DIR__ . '/db.php'; // ger $pdo

// Redan inloggad? Skicka vidare.
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if (empty($_SESSION['csrf_register'])) {
    $_SESSION['csrf_register'] = bin2hex(random_bytes(32));
}

function e(string $t): string
{
    return htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
}

$fel = [];
$namn = '';
$epost = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namn       = trim((string)($_POST['namn'] ?? ''));
    $epost      = strtolower(trim((string)($_POST['epost'] ?? '')));
    $losenord   = (string)($_POST['losenord'] ?? '');
    $bekrafta   = (string)($_POST['bekrafta'] ?? '');
    $token      = (string)($_POST['csrf'] ?? '');

    if (!hash_equals($_SESSION['csrf_register'], $token)) {
        $fel['allmant'] = 'Sessionen gick ut. Försök igen.';
    }

    if ($namn === '') {
        $fel['namn'] = 'Fyll i ditt namn.';
    } elseif (mb_strlen($namn) > 100) {
        $fel['namn'] = 'Namnet får vara högst 100 tecken.';
    }

    if ($epost === '') {
        $fel['epost'] = 'Fyll i din e-postadress.';
    } elseif (!filter_var($epost, FILTER_VALIDATE_EMAIL) || strlen($epost) > 255) {
        $fel['epost'] = 'Ange en giltig e-postadress.';
    }

    if ($losenord === '') {
        $fel['losenord'] = 'Välj ett lösenord.';
    } elseif (mb_strlen($losenord) < 8) {
        $fel['losenord'] = 'Lösenordet måste vara minst 8 tecken.';
    } elseif (strlen($losenord) > 72) {
        // bcrypt hanterar bara de första 72 byten
        $fel['losenord'] = 'Lösenordet får vara högst 72 tecken.';
    }

    if ($bekrafta === '') {
        $fel['bekrafta'] = 'Bekräfta ditt lösenord.';
    } elseif ($losenord !== $bekrafta) {
        $fel['bekrafta'] = 'Lösenorden matchar inte.';
    }

    // Finns e-postadressen redan?
    if (!isset($fel['epost']) && !isset($fel['allmant'])) {
        try {
            $kontroll = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $kontroll->execute([':email' => $epost]);
            if ($kontroll->fetch()) {
                $fel['epost'] = 'Det finns redan ett konto med den e-postadressen.';
            }
        } catch (PDOException $ex) {
            $fel['allmant'] = 'Något gick fel. Försök igen om en stund.';
        }
    }

    // Skapa användaren
    if (!$fel) {
        try {
            $hash = password_hash($losenord, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
            $insert->execute([
                ':name'     => $namn,
                ':email'    => $epost,
                ':password' => $hash,
            ]);

            $ny_id = (int)$pdo->lastInsertId();

            session_regenerate_id(true);
            $_SESSION['user_id']   = $ny_id;
            $_SESSION['user_name'] = $namn;
            unset($_SESSION['csrf_register']);

            header('Location: dashboard.php');
            exit;
        } catch (PDOException $ex) {
            // 23000 = unik nyckel (e-posten registrerades precis samtidigt)
            if ($ex->getCode() === '23000') {
                $fel['epost'] = 'Det finns redan ett konto med den e-postadressen.';
            } else {
                $fel['allmant'] = 'Kontot kunde inte skapas. Försök igen om en stund.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Skapa konto – Striver</title>
  <meta name="robots" content="noindex">
  <meta name="theme-color" content="#0a0b14">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    .auth { position: relative; overflow: hidden; padding: 4rem 0 5rem; min-height: calc(100vh - 72px - 110px); }
    .auth::before, .auth::after { content: ""; position: absolute; border-radius: 50%; filter: blur(120px); pointer-events: none; z-index: 0; }
    .auth::before { width: 480px; height: 480px; background: rgba(79, 124, 255, 0.24); top: -160px; left: -140px; }
    .auth::after { width: 440px; height: 440px; background: rgba(139, 92, 246, 0.22); bottom: -160px; right: -120px; }
    .auth .container { position: relative; z-index: 1; display: flex; justify-content: center; }
    .auth-card {
      width: 100%; max-width: 480px; padding: 2.5rem; border-radius: 28px; border: 1px solid var(--line);
      background: linear-gradient(180deg, var(--surface-2), var(--surface));
      box-shadow: 0 30px 80px -40px rgba(0, 0, 0, 0.8);
    }
    .auth-card h1 { font: 800 clamp(1.8rem, 5vw, 2.2rem)/1.15 var(--font-head); letter-spacing: -0.03em; margin: 0 0 0.6rem; }
    .auth-sub { color: var(--muted); margin: 0 0 2rem; }

    .falt { margin-bottom: 1.25rem; }
    .falt label { display: block; font-weight: 600; font-size: 0.95rem; margin-bottom: 0.45rem; }
    .falt input {
      width: 100%; padding: 0.9rem 1.1rem; border-radius: 14px; border: 1px solid var(--line);
      background: rgba(255, 255, 255, 0.04); color: var(--text); font: 500 1rem var(--font-body);
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .falt input::placeholder { color: #6f7499; }
    .falt input:focus { outline: none; border-color: var(--violet); box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.2); }
    .falt.har-fel input { border-color: rgba(248, 113, 113, 0.7); }
    .falt-fel { margin: 0.4rem 0 0; color: #fca5a5; font-size: 0.92rem; }
    .falt-tips { margin: 0.4rem 0 0; color: var(--muted); font-size: 0.9rem; }

    .fel-ruta { margin: 0 0 1.5rem; padding: 0.85rem 1.1rem; border-radius: 14px; background: rgba(248, 113, 113, 0.12); border: 1px solid rgba(248, 113, 113, 0.4); color: #fecaca; font-size: 0.98rem; }
    .auth-card .btn { width: 100%; margin-top: 0.5rem; }
    .auth-alt { text-align: center; color: var(--muted); margin: 1.75rem 0 0; font-size: 0.98rem; }
    .auth-alt a { color: #a5b4fc; font-weight: 600; }
    .auth-alt a:hover { color: #fff; }
    .auth-back { display: block; text-align: center; margin-top: 1rem; color: var(--muted); font-size: 0.95rem; }
    .auth-back:hover { color: var(--text); }

    @media (max-width: 600px) {
      .auth { padding: 2rem 0 3rem; }
      .auth-card { padding: 1.75rem 1.25rem; border-radius: 22px; }
    }
  </style>
</head>
<body>

<header class="nav">
  <div class="container nav-inner">
    <a href="index.php" class="logo" aria-label="Striver – startsida">
      <span class="logo-mark" aria-hidden="true">S</span>Striver
    </a>
    <div class="nav-actions">
      <a href="login.php" class="btn btn-ghost">Logga in</a>
    </div>
  </div>
</header>

<main class="auth">
  <div class="container">
    <div class="auth-card">
      <h1>Skapa ditt gratis konto</h1>
      <p class="auth-sub">Det tar under en minut. Sedan kan du fortsätta med din plan när du vill.</p>

      <?php if (isset($fel['allmant'])): ?>
        <p class="fel-ruta" role="alert"><?= e($fel['allmant']) ?></p>
      <?php endif; ?>

      <form method="post" action="register.php" novalidate>
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf_register']) ?>">

        <div class="falt<?= isset($fel['namn']) ? ' har-fel' : '' ?>">
          <label for="namn">Namn</label>
          <input type="text" id="namn" name="namn" value="<?= e($namn) ?>" maxlength="100" autocomplete="name" placeholder="Ditt namn" required>
          <?php if (isset($fel['namn'])): ?><p class="falt-fel"><?= e($fel['namn']) ?></p><?php endif; ?>
        </div>

        <div class="falt<?= isset($fel['epost']) ? ' har-fel' : '' ?>">
          <label for="epost">E-postadress</label>
          <input type="email" id="epost" name="epost" value="<?= e($epost) ?>" maxlength="255" autocomplete="email" placeholder="namn@exempel.se" required>
          <?php if (isset($fel['epost'])): ?><p class="falt-fel"><?= e($fel['epost']) ?></p><?php endif; ?>
        </div>

        <div class="falt<?= isset($fel['losenord']) ? ' har-fel' : '' ?>">
          <label for="losenord">Lösenord</label>
          <input type="password" id="losenord" name="losenord" autocomplete="new-password" placeholder="Minst 8 tecken" required>
          <?php if (isset($fel['losenord'])): ?>
            <p class="falt-fel"><?= e($fel['losenord']) ?></p>
          <?php else: ?>
            <p class="falt-tips">Minst 8 tecken.</p>
          <?php endif; ?>
        </div>

        <div class="falt<?= isset($fel['bekrafta']) ? ' har-fel' : '' ?>">
          <label for="bekrafta">Bekräfta lösenord</label>
          <input type="password" id="bekrafta" name="bekrafta" autocomplete="new-password" placeholder="Skriv lösenordet igen" required>
          <?php if (isset($fel['bekrafta'])): ?><p class="falt-fel"><?= e($fel['bekrafta']) ?></p><?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">Skapa konto</button>
      </form>

      <p class="auth-alt">Har du redan ett konto? <a href="login.php">Logga in</a></p>
      <a href="index.php" class="auth-back">Tillbaka till startsidan</a>
    </div>
  </div>
</main>

<footer>
  <div class="container footer-inner">
    <span>© <?= date('Y') ?> Striver</span>
    <span>Ett gymnasiearbete om träning, kost och teknik.</span>
  </div>
</footer>

</body>
</html>