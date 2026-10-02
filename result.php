<?php
// Striver – resultatsida
// Läser svaren från frågeformuläret. Ingen databas behövs ännu.

session_start();

// ---------- Skydd: formuläret måste vara genomfört ----------
$nycklar = ['mal', 'traningstyp', 'frekvens', 'passlangd', 'utrustning', 'hjalp'];
$svar = $_SESSION['questionnaire'] ?? null;

$komplett = !empty($_SESSION['questionnaire_complete']) && is_array($svar);
if ($komplett) {
    foreach ($nycklar as $k) {
        if (!isset($svar[$k]) || $svar[$k] === '') {
            $komplett = false;
            break;
        }
    }
}
if (!$komplett) {
    header('Location: questionnaire.php');
    exit;
}

$etiketter = is_array($_SESSION['questionnaire_labels'] ?? null) ? $_SESSION['questionnaire_labels'] : [];

function e(string $t): string
{
    return htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
}
function etikett(string $nyckel, array $svar, array $etiketter): string
{
    return (string)($etiketter[$nyckel] ?? $svar[$nyckel]);
}

// ---------- Innehåll som anpassas efter svaren ----------
$rubriker = [
    'bygga_muskler'   => ['Din väg mot mer muskler', 'Fokus ligger på progressiv belastning, tillräcklig volym och bra återhämtning.'],
    'bli_starkare'    => ['Din väg mot att bli starkare', 'Fokus ligger på tunga basövningar, tydlig progression och längre vila mellan seten.'],
    'kondition'       => ['Din väg mot bättre kondition', 'Fokus ligger på en balanserad mix av lugn distans, tempo och intervaller.'],
    'explosivitet'    => ['Din väg mot mer explosivitet', 'Fokus ligger på snabb styrka, hopp- och kastövningar samt kvalitet före kvantitet.'],
    'sportprestation' => ['Din väg mot bättre sportprestation', 'Fokus ligger på styrka, snabbhet och skadeförebyggande träning som stöttar din sport.'],
    'ga_ner_i_vikt'   => ['Din väg mot viktnedgång', 'Fokus ligger på hållbar träning, styrka för att behålla muskler och en kost du orkar följa.'],
    'annat'           => ['Din väg mot ditt mål', 'Fokus anpassas efter det du vill uppnå, så att planen blir din egen.'],
];
[$rubrik, $fokus] = $rubriker[$svar['mal']] ?? $rubriker['annat'];

$frekvens_text = [
    '1-2' => 'Med 1–2 pass i veckan är varje pass viktigt. Helkroppspass ger mest effekt på begränsad tid.',
    '3'   => 'Tre pass i veckan är en stark grund. Helkroppspass eller en enkel uppdelning fungerar bra.',
    '4'   => 'Fyra pass ger utrymme för en uppdelning, till exempel överkropp och underkropp.',
    '5'   => 'Fem pass kräver en genomtänkt uppdelning och planerade vilodagar för att du ska orka.',
    '6+'  => 'Sex eller fler pass ställer höga krav på återhämtning, så belastningen behöver varieras.',
];
$pass_text = [
    'under_30' => 'Korta pass fungerar bra när de är fokuserade: färre övningar och högre intensitet.',
    '30-45'    => 'Med 30–45 minuter hinner du med ett effektivt pass med uppvärmning och huvuddelen.',
    '45-60'    => 'Med 45–60 minuter finns plats för uppvärmning, huvudövningar och kompletterande arbete.',
    '60-90'    => 'Med 60–90 minuter kan passen ha fler övningar, mer volym och längre vila.',
    'over_90'  => 'Långa pass kräver bra struktur så att kvaliteten hålls uppe hela vägen.',
];
$utrustning_text = [
    'fullt_gym'  => 'Med ett fullt gym kan planen använda skivstång, hantlar, maskiner och kablar.',
    'enkelt_gym' => 'Med ett enklare gym byggs planen kring de redskap som finns, utan onödiga kompromisser.',
    'hemma'      => 'Med hemmautrustning anpassas övningarna efter det du har hemma.',
    'kroppsvikt' => 'Med endast kroppsvikt bygger planen på övningar du kan göra var som helst.',
    'annat'      => 'Planen anpassas efter den utrustning du har tillgång till.',
];

// Veckorytm: vilka dagar som är träningsdagar utifrån frekvens
$dagar = ['Mån', 'Tis', 'Ons', 'Tor', 'Fre', 'Lör', 'Sön'];
$rytm = [
    '1-2' => [1, 4],
    '3'   => [0, 2, 4],
    '4'   => [0, 1, 3, 4],
    '5'   => [0, 1, 2, 4, 5],
    '6+'  => [0, 1, 2, 3, 4, 5],
];
$traningsdagar = $rytm[$svar['frekvens']] ?? [0, 2, 4];

