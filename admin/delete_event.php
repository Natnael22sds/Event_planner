<?php
require '../config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


// Fetch user role from DB (or store in session on login)
$stmt = $pdo->prepare("SELECT role FROM eusers WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user['role'] !== 'admin') {
    die('Access denied. You do not have permission to perform this action.');
}




$event_id = intval($_GET['id'] ?? 0);
if (!$event_id) {
    header('Location: events_list.php');
    exit;
}

// Verify ownership
$stmt = $pdo->prepare("SELECT organizer_id FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$organizer = $stmt->fetchColumn();

if (!$organizer || $organizer != $_SESSION['user_id']) {
    die('Unauthorized access.');
}

// Delete event (attachments are deleted via foreign key cascade)
$stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
$stmt->execute([$event_id]);

header('Location: dashboard.php');
exit;
