<?php
// Striver – startsida
// Ingen databasfunktion behövs här ännu, så db.php inkluderas inte.
// När sidan ska använda databasen: require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Striver – Din träning. Din kost. Din coach.</title>
  <meta name="description" content="Striver hjälper dig skapa en personlig tränings- och kostplan och hitta riktiga coacher.">
  <meta name="theme-color" content="#0a0b14">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="nav">
  <div class="container nav-inner">
    <a href="index.php" class="logo" aria-label="Striver – startsida">
      <span class="logo-mark" aria-hidden="true">S</span>Striver
    </a>

    <ul class="nav-links" id="nav-links">
      <li><a href="index.php" aria-current="page">Hem</a></li>
      <li><a href="#ai-traning">AI Träning</a></li>
      <li><a href="#ai-kost">AI Kost</a></li>
      <li><a href="coaches.php">Hitta coach</a></li>
      <li><a href="login.php">Logga in</a></li>
    </ul>

    <div class="nav-actions">
      <a href="questionnaire.php" class="btn btn-primary">Skapa min plan</a>
      <button class="menu-toggle" id="menu-toggle" aria-label="Öppna menyn" aria-expanded="false" aria-controls="nav-links">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
  </div>
</header>

<main>
  <section class="hero">
    <div class="container">
      <h1>
        <span>Din träning.</span>
        <span>Din kost.</span>
        <span class="grad-text">Din coach.</span>
      </h1>
      <p>Striver hjälper dig att skapa en personlig tränings- och kostplan som passar just dig, och att hitta riktiga coacher när du vill ha hjälp av en människa.</p>
      <div class="hero-cta">
        <a href="questionnaire.php" class="btn btn-primary btn-lg">Skapa min plan</a>
        <a href="coaches.php" class="btn btn-ghost btn-lg">Hitta en coach</a>
      </div>
    </div>
  </section>

  <section id="funktioner">
    <div class="container">
      <div class="section-head">
        <h2>Allt du behöver för att nå ditt mål</h2>
        <p>Tre delar som fungerar var för sig eller tillsammans, beroende på hur du vill träna.</p>
      </div>
      <div class="cards">
        <article class="card" id="ai-traning">
          <div class="card-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 6.5v11M17.5 6.5v11M3.5 9v6M20.5 9v6M6.5 12h11"/></svg>
          </div>
          <h3>AI Träning</h3>
          <p>En personlig träningsplan baserad på dina mål, din träningsform och hur mycket tid du har.</p>
          <a href="questionnaire.php" class="card-link">Skapa träningsplan</a>
        </article>
        <article class="card" id="ai-kost">
          <div class="card-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h18a9 9 0 0 1-18 0z"/><path d="M8 8c0-2 1.5-2.5 1.5-4.5M13.5 8c0-2 1.5-2.5 1.5-4.5"/></svg>
          </div>
          <h3>AI Kost</h3>
          <p>En kostplan som är anpassad efter dina mål och dina preferenser, så att maten funkar i vardagen.</p>
          <a href="questionnaire.php" class="card-link">Skapa kostplan</a>
        </article>
        <article class="card" id="coacher">
          <div class="card-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><circle cx="17.5" cy="9" r="2.5"/><path d="M17 14.2c2.7.3 4.5 2.3 4.5 5.3"/></svg>
          </div>
          <h3>Riktiga coacher</h3>
          <p>Hitta riktiga tränare och coacher som ger dig personlig hjälp, online eller på plats.</p>
          <a href="coaches.php" class="card-link">Hitta coach</a>
        </article>
      </div>
    </div>
  </section>

  <section id="sa-fungerar-striver">
    <div class="container">
      <div class="section-head">
        <h2>Så fungerar Striver</h2>
        <p>Från första frågan till din första träning.</p>
      </div>
      <ol class="steps">
        <li>
          <h3>Berätta om dig</h3>
          <p>Svara på några frågor om dina mål, din erfarenhet, din tid och dina matvanor.</p>
        </li>
        <li>
          <h3>Få din personliga plan</h3>
          <p>Du får en tränings- och kostplan som är byggd utifrån dina svar.</p>
        </li>
        <li>
          <h3>Utvecklas med AI eller en riktig coach</h3>
          <p>Fortsätt på egen hand med din plan, eller välj en coach som följer upp och hjälper dig vidare.</p>
        </li>
      </ol>
    </div>
  </section>

  <section>
    <div class="container">
      <div class="final-cta">
        <h2>Redo att börja?</h2>
        <a href="questionnaire.php" class="btn btn-primary btn-lg">Skapa din personliga plan</a>
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

<script>
  // Öppnar och stänger mobilmenyn
  const toggle = document.getElementById('menu-toggle');
  const links = document.getElementById('nav-links');
  toggle.addEventListener('click', () => {
    const open = links.classList.toggle('open');
    toggle.setAttribute('aria-expanded', open);
    toggle.setAttribute('aria-label', open ? 'Stäng menyn' : 'Öppna menyn');
  });
  links.addEventListener('click', e => {
    if (e.target.tagName === 'A') {
      links.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
    }
  });
</script>
</body>
</html>