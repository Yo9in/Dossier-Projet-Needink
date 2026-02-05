<?php
require_once __DIR__ . '/../config/db.php';

// Récupérer artistes actifs
$artists = db()->query("
  SELECT id_artist, artist_name
  FROM artist
  WHERE is_active = 1
  ORDER BY artist_name
")->fetchAll();

// Récupérer services actifs
$services = db()->query("
  SELECT id_service, service_name, duration_min
  FROM service
  WHERE is_active = 1
  ORDER BY service_name
")->fetchAll();

// Valeurs par défaut
$today = (new DateTime())->format('Y-m-d');

?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Réserver - NeedInk</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; max-width: 720px; margin: 40px auto; padding: 0 16px; }
    label { display:block; margin: 14px 0 6px; }
    select, input { width:100%; padding:10px; }
    button { margin-top:16px; padding:10px 14px; cursor:pointer; }
    .row { display:grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .hint { color:#555; font-size: 0.9rem; margin-top: 6px; }
  </style>
</head>
<body>
  <h1>Réserver un rendez-vous</h1>

  <form method="GET" action="availability.php">
    <label for="artist">Artiste</label>
    <select id="artist" name="artist" required>
      <option value="">-- Choisir un artiste --</option>
      <?php foreach ($artists as $a): ?>
        <option value="<?= (int)$a['id_artist'] ?>">
          <?= htmlspecialchars($a['artist_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label for="service">Prestation</label>
    <select id="service" name="service" required>
      <option value="">-- Choisir une prestation --</option>
      <?php foreach ($services as $s): ?>
        <option value="<?= (int)$s['id_service'] ?>">
          <?= htmlspecialchars($s['service_name']) ?> (<?= (int)$s['duration_min'] ?> min)
        </option>
      <?php endforeach; ?>
    </select>

    <div class="row">
      <div>
        <label for="date">Date</label>
        <input id="date" type="date" name="date" value="<?= htmlspecialchars($today) ?>" required>
        <div class="hint">On affiche ensuite les créneaux disponibles.</div>
      </div>

      <div>
        <label for="step">Pas de créneau</label>
        <select id="step" name="step">
          <option value="15" selected>15 min</option>
          <option value="30">30 min</option>
        </select>
        <div class="hint">Facultatif (15 min conseillé).</div>
      </div>
    </div>

    <button type="submit">Voir les disponibilités</button>
  </form>

  <p style="margin-top:20px;">
    <a href="../index.php">← Retour</a>
  </p>
</body>
</html>
