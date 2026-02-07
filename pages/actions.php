<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}

$pdo = db();

$action = $_POST['action'] ?? '';
$csrf = $_POST['csrf'] ?? null;

if (!csrf_verify($csrf)) {
    flash_set('error', 'Session expirée (CSRF). Réessayez.');
    redirect('/index.php');
}

switch ($action) {

    case 'login': {
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            flash_set('error', 'Email et mot de passe requis.');
            redirect('/pages/login.php');
        }

        if (!auth_login($pdo, $email, $password)) {
            flash_set('error', 'Identifiants invalides.');
            redirect('/pages/login.php');
        }

        flash_set('success', 'Connexion réussie.');
        redirect('/index.php');
    }

    case 'create_appointment': {
        require_login();
        $u = current_user();

        $id_artist  = (int)($_POST['id_artist'] ?? 0);
        $id_service = (int)($_POST['id_service'] ?? 0);
        $start_at   = (string)($_POST['start_at'] ?? '');
        $date       = (string)($_POST['date'] ?? '');

        if ($id_artist <= 0 || $id_service <= 0 || $start_at === '') {
            flash_set('error', 'Paramètres invalides.');
            redirect('/pages/booking.php');
        }

        // Durée service
        $stmt = $pdo->prepare("SELECT duration_min FROM service WHERE id_service = :id AND is_active = 1");
        $stmt->execute([':id' => $id_service]);
        $durationMin = (int)($stmt->fetchColumn() ?: 0);

        if ($durationMin <= 0) {
            flash_set('error', 'Prestation invalide.');
            redirect('/pages/booking.php');
        }

        $start = new DateTimeImmutable($start_at);
        $end = $start->add(new DateInterval('PT' . $durationMin . 'M'));

        // Conflit RDV ?
        $stmt = $pdo->prepare("
          SELECT COUNT(*)
          FROM appointment
          WHERE id_artist = :id_artist
            AND status IN ('PENDING','CONFIRMED')
            AND (start_at < :end_at AND :start_at < end_at)
        ");
        $stmt->execute([
            ':id_artist' => $id_artist,
            ':start_at'  => $start->format('Y-m-d H:i:s'),
            ':end_at'    => $end->format('Y-m-d H:i:s'),
        ]);
        if ((int)$stmt->fetchColumn() > 0) {
            flash_set('error', 'Ce créneau vient d’être pris. Rechargez les disponibilités.');
            redirect('/pages/booking.php?id_artist='.$id_artist.'&id_service='.$id_service.'&date='.urlencode($date));
        }

        // Indispo ?
        $stmt = $pdo->prepare("
          SELECT COUNT(*)
          FROM unavailability
          WHERE id_artist = :id_artist
            AND (start_at < :end_at AND :start_at < end_at)
        ");
        $stmt->execute([
            ':id_artist' => $id_artist,
            ':start_at'  => $start->format('Y-m-d H:i:s'),
            ':end_at'    => $end->format('Y-m-d H:i:s'),
        ]);
        if ((int)$stmt->fetchColumn() > 0) {
            flash_set('error', 'Artiste indisponible sur ce créneau.');
            redirect('/pages/booking.php?id_artist='.$id_artist.'&id_service='.$id_service.'&date='.urlencode($date));
        }

        // Insert PENDING
        $stmt = $pdo->prepare("
          INSERT INTO appointment (id_user, id_artist, id_service, start_at, end_at, status, created_at)
          VALUES (:id_user, :id_artist, :id_service, :start_at, :end_at, 'PENDING', NOW())
        ");
        $stmt->execute([
            ':id_user'    => $u['id_user'],
            ':id_artist'  => $id_artist,
            ':id_service' => $id_service,
            ':start_at'   => $start->format('Y-m-d H:i:s'),
            ':end_at'     => $end->format('Y-m-d H:i:s'),
        ]);

        flash_set('success', 'Demande de RDV envoyée (PENDING).');
        redirect('/index.php');
    }

    case 'update_status': {
        require_role('ADMIN');

        $id_appointment = (int)($_POST['id_appointment'] ?? 0);
        $status = (string)($_POST['status'] ?? '');

        $allowed = ['PENDING', 'CONFIRMED', 'REFUSED', 'CANCELLED'];
        if ($id_appointment <= 0 || !in_array($status, $allowed, true)) {
            flash_set('error', 'Action invalide.');
            redirect('/pages/admin.php');
        }

        $stmt = $pdo->prepare("UPDATE appointment SET status = :status WHERE id_appointment = :id");
        $stmt->execute([':status' => $status, ':id' => $id_appointment]);

        flash_set('success', 'Statut mis à jour.');
        redirect('/pages/admin.php');
    }

    case 'update_appointment': {
        require_role('ADMIN');

        $id_appointment = (int)($_POST['id_appointment'] ?? 0);
        $start_at_local = (string)($_POST['start_at'] ?? '');
        $id_artist_new  = (int)($_POST['id_artist'] ?? 0);
        $id_service_new = (int)($_POST['id_service'] ?? 0);

        if ($id_appointment <= 0 || $start_at_local === '' || $id_artist_new <= 0 || $id_service_new <= 0) {
            flash_set('error', 'Paramètres invalides pour la modification.');
            redirect('/pages/admin.php');
        }

        // datetime-local => "YYYY-MM-DDTHH:MM" -> "YYYY-MM-DD HH:MM:00"
        $start_at = str_replace('T', ' ', $start_at_local) . ':00';
        $start = new DateTimeImmutable($start_at);

        // Durée du service choisi
        $stmt = $pdo->prepare("
          SELECT duration_min
          FROM service
          WHERE id_service = :id_service AND is_active = 1
        ");
        $stmt->execute([':id_service' => $id_service_new]);
        $durationMin = (int)($stmt->fetchColumn() ?: 0);

        if ($durationMin <= 0) {
            flash_set('error', 'Service invalide.');
            redirect('/pages/admin.php');
        }

        $end = $start->add(new DateInterval('PT' . $durationMin . 'M'));

        // Vérifier conflit avec un autre RDV pour le NOUVEL artiste
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM appointment
            WHERE id_artist = :id_artist
              AND id_appointment <> :id_appointment
              AND status IN ('PENDING','CONFIRMED')
              AND (start_at < :end_at AND :start_at < end_at)
        ");
        $stmt->execute([
            ':id_artist' => $id_artist_new,
            ':id_appointment' => $id_appointment,
            ':start_at' => $start->format('Y-m-d H:i:s'),
            ':end_at' => $end->format('Y-m-d H:i:s'),
        ]);
        if ((int)$stmt->fetchColumn() > 0) {
            flash_set('error', 'Conflit : l’artiste a déjà un rendez-vous sur ce créneau.');
            redirect('/pages/admin.php');
        }

        // Vérifier indisponibilité du NOUVEL artiste
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM unavailability
            WHERE id_artist = :id_artist
              AND (start_at < :end_at AND :start_at < end_at)
        ");
        $stmt->execute([
            ':id_artist' => $id_artist_new,
            ':start_at' => $start->format('Y-m-d H:i:s'),
            ':end_at' => $end->format('Y-m-d H:i:s'),
        ]);
        if ((int)$stmt->fetchColumn() > 0) {
            flash_set('error', 'Conflit : artiste indisponible sur ce créneau.');
            redirect('/pages/admin.php');
        }

        // Update complet (date + artiste + service + end_at)
        $stmt = $pdo->prepare("
            UPDATE appointment
            SET start_at = :start_at,
                end_at = :end_at,
                id_artist = :id_artist,
                id_service = :id_service
            WHERE id_appointment = :id
        ");
        $stmt->execute([
            ':start_at' => $start->format('Y-m-d H:i:s'),
            ':end_at' => $end->format('Y-m-d H:i:s'),
            ':id_artist' => $id_artist_new,
            ':id_service' => $id_service_new,
            ':id' => $id_appointment,
        ]);

        flash_set('success', 'Rendez-vous modifié.');
        redirect('/pages/admin.php');
    }

    default:
        flash_set('error', 'Action inconnue.');
        redirect('/index.php');
}
