<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';

require_role('ADMIN');
$pdo = db();

// Listes pour "Modifier"
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

// RDV
$stmt = $pdo->query("
  SELECT a.id_appointment, a.start_at, a.end_at, a.status,
         a.id_artist, a.id_service,
         u.id_user, u.firstname, u.lastname, u.email,
         s.service_name, s.duration_min,
         ar.artist_name
  FROM appointment a
  JOIN `user_account` u ON u.id_user = a.id_user
  JOIN service s ON s.id_service = a.id_service
  JOIN artist ar ON ar.id_artist = a.id_artist
  ORDER BY a.start_at DESC
  LIMIT 200
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once BASE_PATH . '/app/views/partials/header.php';

$actionUrl = BASE_URL . '/pages/actions.php';
?>

<h1>Admin — Rendez-vous</h1>

<div class="card table-responsive">
  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Client</th>
        <th>Prestation</th>
        <th>Artiste</th>
        <th>Statut</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>

    <?php foreach ($rows as $r): ?>
      <?php
        $id = (int)$r['id_appointment'];
        $csrf = csrf_token();
        $startLocal = (new DateTimeImmutable($r['start_at']))->format('Y-m-d\TH:i');
      ?>

      <tr>
        <td><?= e((new DateTimeImmutable($r['start_at']))->format('d/m/Y H:i')) ?></td>

        <td>
          <a href="<?= e(BASE_URL) ?>/pages/client.php?id_user=<?= (int)$r['id_user'] ?>">
            <?= e($r['firstname'].' '.$r['lastname']) ?>
          </a><br>
          <small><?= e($r['email']) ?></small>
        </td>

        <td><?= e($r['service_name']) ?> <small>(<?= (int)$r['duration_min'] ?> min)</small></td>
        <td><?= e($r['artist_name']) ?></td>
        <td><?= e($r['status']) ?></td>

        <td>
          <div class="actions">
            <!-- Confirmer -->
            <form method="post" action="<?= e($actionUrl) ?>" style="display:inline">
              <input type="hidden" name="action" value="update_status">
              <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
              <input type="hidden" name="id_appointment" value="<?= $id ?>">
              <input type="hidden" name="status" value="CONFIRMED">
              <button type="submit">Confirmer</button>
            </form>

            <!-- Refuser -->
            <form method="post" action="<?= e($actionUrl) ?>" style="display:inline">
              <input type="hidden" name="action" value="update_status">
              <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
              <input type="hidden" name="id_appointment" value="<?= $id ?>">
              <input type="hidden" name="status" value="REFUSED">
              <button type="submit">Refuser</button>
            </form>

            <!-- Annuler (soft delete) -->
            <form method="post" action="<?= e($actionUrl) ?>" style="display:inline">
              <input type="hidden" name="action" value="update_status">
              <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
              <input type="hidden" name="id_appointment" value="<?= $id ?>">
              <input type="hidden" name="status" value="CANCELLED">
              <button type="submit">Annuler</button>
            </form>

            <!-- Modifier -->
            <details style="margin-top:8px;width:100%">
              <summary class="btn" style="width:auto;display:inline-block">Modifier</summary>

              <form method="post" action="<?= e($actionUrl) ?>" style="margin-top:10px">
                <input type="hidden" name="action" value="update_appointment">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="id_appointment" value="<?= $id ?>">

                <div class="row">
                  <div>
                    <label>Début</label>
                    <input type="datetime-local" name="start_at" required value="<?= e($startLocal) ?>">
                  </div>

                  <div>
                    <label>Artiste</label>
                    <select name="id_artist" required>
                      <?php foreach ($artists as $a): ?>
                        <option value="<?= (int)$a['id_artist'] ?>"
                          <?= ((int)$r['id_artist'] === (int)$a['id_artist']) ? 'selected' : '' ?>>
                          <?= e($a['artist_name']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div>
                    <label>Prestation</label>
                    <select name="id_service" required>
                      <?php foreach ($services as $s): ?>
                        <option value="<?= (int)$s['id_service'] ?>"
                          <?= ((int)$r['id_service'] === (int)$s['id_service']) ? 'selected' : '' ?>>
                          <?= e($s['service_name']) ?> (<?= (int)$s['duration_min'] ?> min)
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <p style="margin:10px 0 0">
                  <button type="submit">Enregistrer</button>
                </p>
                <p style="color:#666;margin:6px 0 0">
                  La fin (end_at) est recalculée automatiquement selon la durée du service.
                </p>
              </form>
            </details>

          </div>
        </td>
      </tr>
    <?php endforeach; ?>

    </tbody>
  </table>
</div>

</main></body></html>
