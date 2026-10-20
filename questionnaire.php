<?php
// Striver – frågeformulär (steg för steg)
// Sparar svaren i sessionen och i databasen när formuläret slutförs.
session_start();
require_once __DIR__ . '/db.php';
// ---------- Frågor och tillåtna svar ----------
$steg_data = [
    1 => [
        'nyckel' => 'mal',
        'fraga'  => 'Vad är ditt mål?',
        'hjalp'  => 'Välj det som stämmer bäst just nu.',
        'val'    => [
            'bygga_muskler'   => 'Bygga muskler',
            'bli_starkare'    => 'Bli starkare',
            'kondition'       => 'Förbättra konditionen',
            'explosivitet'    => 'Bli mer explosiv',
            'sportprestation' => 'Prestera bättre i min sport',
            'ga_ner_i_vikt'   => 'Gå ner i vikt',
            'annat'           => 'Annat',
        ],
    ],
    2 => [
        'nyckel' => 'traningstyp',
        'fraga'  => 'Vilken typ av träning gör du?',
        'hjalp'  => 'Välj den träningsform du fokuserar mest på.',
        'val'    => [
            'gym'         => 'Gym',
            'lopning'     => 'Löpning',
            'kampsport'   => 'Kampsport',
            'fotboll'     => 'Fotboll',
            'basket'      => 'Basket',
            'annan_sport' => 'Annan sport',
            'blandat'     => 'Blandat',
        ],
    ],
    3 => [
        'nyckel' => 'frekvens',
        'fraga'  => 'Hur ofta vill du träna?',
        'hjalp'  => 'Välj det som är realistiskt för din vardag.',
        'val'    => [
            '1-2' => '1–2 gånger i veckan',
            '3'   => '3 gånger i veckan',
            '4'   => '4 gånger i veckan',
            '5'   => '5 gånger i veckan',
            '6+'  => '6+ gånger i veckan',
        ],
    ],
    4 => [
        'nyckel' => 'passlangd',
        'fraga'  => 'Hur långt vill du träna per pass?',
        'hjalp'  => 'Räkna den tid du faktiskt har för själva träningen.',
        'val'    => [
            'under_30' => 'Under 30 minuter',
            '30-45'    => '30–45 minuter',
            '45-60'    => '45–60 minuter',
            '60-90'    => '60–90 minuter',
            'over_90'  => 'Över 90 minuter',
        ],
    ],
    5 => [
        'nyckel' => 'utrustning',
        'fraga'  => 'Vilken utrustning har du tillgång till?',
        'hjalp'  => 'Då kan planen byggas på det du faktiskt har.',
        'val'    => [
            'fullt_gym'  => 'Fullt gym',
            'enkelt_gym' => 'Enklare gym',
            'hemma'      => 'Hemmautrustning',
            'kroppsvikt' => 'Endast kroppsvikt',
            'annat'      => 'Annat',
        ],
    ],
    6 => [
        'nyckel' => 'hjalp',
        'fraga'  => 'Vad vill du ha hjälp med?',
        'hjalp'  => 'Du väljer själv vad du vill börja med.',
        'val'    => [
            'traning' => 'Träningsplan',
            'kost'    => 'Kostplan',
            'bada'    => 'Både träning och kost',
        ],
    ],
];
$antal_steg = count($steg_data);
// ---------- Sessionsdata och CSRF-token ----------
if (!isset($_SESSION['questionnaire']) || !is_array($_SESSION['questionnaire'])) {
    $_SESSION['questionnaire'] = [];
}
if (!isset($_SESSION['questionnaire_labels']) || !is_array($_SESSION['questionnaire_labels'])) {
    $_SESSION['questionnaire_labels'] = [];
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
// Första steget som saknar svar (så att man inte kan hoppa över steg)
function forsta_saknade_steg(array $steg_data, array $svar): int
{
    foreach ($steg_data as $nr => $steg) {
        if (!isset($svar[$steg['nyckel']])) {
            return $nr;
        }
    }
    return count($steg_data) + 1;
}
$fel = '';
$steg_nr = 1;
// ---------- Hantera skickat formulär ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $steg_nr = (int)($_POST['steg'] ?? 0);
    $token   = (string)($_POST['csrf'] ?? '');
    if (!hash_equals($_SESSION['csrf'], $token)) {
        $fel = 'Sessionen gick ut. Försök igen.';
        $steg_nr = max(1, min($antal_steg, $steg_nr));
    } elseif (!isset($steg_data[$steg_nr])) {
        header('Location: questionnaire.php');
        exit;
    } elseif ($steg_nr > forsta_saknade_steg($steg_data, $_SESSION['questionnaire'])) {
        // Försöker skicka in ett steg utan att ha svarat på tidigare steg
        header('Location: questionnaire.php?steg=' . forsta_saknade_steg($steg_data, $_SESSION['questionnaire']));
        exit;
    } else {
        $steg = $steg_data[$steg_nr];
        $svar = (string)($_POST['svar'] ?? '');
        if (!array_key_exists($svar, $steg['val'])) {
            $fel = 'Välj ett alternativ för att gå vidare.';
        } else {
            $_SESSION['questionnaire'][$steg['nyckel']] = $svar;
            $_SESSION['questionnaire_labels'][$steg['nyckel']] = $steg['val'][$svar];
            $_SESSION['questionnaire_complete'] = false;
            if ($steg_nr < $antal_steg) {
                header('Location: questionnaire.php?steg=' . ($steg_nr + 1));
                exit;
            }
            // Sista steget: kontrollera att allt är besvarat
            if (forsta_saknade_steg($steg_data, $_SESSION['questionnaire']) > $antal_steg) {
                // Kontrollera att användaren är inloggad
                if (empty($_SESSION['user_id'])) {
                    header('Location: login.php');
                    exit;
                }
                // Hämta svaren från sessionen
                $svar = $_SESSION['questionnaire'];
                try {
                    // Förbered SQL-frågan
                    $stmt = $pdo->prepare("
                        INSERT INTO questionnaire_answers
                        (user_id, goal, training_type, frequency, session_length, equipment, help_type)
                        VALUES
                        (:user_id, :goal, :training_type, :frequency, :session_length, :equipment, :help_type)
                    ");
                    // Skicka svaren till databasen
                    $stmt->execute([
                        ':user_id' => $_SESSION['user_id'],
                        ':goal' => $svar['mal'],
                        ':training_type' => $svar['traningstyp'],
                        ':frequency' => $svar['frekvens'],
                        ':session_length' => $svar['passlangd'],
                        ':equipment' => $svar['utrustning'],
                        ':help_type' => $svar['hjalp']
                    ]);
                    // Markera formuläret som färdigt
                    $_SESSION['questionnaire_complete'] = true;
                    // Skicka användaren till resultatsidan
                    header('Location: result.php');
                    exit;
                } catch (PDOException $ex) {
                    $fel = 'Kunde inte spara dina svar. Försök igen.';
                }
            }
        }
    }
} else {
    // ---------- Visa ett steg (GET) ----------
    $steg_nr = (int)($_GET['steg'] ?? 1);
    $steg_nr = max(1, min($antal_steg, $steg_nr));
    $forsta_saknade = forsta_saknade_steg($steg_data, $_SESSION['questionnaire']);
    if ($steg_nr > $forsta_saknade) {
        header('Location: questionnaire.php?steg=' . min($forsta_saknade, $antal_steg));
        exit;
    }
}
$steg = $steg_data[$steg_nr];
$vald = $_SESSION['questionnaire'][$steg['nyckel']] ?? '';
$procent = round($steg_nr / $antal_steg * 100);
$ar_sista = ($steg_nr === $antal_steg);
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
  <title>Steg <?= $steg_nr ?> av <?= $antal_steg ?> – Striver</title>
  <meta name="robots" content="noindex">
  <meta name="theme-color" content="#0a0b14">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #0a0b14;
      --surface: #12142a;
      --surface-2: #181b38;
      --line: rgba(148, 163, 255, 0.16);
      --text: #eef0ff;
      --muted: #a3a8c9;
      --blue: #4f7cff;
      --violet: #8b5cf6;
      --grad: linear-gradient(120deg, #4f7cff 0%, #8b5cf6 100%);
      --font-head: "Sora", "Segoe UI", system-ui, sans-serif;
      --font-body: "DM Sans", "Segoe UI", system-ui, sans-serif;
    }
    *, *::before, *::after { box-sizing: border-box; }
    body {
      margin: 0; min-height: 100vh; display: flex; flex-direction: column;
      background: var(--bg); color: var(--text);
      font-family: var(--font-body); font-size: 1.05rem; line-height: 1.6;
      -webkit-font-smoothing: antialiased; position: relative; overflow-x: hidden;
    }
    body::before, body::after {
      content: ""; position: fixed; border-radius: 50%; filter: blur(120px); pointer-events: none; z-index: 0;
    }
    body::before { width: 460px; height: 460px; background: rgba(79, 124, 255, 0.22); top: -160px; left: -140px; }
    body::after { width: 420px; height: 420px; background: rgba(139, 92, 246, 0.2); bottom: -160px; right: -120px; }
    a { color: inherit; text-decoration: none; }
    :focus-visible { outline: 2px solid var(--blue); outline-offset: 3px; border-radius: 8px; }
    .topbar {
      position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between;
      width: min(760px, 100% - 2.5rem); margin: 0 auto; padding: 1.5rem 0;
    }
    .logo { display: flex; align-items: center; gap: 0.6rem; font: 700 1.35rem var(--font-head); letter-spacing: -0.02em; }
    .logo-mark { width: 32px; height: 32px; border-radius: 10px; background: var(--grad); display: grid; place-items: center; font-size: 1rem; color: #fff; }
    .avbryt { color: var(--muted); font-weight: 500; font-size: 0.95rem; }
    .avbryt:hover { color: var(--text); }
    main { position: relative; z-index: 1; flex: 1; width: min(760px, 100% - 2.5rem); margin: 0 auto; padding: 1rem 0 3rem; }
    .progress-text { display: flex; justify-content: space-between; color: var(--muted); font-weight: 500; font-size: 0.95rem; margin-bottom: 0.75rem; }
    .progress-text strong { color: var(--text); }
    .progress { height: 8px; border-radius: 999px; background: rgba(148, 163, 255, 0.14); overflow: hidden; }
    .progress-bar { height: 100%; width: <?= $procent ?>%; border-radius: 999px; background: var(--grad); transition: width 0.4s ease; }
    h1 { font: 800 clamp(1.9rem, 5vw, 2.7rem)/1.15 var(--font-head); letter-spacing: -0.03em; margin: 2.5rem 0 0.6rem; }
    .hjalp { color: var(--muted); margin: 0 0 2rem; }
    fieldset { border: 0; padding: 0; margin: 0; min-width: 0; }
    .options { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.9rem; }
    .option { position: relative; display: block; cursor: pointer; }
    .option input { position: absolute; opacity: 0; inset: 0; width: 100%; height: 100%; margin: 0; cursor: pointer; }
    .option-body {
      display: flex; align-items: center; justify-content: space-between; gap: 1rem;
      min-height: 76px; padding: 1.1rem 1.4rem; border-radius: 20px;
      background: linear-gradient(180deg, var(--surface-2), var(--surface));
      border: 1px solid var(--line); font-weight: 600; font-size: 1.05rem;
      transition: border-color 0.2s, background 0.2s, transform 0.2s;
    }
    .option:hover .option-body { border-color: rgba(139, 92, 246, 0.55); }
    .check {
      flex: none; width: 24px; height: 24px; border-radius: 50%; border: 2px solid var(--line);
      display: grid; place-items: center; transition: all 0.2s;
    }
    .check svg { width: 14px; height: 14px; opacity: 0; color: #fff; }
    .option input:checked + .option-body {
      border-color: transparent;
      background: linear-gradient(var(--surface-2), var(--surface-2)) padding-box, var(--grad) border-box;
      border: 2px solid transparent;
      box-shadow: 0 10px 34px -14px rgba(99, 102, 241, 0.8);
    }
    .option input:checked + .option-body .check { background: var(--grad); border-color: transparent; }
    .option input:checked + .option-body .check svg { opacity: 1; }
    .option input:focus-visible + .option-body { outline: 2px solid var(--blue); outline-offset: 3px; }
    .fel { margin: 0 0 1.25rem; padding: 0.85rem 1.1rem; border-radius: 14px; background: rgba(248, 113, 113, 0.12); border: 1px solid rgba(248, 113, 113, 0.4); color: #fecaca; font-size: 0.98rem; }
    .actions { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-top: 2.5rem; }
    .btn {
      display: inline-flex; align-items: center; justify-content: center; padding: 0.95rem 1.9rem; border-radius: 999px;
      border: 1px solid transparent; font: 600 1rem var(--font-body); cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s, background 0.2s, opacity 0.2s;
    }
    .btn-primary { background: var(--grad); color: #fff; box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.7); }
    .btn-primary:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 14px 36px -10px rgba(99, 102, 241, 0.9); }
    .btn-primary:disabled { opacity: 0.4; cursor: not-allowed; box-shadow: none; }
    .btn-ghost { border-color: var(--line); background: rgba(255, 255, 255, 0.03); color: var(--text); }
    .btn-ghost:hover { background: rgba(255, 255, 255, 0.08); }
    @media (max-width: 600px) {
      .options { grid-template-columns: 1fr; }
      .option-body { min-height: 68px; }
      h1 { margin-top: 2rem; }
      .actions .btn { flex: 1; padding-inline: 1rem; }
    }
    @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
  </style>
</head>
<body>
<div class="topbar">
  <a href="index.php" class="logo" aria-label="Striver – till startsidan">
    <span class="logo-mark" aria-hidden="true">S</span>Striver
  </a>
  <a href="index.php" class="avbryt">Avbryt</a>
</div>
<main>
  <div class="progress-text">
    <strong>Steg <?= $steg_nr ?> av <?= $antal_steg ?></strong>
    <span><?= $procent ?> %</span>
  </div>
  <div class="progress" role="progressbar" aria-valuemin="1" aria-valuemax="<?= $antal_steg ?>" aria-valuenow="<?= $steg_nr ?>" aria-label="Framsteg i frågeformuläret">
    <div class="progress-bar"></div>
  </div>
  <form method="post" action="questionnaire.php" id="fraga-form" novalidate>
    <input type="hidden" name="steg" value="<?= $steg_nr ?>">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <fieldset>
      <legend>
        <h1><?= e($steg['fraga']) ?></h1>
      </legend>
      <p class="hjalp"><?= e($steg['hjalp']) ?></p>
      <?php if ($fel !== ''): ?>
        <p class="fel" role="alert"><?= e($fel) ?></p>
      <?php endif; ?>
      <div class="options">
        <?php foreach ($steg['val'] as $varde => $etikett): ?>
          <label class="option">
            <input type="radio" name="svar" value="<?= e((string)$varde) ?>" <?= ((string)$varde === (string)$vald) ? 'checked' : '' ?> required>
            <span class="option-body">
              <span><?= e($etikett) ?></span>
              <span class="check" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
              </span>
            </span>
          </label>
        <?php endforeach; ?>
      </div>
    </fieldset>
    <div class="actions">
      <?php if ($steg_nr > 1): ?>
        <a href="questionnaire.php?steg=<?= $steg_nr - 1 ?>" class="btn btn-ghost">Föregående</a>
      <?php else: ?>
        <span></span>
      <?php endif; ?>
      <button type="submit" class="btn btn-primary" id="nasta-knapp">
        <?= $ar_sista ? 'Slutför' : 'Nästa' ?>
      </button>
    </div>
  </form>
</main>
<script>
  // Knappen är inaktiv tills användaren har valt ett alternativ
  const knapp = document.getElementById('nasta-knapp');
  const val = document.querySelectorAll('input[name="svar"]');
  const uppdatera = () => { knapp.disabled = !document.querySelector('input[name="svar"]:checked'); };
  val.forEach(r => r.addEventListener('change', uppdatera));
  uppdatera();
</script>
</body>
</html>