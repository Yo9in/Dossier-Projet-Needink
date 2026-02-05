<?php
try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;port=8889;dbname=NeedInk;charset=utf8mb4",
        "root",
        "root",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "✅ Connexion DB OK";
} catch (Throwable $e) {
    echo "❌ Connexion DB FAIL<br>";
    echo $e->getMessage();
}
