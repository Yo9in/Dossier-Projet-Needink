<?php
require_once __DIR__ . '/config/db.php';

$tables = db()->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>NeedInk</title>
</head>
<body>
  <h1>NeedInk - OK</h1>
  <p>Connexion DB : ✅</p>

  <h2>Tables détectées</h2>
  <ul>
    <?php foreach ($tables as $t): ?>
      <li><?= htmlspecialchars($t) ?></li>
    <?php endforeach; ?>
  </ul>
</body>
</html>
