<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';

auth_logout();

// Redémarre une session pour flash
session_start();
flash_set('success', 'Vous êtes déconnecté.');
redirect('/index.php');
