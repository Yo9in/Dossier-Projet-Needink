<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';

require_login();
$pdo = db();

// --- Listes ---
$artists = $pdo->query("
  SELECT id_artist, artist_name
  FROM artist
  WHERE is_active = 1
  ORDER BY artist_name
")->fetchAll(PDO::FETCH_ASSOC);

$services = $pdo->query("
  SELECT id_service, service_name, duration_min
  FROM service
  WHERE is_active = 1
  ORDER BY service_name
")->fetchAll(PDO::FETCH_ASSOC);

// --- Paramètres GET (sélection) ---
$id_artist  = isset($_GET['id_artist']) ? (int)$_GET['id_artist'] : 0;
$id_service = isset($_GET['id_service']) ? (int)$_GET['id_service'] : 0;
$date       = $_GET['date'] ?? (new DateTimeImmutable('today'))->format('Y-m-d');

// --- Durée du service choisi ---
$durationMin = 0;
if ($id_service > 0) {
    $stmt = $pdo->prepare("SELECT duration_min FROM service WHERE id_service = :id AND is_active = 1");
    $stmt->execute([':id' => $id_service]);
    $durationMin = (int)($stmt->fetchColumn() ?: 0);
}

// --- Calcul disponibilités ---
$slots = [];
$canShowSlots = ($id_artist > 0 && $id_service > 0 && $durationMin > 0);

if ($canShowSlots) {
    // Horaires d'ouverture (à ajuster)
    $open  = new DateTimeImmutable($date . ' 09:00:00');
    $close = new DateTimeImmutable($date . ' 18:00:00');

    // RDV bloquants (PENDING + CONFIRMED)
    $stmt = $pdo->prepare("
      SELECT start_at, end_at
      FROM appointment
      WHERE id_artist = :id_artist
        AND DATE(start_at) = :d
        AND status IN ('PENDING','CONFIRMED')
    ");
    $stmt->execute([':id_artist' => $id_artist, ':d' => $date]);
    $appointments = array_map(fn($r) => [dt($r['start_at']), dt($r['end_at'])], $stmt->fetchAll(PDO::FETCH_ASSOC));

    // Indisponibilités
    $stmt = $pdo->prepare("
      SELECT start_at, end_at
      FROM unavailability
      WHERE id_artist = :id_artist
        AND DATE(start_at) <= :d1
        AND DATE(end_at) >= :d2
    ");
    $stmt->execute([':id_artist' => $id_artist, ':d1' => $date, ':d2' => $date,]);
    $unavs = array_map(fn($r) => [dt($r['start_at']), dt($r['end_at'])], $stmt->fetchAll(PDO::FETCH_ASSOC));

    // Génération créneaux (pas = durée prestation)
    $cursor = $open;
    $step = new DateInterval('PT' . $durationMin . 'M');

    while ($cursor < $close) {
        $end = $cursor->add($step);
        if ($end > $close) break;

        $blocked = false;

        foreach ($appointments as [$aS, $aE]) {
            if (overlap($cursor, $end, $aS, $aE)) { $blocked = true; break; }
        }

        if (!$blocked) {
            foreach ($unavs as [$uS, $uE]) {
                if (overlap($cursor, $end, $uS, $uE)) { $blocked = true; break; }
            }
        }

        if (!$blocked) {
            $slots[] = ['start' => $cursor, 'end' => $end];
        }

        $cursor = $cursor->add($step);
    }
}

require_once BASE_PATH . '/app/views/partials/header.php';

$actionUrl = BASE_URL . '/pages/actions.php';
$selfUrl   = BASE_URL . '/pages/booking.php';
?>

<h1>Réserver</h1>

<div class="card">
  <h2>Choix</h2>

  <!-- FORM GET: sélection => recharge booking.php et affiche les créneaux -->
  <form method="get" action="<?= e($selfUrl) ?>" id="filterForm">
    <div class="row">
      <div>
        <label>Artiste</label>
        <select name="id_artist" id="id_artist" required>
          <option value="">— Choisir —</option>
          <?php foreach ($artists as $a): ?>
            <option value="<?= (int)$a['id_artist'] ?>" <?= $id_artist === (int)$a['id_artist'] ? 'selected' : '' ?>>
              <?= e($a['artist_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label>Prestation</label>
        <select name="id_service" id="id_service" required>
          <option value="">— Choisir —</option>
          <?php foreach ($services as $s): ?>
            <option value="<?= (int)$s['id_service'] ?>" <?= $id_service === (int)$s['id_service'] ? 'selected' : '' ?>>
              <?= e($s['service_name']) ?> (<?= (int)$s['duration_min'] ?> min)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label>Date</label>
        <input type="date" name="date" id="date" value="<?= e($date) ?>" required>
      </div>
    </div>

    <p style="margin-top:12px">
      <button type="submit">Afficher les disponibilités</button>
    </p>
    <p style="color:#666;margin:0">
      
    </p>
  </form>
</div>

<div class="card">
  <h2>Disponibilités</h2>

  <?php if (!$canShowSlots): ?>
    <p>Choisis un artiste, une prestation et une date pour voir les créneaux.</p>

  <?php elseif (!$slots): ?>
    <p>Aucun créneau disponible sur cette date.</p>

  <?php else: ?>
    <!-- FORM POST: confirmation RDV -->
    <form method="post" action="<?= e($actionUrl) ?>">
      <input type="hidden" name="action" value="create_appointment">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="id_artist" value="<?= (int)$id_artist ?>">
      <input type="hidden" name="id_service" value="<?= (int)$id_service ?>">
      <input type="hidden" name="date" value="<?= e($date) ?>">

      <label>Choisir un créneau</label>
      <select name="start_at" required>
        <?php foreach ($slots as $sl): ?>
          <?php
            $startStr = $sl['start']->format('Y-m-d H:i:s');
            $label = $sl['start']->format('H:i') . ' → ' . $sl['end']->format('H:i');
          ?>
          <option value="<?= e($startStr) ?>"><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>

      <p style="margin-top:12px">
        <button type="submit">Confirmer la demande</button>
      </p>

      <p style="color:#666;margin:0">
        La demande sera créée en <strong>PENDING</strong> jusqu'a la validation par le salon.
      </p>
    </form>
  <?php endif; ?>
</div>

<script>
  // Auto-refresh des dispos dès qu'on change un champ (reste sur booking.php)
  (function () {
    const form = document.getElementById('filterForm');
    const ids = ['id_artist', 'id_service', 'date'];
    ids.forEach(id => {
      const el = document.getElementById(id);
      if (!el) return;
      el.addEventListener('change', () => form.submit());
    });
  })();
</script>

</main></body></html>
