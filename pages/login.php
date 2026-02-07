<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';


if (is_logged_in()) redirect('/index.php');

require_once BASE_PATH . '/app/views/partials/header.php';
?>

<h1>Connexion</h1>

<div class="card">
  <form method="post" action="<?= BASE_URL ?>/pages/actions.php">
    <input type="hidden" name="action" value="login">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

    <div class="row">
      <div>
        <label>Email</label>
        <input required type="email" name="email" autocomplete="email">
      </div>
      <div>
        <label>Mot de passe</label>
        <input required type="password" name="password" autocomplete="current-password">
      </div>
    </div>

    <p style="margin-top:12px"><button type="submit">Se connecter</button></p>
  </form>
</div>

</main></body></html>
