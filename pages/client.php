<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';

require_role('ADMIN');
$pdo = db();

$id_user = isset($_GET['id_user']) ? (int)$_GET['id_user'] : 0;
if ($id_user <= 0) {
    flash_set('error', 'Client invalide.');
    redirect('<?= BASE_URL ?>/pages/admin.php');
}

$stmt = $pdo->prepare("
  SELECT id_user, email, firstname, lastname, telephone, role, created_at
  FROM `user_account`
  WHERE id_user = :id
");
$stmt->execute([':id' => $id_user]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    flash_set('error', 'Client introuvable.');
    redirect('<?= BASE_URL ?>/pages/admin.php');
}

$stmt = $pdo->prepare("
  SELECT a.id_appointment, a.start_at, a.end_at, a.status,
         s.service_name, ar.artist_name
  FROM appointment a
  JOIN service s ON s.id_service = a.id_service
  JOIN artist ar ON ar.id_artist = a.id_artist
  WHERE a.id_user = :id_user
  ORDER BY a.start_at DESC
  LIMIT 100
");
$stmt->execute([':id_user' => $id_user]);
$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once BASE_PATH . '/app/views/partials/header.php';
?>

<h1>Fiche client</h1>

<div class="card">
  <p><strong>Nom :</strong> <?= e($user['firstname'].' '.$user['lastname']) ?></p>
  <p><strong>Email :</strong> <?= e($user['email']) ?></p>
  <p><strong>Téléphone :</strong> <?= e($user['telephone'] ?? '') ?></p>
  <p><strong>Créé le :</strong> <?= e((new DateTimeImmutable($user['created_at']))->format('d/m/Y')) ?></p>
</div>

<div class="card">
  <h2>Historique RDV</h2>
  <?php if (!$apps): ?>
    <p>Aucun rendez-vous.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>Date</th><th>Prestation</th><th>Artiste</th><th>Statut</th></tr></thead>
      <tbody>
      <?php foreach ($apps as $a): ?>
        <tr>
          <td><?= e((new DateTimeImmutable($a['start_at']))->format('d/m/Y H:i')) ?></td>
          <td><?= e($a['service_name']) ?></td>
          <td><?= e($a['artist_name']) ?></td>
          <td><?= e($a['status']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

</main></body></html>
