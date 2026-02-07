<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';

$pdo = db();
$u = current_user();

require_once BASE_PATH . '/app/views/partials/header.php';
?>

<h1>Accueil</h1>

<div class="card">
  <?php if (!$u): ?>
    <p>Bienvenue ! Connectez-vous pour réserver un rendez-vous.</p>
    <p><a class="btn" href="<?= BASE_URL ?>/pages/login.php">Se connecter</a></p>
  <?php else: ?>
    <p>Bienvenue <?= e($u['firstname']) ?> 👋</p>
    <div class="actions">
      <a class="btn" href="<?= BASE_URL ?>/pages/booking.php">Réserver un RDV</a>
      <?php if ($u['role'] === 'ADMIN'): ?>
        <a class="btn" href="<?= BASE_URL ?>/pages/admin.php">Gestion Admin</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<?php if ($u && $u['role'] === 'CLIENT'): ?>
<?php
  $stmt = $pdo->prepare("
    SELECT a.id_appointment, a.start_at, a.end_at, a.status,
           s.service_name, ar.artist_name
    FROM appointment a
    JOIN service s ON s.id_service = a.id_service
    JOIN artist ar ON ar.id_artist = a.id_artist
    WHERE a.id_user = :id_user
      AND a.start_at >= NOW()
      AND a.status IN ('PENDING','CONFIRMED')
    ORDER BY a.start_at ASC
    LIMIT 5
  ");
  $stmt->execute([':id_user' => $u['id_user']]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
  <h2>Vos RDV à venir</h2>
  <?php if (!$rows): ?>
    <p>Aucun rendez-vous à venir.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>Date</th><th>Prestation</th><th>Artiste</th><th>Statut</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= e((new DateTimeImmutable($r['start_at']))->format('d/m/Y H:i')) ?></td>
            <td><?= e($r['service_name']) ?></td>
            <td><?= e($r['artist_name']) ?></td>
            <td><?= e($r['status']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<?php endif; ?>

</main></body></html>
