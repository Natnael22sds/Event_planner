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




$error = '';
$success = '';

$event_id = intval($_GET['id'] ?? 0);
if (!$event_id) {
    header('Location: events_list.php');
    exit;
}

// Fetch event to edit
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header('Location: events_list.php');
    exit;
}

// Check if current user is organizer
if ($event['organizer_id'] != $_SESSION['user_id']) {
    die('Unauthorized access.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start_datetime = $_POST['start_datetime'] ?? '';
    $end_datetime = $_POST['end_datetime'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $category = trim($_POST['category'] ?? '');

    // Basic validation
    if (!$title || !$start_datetime || !$end_datetime || !$category) {
        $error = "Please fill in all required fields (Title, Start, End, Category).";
    } elseif (strtotime($end_datetime) <= strtotime($start_datetime)) {
        $error = "End datetime must be after start datetime.";
    }

    if (!$error) {
        // Update event
        $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, start_datetime = ?, end_datetime = ?, location = ?, category = ? WHERE id = ?");
        $stmt->execute([$title, $description, $start_datetime, $end_datetime, $location, $category, $event_id]);

        // Handle new attachment upload if any
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $filename = basename($_FILES['attachment']['name']);
            $targetFile = $uploadDir . uniqid() . "_" . $filename;

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
                $stmt = $pdo->prepare("INSERT INTO event_attachments (event_id, filename, filepath) VALUES (?, ?, ?)");
                $stmt->execute([$event_id, $filename, $targetFile]);
            } else {
                $error = "Failed to upload attachment.";
            }
        }

        if (!$error) {
            // Notify all users except organizer
            $stmtEmails = $pdo->prepare("SELECT email FROM eusers WHERE id != ?");
            $stmtEmails->execute([$_SESSION['user_id']]);
            $emails = $stmtEmails->fetchAll(PDO::FETCH_COLUMN);

            $subject = "Event Updated: " . $title;
            $message = "
                <h2>Event Updated</h2>
                <p><strong>Title:</strong> " . htmlspecialchars($title) . "</p>
                <p><strong>Description:</strong><br>" . nl2br(htmlspecialchars($description)) . "</p>
                <p><strong>When:</strong> {$start_datetime} to {$end_datetime}</p>
                <p><strong>Location:</strong> " . htmlspecialchars($location) . "</p>
                <p>Check the event planner for more details.</p>
            ";

            foreach ($emails as $email) {
                send_email($email, $subject, $message);
            }

            $success = "Event updated successfully and notifications sent!";
            // Refresh event data
            $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->execute([$event_id]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Event</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white p-8 min-h-screen">
  <h1 class="text-3xl font-bold mb-6">Edit Event</h1>

    <div class="flex flex-wrap gap-4 mb-8">
      <a href="dashboard.php" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded">Back to dashboard</a>
    </div>

  <?php if ($error): ?>
    <div class="bg-red-600 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
  <?php elseif ($success): ?>
    <div class="bg-green-600 p-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="max-w-lg space-y-4">
    <div>
      <label class="block mb-1 font-semibold">Title <span class="text-red-500">*</span></label>
      <input type="text" name="title" required class="w-full p-2 rounded bg-gray-800 border border-gray-700" value="<?= htmlspecialchars($event['title']) ?>" />
    </div>

    <div>
      <label class="block mb-1 font-semibold">Description</label>
      <textarea name="description" rows="4" class="w-full p-2 rounded bg-gray-800 border border-gray-700"><?= htmlspecialchars($event['description']) ?></textarea>
    </div>

    <div>
      <label class="block mb-1 font-semibold">Start Date & Time <span class="text-red-500">*</span></label>
      <input type="datetime-local" name="start_datetime" required class="w-full p-2 rounded bg-gray-800 border border-gray-700" value="<?= date('Y-m-d\TH:i', strtotime($event['start_datetime'])) ?>" />
    </div>

    <div>
      <label class="block mb-1 font-semibold">End Date & Time <span class="text-red-500">*</span></label>
      <input type="datetime-local" name="end_datetime" required class="w-full p-2 rounded bg-gray-800 border border-gray-700" value="<?= date('Y-m-d\TH:i', strtotime($event['end_datetime'])) ?>" />
    </div>

    <div>
      <label class="block mb-1 font-semibold">Location</label>
      <input type="text" name="location" class="w-full p-2 rounded bg-gray-800 border border-gray-700" value="<?= htmlspecialchars($event['location']) ?>" />
    </div>

    <div>
      <label class="block mb-1 font-semibold">Category <span class="text-red-500">*</span></label>
      <select name="category" required class="w-full p-2 rounded bg-gray-800 border border-gray-700">
        <option value="">Select category</option>
        <option value="Meeting" <?= $event['category'] === 'Meeting' ? 'selected' : '' ?>>Meeting</option>
        <option value="Training" <?= $event['category'] === 'Training' ? 'selected' : '' ?>>Training</option>
        <option value="Social" <?= $event['category'] === 'Social' ? 'selected' : '' ?>>Social</option>
      </select>
    </div>

    <div>
      <label class="block mb-1 font-semibold">Attachment (optional)</label>
      <input type="file" name="attachment" accept=".pdf,.doc,.docx,.jpg,.png,.jpeg" class="w-full p-2 rounded bg-gray-800 border border-gray-700" />
    </div>

    <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 px-6 py-2 rounded font-semibold">Update Event</button>
  </form>

   <form method="GET" action="delete_event.php" onsubmit="return confirm('Are you sure you want to delete this event?');">
    <input type="hidden" name="id" value="<?= $event_id ?>">
    <button type="submit" class="mt-6 bg-red-600 hover:bg-red-700 px-6 py-2 rounded font-semibold">Delete Event</button>
  </form>


  <p class="mt-6">
    <a href="events_list.php" class="text-cyan-400 hover:underline">← Back to Events List</a>
  </p>
</body>
</html>
