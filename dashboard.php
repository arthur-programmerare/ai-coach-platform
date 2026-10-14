<?php
// Striver – dashboard

// Startar sessionen så att vi kan läsa $_SESSION
session_start();

// SKYDD: är användaren inte inloggad skickas hen till inloggningssidan.
// exit är viktigt, annars fortsätter PHP att köra resten av filen.
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Hämtar namnet från sessionen. "??" ger ett reservvärde om namnet saknas.
$namn = (string)($_SESSION['user_name'] ?? 'där');

// Tar bara förnamnet så att hälsningen blir kortare
$fornamn = explode(' ', trim($namn))[0];

// Gör text säker att skriva ut i HTML (skydd mot XSS)
function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Din dashboard – Striver</title>
  <meta name="robots" content="noindex">
  <meta name="theme-color" content="#0a0b14">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- Den befintliga stilen återanvänds: navbar, knappar, färger och typsnitt -->
  <link rel="stylesheet" href="style.css">
  <style>
    /* Extra stil som bara gäller dashboarden */
    .dash-welcome { position: relative; overflow: hidden; padding: 4rem 0 2rem; }
    .dash-welcome::before, .dash-welcome::after { content: ""; position: absolute; border-radius: 50%; filter: blur(110px); pointer-events: none; }
    .dash-welcome::before { width: 440px; height: 440px; background: rgba(79, 124, 255, 0.24); top: -180px; left: -120px; }
    .dash-welcome::after { width: 400px; height: 400px; background: rgba(139, 92, 246, 0.22); top: -80px; right: -120px; }
    .dash-welcome .container { position: relative; z-index: 1; }
    .dash-welcome h1 { font: 800 clamp(2rem, 5vw, 3.2rem)/1.1 var(--font-head); letter-spacing: -0.03em; margin: 0 0 0.75rem; }
    .dash-welcome p { max-width: 560px; margin: 0 0 1.75rem; color: var(--muted); font-size: 1.15rem; }

    .dash-section { padding: 1.5rem 0 4rem; }

    /* Lite extra stil för platshållarna i korten */
    .card-status {
      align-self: flex-start; margin-bottom: 1rem; padding: 0.25rem 0.8rem; border-radius: 999px;
      border: 1px solid var(--line); background: rgba(255, 255, 255, 0.04); color: var(--muted); font-size: 0.85rem; font-weight: 600;
    }
    .placeholder-lines { display: grid; gap: 0.6rem; margin: 0 0 1.75rem; }
    .placeholder-lines span { display: block; height: 10px; border-radius: 999px; background: rgba(148, 163, 255, 0.12); }
    .placeholder-lines span:nth-child(1) { width: 100%; }
    .placeholder-lines span:nth-child(2) { width: 82%; }
    .placeholder-lines span:nth-child(3) { width: 60%; }
    .card .card-link.disabled { color: var(--muted); cursor: default; }

    @media (max-width: 600px) {
      .dash-welcome { padding: 2.5rem 0 1rem; }
      .dash-welcome .btn { width: 100%; }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<header class="nav">
  <div class="container nav-inner">
    <a href="index.php" class="logo" aria-label="Striver – startsida">
      <span class="logo-mark" aria-hidden="true">S</span>Striver
    </a>
    <div class="nav-actions">
      <!-- Länk till logout.php (filen skapas senare) -->
      <a href="logout.php" class="btn btn-ghost">Logga ut</a>
    </div>
  </div>
</header>

<main>
  <!-- Välkommen-sektion. e() gör namnet säkert att skriva ut. -->
  <section class="dash-welcome">
    <div class="container">
      <h1>Välkommen, <?= e($fornamn) ?>!</h1>
      <p>Det här är din startpunkt i Striver. Härifrån kommer du snart att kunna hantera din träning, din kost och hitta en coach.</p>
      <a href="questionnaire.php?steg=1" class="btn btn-primary">Svara på frågorna igen</a>
    </div>
  </section>

  <!-- Tre kort. De är bara platshållare och har ingen riktig funktion ännu. -->
  <section class="dash-section">
    <div class="container">
      <div class="cards">

        <article class="card">
          <div class="card-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 6.5v11M17.5 6.5v11M3.5 9v6M20.5 9v6M6.5 12h11"/></svg>
          </div>
          <span class="card-status">Kommer snart</span>
          <h3>Träningsplan</h3>
          <p>Här visas din personliga träningsplan när den är skapad.</p>
          <div class="placeholder-lines" aria-hidden="true"><span></span><span></span><span></span></div>
          <span class="card-link disabled">Ingen plan ännu</span>
        </article>

        <article class="card">
          <div class="card-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h18a9 9 0 0 1-18 0z"/><path d="M8 8c0-2 1.5-2.5 1.5-4.5M13.5 8c0-2 1.5-2.5 1.5-4.5"/></svg>
          </div>
          <span class="card-status">Kommer snart</span>
          <h3>Kostplan</h3>
          <p>Här visas din kostplan, anpassad efter dina mål och preferenser.</p>
          <div class="placeholder-lines" aria-hidden="true"><span></span><span></span><span></span></div>
          <span class="card-link disabled">Ingen plan ännu</span>
        </article>

        <article class="card">
          <div class="card-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><circle cx="17.5" cy="9" r="2.5"/><path d="M17 14.2c2.7.3 4.5 2.3 4.5 5.3"/></svg>
          </div>
          <span class="card-status">Kommer snart</span>
          <h3>Hitta coach</h3>
          <p>Här kommer du att kunna hitta riktiga tränare och coacher.</p>
          <div class="placeholder-lines" aria-hidden="true"><span></span><span></span><span></span></div>
          <a href="coaches.php" class="card-link">Gå till coacher</a>
        </article>

      </div>
    </div>
  </section>
</main>

<footer>
  <div class="container footer-inner">
    <span>© <?= date('Y') ?> Striver</span>
    <span>Ett gymnasiearbete om träning, kost och teknik.</span>
  </div>
</footer>

</body>
</html>