// Vad Striver kan skapa, och vilket som motsvarar användarens val
$hjalp = $svar['hjalp'];
$leveranser = [
    [
        'titel' => 'Personlig träningsplan',
        'text'  => 'Pass, övningar och progression utifrån ditt mål, din tid och din utrustning.',
        'valt'  => in_array($hjalp, ['traning', 'bada'], true),
    ],
    [
        'titel' => 'Personlig kostplan',
        'text'  => 'Måltider och riktlinjer som passar ditt mål och dina preferenser.',
        'valt'  => in_array($hjalp, ['kost', 'bada'], true),
    ],
    [
        'titel' => 'Riktiga coacher',
        'text'  => 'Hitta en tränare eller coach som ger personlig hjälp, online eller på plats.',
        'valt'  => false,
    ],
];

$sammanfattning = [
    'mal'         => 'Mål',
    'traningstyp' => 'Träningsform',
    'frekvens'    => 'Frekvens',
    'passlangd'   => 'Passlängd',
    'utrustning'  => 'Utrustning',
    'hjalp'       => 'Hjälp med',
];
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Din personliga startpunkt – Striver</title>
  <meta name="robots" content="noindex">
  <meta name="theme-color" content="#0a0b14">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    .r-sec { padding: 2.5rem 0; }
    .r-hero { position: relative; overflow: hidden; padding: 4.5rem 0 2rem; text-align: center; }
    .r-hero::before, .r-hero::after { content: ""; position: absolute; border-radius: 50%; filter: blur(110px); pointer-events: none; z-index: 0; }
    .r-hero::before { width: 460px; height: 460px; background: rgba(79, 124, 255, 0.26); top: -180px; left: -120px; }
    .r-hero::after { width: 420px; height: 420px; background: rgba(139, 92, 246, 0.24); top: -60px; right: -120px; }
    .r-hero .container { position: relative; z-index: 1; max-width: 800px; }
    .r-badge { display: inline-block; padding: 0.4rem 1rem; border-radius: 999px; border: 1px solid var(--line); background: rgba(255,255,255,0.04); color: #c4b5fd; font-weight: 600; font-size: 0.92rem; margin-bottom: 1.5rem; }
    .r-hero h1 { font: 800 clamp(2.2rem, 6vw, 3.8rem)/1.1 var(--font-head); letter-spacing: -0.035em; margin: 0 0 1.25rem; }
    .r-hero p { max-width: 600px; margin: 0 auto; color: var(--muted); font-size: 1.15rem; }

    .r-title { font: 700 1.6rem var(--font-head); letter-spacing: -0.02em; margin: 0 0 1.5rem; }
    .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
    .summary div { padding: 1.25rem 1.4rem; border-radius: 20px; border: 1px solid var(--line); background: linear-gradient(180deg, var(--surface-2), var(--surface)); }
    .summary dt { color: var(--muted); font-size: 0.92rem; margin-bottom: 0.25rem; }
    .summary dd { margin: 0; font: 600 1.1rem var(--font-head); letter-spacing: -0.01em; }
    .summary { margin: 0; }

    .insights { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; }
    .insight { padding: 1.75rem; border-radius: var(--radius); border: 1px solid var(--line); background: linear-gradient(180deg, var(--surface-2), var(--surface)); }
    .insight h3 { font: 700 1.1rem var(--font-head); margin: 0 0 0.6rem; }
    .insight p { margin: 0; color: var(--muted); font-size: 1rem; }
    .focus-card { margin-bottom: 1.25rem; padding: 1.75rem 2rem; border-radius: var(--radius); border: 1px solid rgba(139, 92, 246, 0.35); background: linear-gradient(135deg, rgba(79,124,255,0.14), rgba(139,92,246,0.14)); }
    .focus-card h3 { font: 700 1.2rem var(--font-head); margin: 0 0 0.5rem; }
    .focus-card p { margin: 0; color: #d4d7f0; }

    .week { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.6rem; margin-top: 1.5rem; }
    .day { padding: 1rem 0.25rem; text-align: center; border-radius: 16px; border: 1px solid var(--line); background: var(--surface); color: var(--muted); font-weight: 500; }
    .day small { display: block; margin-top: 0.25rem; font-size: 0.78rem; }
    .day.on { border-color: transparent; background: var(--grad); color: #fff; font-weight: 600; box-shadow: 0 10px 26px -12px rgba(99,102,241,0.8); }
    .week-note { color: var(--muted); font-size: 0.95rem; margin: 1rem 0 0; }

    .offer { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; }
    .offer-card { position: relative; padding: 1.75rem; border-radius: var(--radius); border: 1px solid var(--line); background: linear-gradient(180deg, var(--surface-2), var(--surface)); }
    .offer-card.valt { border-color: rgba(139, 92, 246, 0.6); }
    .offer-card h3 { font: 700 1.2rem var(--font-head); margin: 0 0 0.6rem; letter-spacing: -0.02em; }
    .offer-card p { margin: 0; color: var(--muted); }
    .valt-tag { display: inline-block; margin-bottom: 0.9rem; padding: 0.2rem 0.7rem; border-radius: 999px; background: var(--grad); color: #fff; font-size: 0.82rem; font-weight: 600; }

    .cta-box { text-align: center; padding: 4rem 2rem; border-radius: 32px; border: 1px solid var(--line);
      background: radial-gradient(ellipse at 20% 0%, rgba(79,124,255,0.3), transparent 60%), radial-gradient(ellipse at 90% 100%, rgba(139,92,246,0.3), transparent 60%), var(--surface); }
    .cta-box h2 { font: 800 clamp(1.8rem, 4.5vw, 2.7rem)/1.15 var(--font-head); letter-spacing: -0.03em; margin: 0 0 1rem; }
    .cta-box p { max-width: 520px; margin: 0 auto 2rem; color: var(--muted); }
    .change-link { display: inline-block; margin-top: 1.5rem; color: var(--muted); font-weight: 500; font-size: 0.95rem; text-decoration: underline; text-underline-offset: 4px; }
    .change-link:hover { color: var(--text); }

    @media (max-width: 900px) {
      .summary, .insights, .offer { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 600px) {
      .r-hero { padding: 3rem 0 1rem; }
      .summary, .insights, .offer { grid-template-columns: 1fr; }
      .week { gap: 0.35rem; }
      .day { padding: 0.75rem 0; border-radius: 12px; font-size: 0.9rem; }
      .day small { display: none; }
      .cta-box { padding: 3rem 1.25rem; }
      .cta-box .btn { width: 100%; }
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
      <a href="questionnaire.php?steg=1" class="nav-login">Ändra svar</a>
    </div>
  </div>
</header>

<main>
  <section class="r-hero">
    <div class="container">
      <span class="r-badge">Frågeformuläret är klart</span>
      <h1><?= e($rubrik) ?></h1>
      <p><?= e($fokus) ?></p>
    </div>
  </section>

  <section class="r-sec">
    <div class="container">
      <h2 class="r-title">Dina svar</h2>
      <dl class="summary">
        <?php foreach ($sammanfattning as $nyckel => $rubrik_text): ?>
          <div>
            <dt><?= e($rubrik_text) ?></dt>
            <dd><?= e(etikett($nyckel, $svar, $etiketter)) ?></dd>
          </div>
        <?php endforeach; ?>
      </dl>
    </div>
  </section>

  <section class="r-sec">
    <div class="container">
      <h2 class="r-title">Så tar vi hänsyn till dina svar</h2>
      <div class="focus-card">
        <h3>Ditt fokus: <?= e(etikett('mal', $svar, $etiketter)) ?></h3>
        <p><?= e($fokus) ?></p>
      </div>
      <div class="insights">
        <div class="insight">
          <h3><?= e(etikett('frekvens', $svar, $etiketter)) ?></h3>
          <p><?= e($frekvens_text[$svar['frekvens']] ?? 'Planen anpassas efter hur ofta du vill träna.') ?></p>
        </div>
        <div class="insight">
          <h3><?= e(etikett('passlangd', $svar, $etiketter)) ?> per pass</h3>
          <p><?= e($pass_text[$svar['passlangd']] ?? 'Passen anpassas efter den tid du har.') ?></p>
        </div>
        <div class="insight">
          <h3><?= e(etikett('utrustning', $svar, $etiketter)) ?></h3>
          <p><?= e($utrustning_text[$svar['utrustning']] ?? 'Planen anpassas efter din utrustning.') ?></p>
        </div>
      </div>
    </div>
  </section>

  <section class="r-sec">
    <div class="container">
      <h2 class="r-title">Exempel på veckorytm</h2>
      <div class="week" role="list" aria-label="Träningsdagar under en vecka">
        <?php foreach ($dagar as $i => $dag): ?>
          <?php $pa = in_array($i, $traningsdagar, true); ?>
          <div class="day<?= $pa ? ' on' : '' ?>" role="listitem">
            <?= e($dag) ?><small><?= $pa ? 'Träning' : 'Vila' ?></small>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="week-note">Ett exempel utifrån hur ofta du vill träna. Din riktiga plan kan justeras efter din vardag.</p>
    </div>
  </section>

  <section class="r-sec">
    <div class="container">
      <h2 class="r-title">Det här kan Striver skapa åt dig</h2>
      <div class="offer">
        <?php foreach ($leveranser as $l): ?>
          <article class="offer-card<?= $l['valt'] ? ' valt' : '' ?>">
            <?php if ($l['valt']): ?><span class="valt-tag">Ditt val</span><?php endif; ?>
            <h3><?= e($l['titel']) ?></h3>
            <p><?= e($l['text']) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="r-sec" style="padding-bottom: 5rem;">
    <div class="container">
      <div class="cta-box">
        <h2>Redo att göra planen till din egen?</h2>
        <p>Skapa ett konto så sparas dina svar och du kan fortsätta när du vill.</p>
        <a href="register.php" class="btn btn-primary btn-lg">Skapa mitt gratis konto</a>
        <div><a href="questionnaire.php?steg=1" class="change-link">Ändra mina svar</a></div>
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