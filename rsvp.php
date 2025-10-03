<?php
session_start();
require 'config.php';

// Ensure POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: events_list.php');
    exit;
}

// Ensure user logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get form data
$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$status   = strtolower(trim($_POST['status'] ?? ''));

// Allowed statuses
$allowed_statuses = ['going', 'maybe', 'not_going'];
if (!$event_id || !in_array($status, $allowed_statuses, true)) {
    $_SESSION['rsvp_error'] = 'Invalid event ID or status';
    header('Location: events_list.php');
    exit;
}

try {
    // ========== RSVP Table Handling (keep old logic) ==========
    $stmt = $pdo->prepare("SELECT id FROM event_rsvps WHERE event_id = :event_id AND user_id = :user_id");
    $stmt->execute([
        ':event_id' => $event_id,
        ':user_id'  => $user_id
    ]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update RSVP
        $stmt = $pdo->prepare("
            UPDATE event_rsvps 
            SET rsvp_status = :status, created_at = NOW() 
            WHERE id = :id
        ");
        $stmt->execute([
            ':status' => $status,
            ':id'     => $existing['id']
        ]);
        $_SESSION['rsvp_success'] = 'RSVP updated successfully';
    } else {
        // Insert RSVP
        $stmt = $pdo->prepare("
            INSERT INTO event_rsvps (event_id, user_id, rsvp_status, created_at) 
            VALUES (:event_id, :user_id, :status, NOW())
        ");
        $stmt->execute([
            ':event_id' => $event_id,
            ':user_id'  => $user_id,
            ':status'   => $status
        ]);
        $_SESSION['rsvp_success'] = 'RSVP created successfully';
    }

    // ========== Event Attendees Table Handling ==========
    if ($status === 'going') {
        // Insert or update attendee
        $stmt = $pdo->prepare("
            INSERT INTO event_attendees (event_id, user_id, rsvp_status, responded_at)
            VALUES (:event_id, :user_id, 'going', NOW())
            ON DUPLICATE KEY UPDATE rsvp_status = 'going', responded_at = NOW()
        ");
        $stmt->execute([
            ':event_id' => $event_id,
            ':user_id'  => $user_id
        ]);
    } else {
        // Remove from attendees if not 'going'
        $stmt = $pdo->prepare("
            DELETE FROM event_attendees 
            WHERE event_id = :event_id AND user_id = :user_id
        ");
        $stmt->execute([
            ':event_id' => $event_id,
            ':user_id'  => $user_id
        ]);
    }

} catch (PDOException $e) {
    $_SESSION['rsvp_error'] = 'Database error: ' . $e->getMessage();
}

// Redirect back
header('Location: events_list.php');
exit;
