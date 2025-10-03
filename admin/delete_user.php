<?php
session_start();
require '../config.php';

// Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// OPTIONAL: Only allow admin or the user themselves to delete the account
// If you have a 'role' column in users table:
$stmt = $pdo->prepare("SELECT role FROM eusers WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// If not admin, can only delete own account
if ($user['role'] !== 'admin' && (!isset($_GET['id']) || $_GET['id'] != $userId)) {
    die("You don't have permission to delete this account.");
}

// The ID to delete
$deleteUserId = isset($_GET['id']) ? (int)$_GET['id'] : $userId;

try {
    $pdo->beginTransaction();

    // Delete RSVPs linked to this user
    $stmt = $pdo->prepare("DELETE FROM event_rsvps WHERE user_id = :uid");
    $stmt->execute([':uid' => $deleteUserId]);

    // Delete events organized by this user (optional: or transfer to another organizer)
    $stmt = $pdo->prepare("DELETE FROM events WHERE organizer_id = :uid");
    $stmt->execute([':uid' => $deleteUserId]);

    // Delete the user
    $stmt = $pdo->prepare("DELETE FROM eusers WHERE id = :uid");
    $stmt->execute([':uid' => $deleteUserId]);

    $pdo->commit();

    // If the current user deleted their own account, log them out
    if ($deleteUserId === $userId) {
        session_destroy();
        header('Location: goodbye.php'); // Create a goodbye/confirmation page
        exit;
    } else {
        header('Location: user_management.php');
        exit;
    }

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error deleting user: " . $e->getMessage());
}
