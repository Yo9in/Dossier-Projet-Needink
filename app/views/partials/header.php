<?php
declare(strict_types=1);
$u = current_user();
$flashes = flash_get_all();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Need Ink</title>
  <style>
    body{font-family:system-ui,Arial;margin:0;background:#fafafa}
    header{background:#111;color:#fff;padding:12px 16px;display:flex;gap:12px;align-items:center}
    header a{color:#fff;text-decoration:none;margin-right:10px}
    main{padding:16px;max-width:1100px;margin:0 auto}
    .flash{padding:10px 12px;border-radius:10px;margin:10px 0}
    .flash.success{background:#e8fff0}
    .flash.error{background:#ffecec}
    .card{background:#fff;border:1px solid #eee;border-radius:14px;padding:14px;margin:12px 0}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}
    .row{display:flex;gap:12px;flex-wrap:wrap}
    .row > *{flex:1;min-width:220px}
    input,select,button{padding:10px;border-radius:10px;border:1px solid #ddd;width:100%}
    button{cursor:pointer}
    .actions{display:flex;gap:8px;flex-wrap:wrap}
    .actions form{margin:0}
    .btn{display:inline-block;padding:10px 12px;border-radius:10px;border:1px solid #ddd;background:#fff;text-decoration:none;color:#111}
    @media (max-width: 700px){
    header{flex-wrap:wrap}
    header a{margin-right:8px}
    .row{flex-direction:column}
    .row > *{min-width:unset}
    table{display:block; overflow-x:auto}
    th,td{white-space:nowrap}
    input,select,button{font-size:16px} 
    .nav{display:flex;gap:10px;flex-wrap:wrap}
    

}

  </style>
</head>
<body>
<header>
<a href="<?= BASE_URL ?>/index.php"><strong>Need Ink</strong></a>
<nav class="nav">
  

</nav>

  
 
 
  <span style="margin-left:auto"></span>
  <?php if ($u): ?>
    <span><?= e($u['firstname'].' '.$u['lastname']) ?> (<?= e($u['role']) ?>)</span>
    <a href="<?= BASE_URL ?>/pages/logout.php">Déconnexion</a>
  <?php else: ?>
  <a href="<?= BASE_URL ?>/pages/login.php">Connexion</a>
  <?php endif; ?>
</header>

<main>
<?php foreach ($flashes as $f): ?>
  <div class="flash <?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>
