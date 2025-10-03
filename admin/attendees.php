<?php
session_start();
require '../config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if user is admin
$stmtRole = $pdo->prepare("SELECT role FROM eusers WHERE id = ?");
$stmtRole->execute([$_SESSION['user_id']]);
$user = $stmtRole->fetch();

if (!$user || $user['role'] !== 'admin') {
    die('Access denied. Admins only.');
}

// Get event ID from query
$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$event_id) {
    die('Invalid event ID.');
}

// Fetch event info
$stmtEvent = $pdo->prepare("SELECT * FROM events WHERE id = :id");
$stmtEvent->execute([':id' => $event_id]);
$event = $stmtEvent->fetch(PDO::FETCH_ASSOC);
if (!$event) {
    die('Event not found.');
}

// Fetch attendees who clicked 'going'
$stmtAttendees = $pdo->prepare("
    SELECT u.id, u.name, u.email, ea.responded_at
    FROM event_attendees ea
    JOIN eusers u ON ea.user_id = u.id
    WHERE ea.event_id = :event_id
    ORDER BY ea.responded_at ASC
");
$stmtAttendees->execute([':event_id' => $event_id]);
$attendees = $stmtAttendees->fetchAll(PDO::FETCH_ASSOC);

// Count total attendees
$totalAttendees = count($attendees);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendees - <?= htmlspecialchars($event['title']) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>
tailwind.config = {
  theme: {
    extend: {
      backgroundImage: {
        'space-pattern': "url('https://images.unsplash.com/photo-1534796636912-3b95b3ab5986?q=80&w=1471&auto=format&fit=crop')",
      }
    }
  }
}
</script>
</head>
<body class="bg-gray-900 text-gray-200 font-sans bg-cover bg-fixed" style="background-image: url('https://images.unsplash.com/photo-1534796636912-3b95b3ab5986?q=80&w=1471&auto=format&fit=crop')">
<div class="bg-gray-900/90 backdrop-blur-sm min-h-screen">
<div class="container mx-auto px-4 py-12 max-w-5xl">
    <!-- Header with space theme -->
    <div class="bg-gradient-to-r from-gray-800/80 to-gray-900/80 rounded-xl p-6 mb-8 border border-gray-700/50 shadow-2xl backdrop-blur-sm relative overflow-hidden">
        <div class="absolute -top-4 -right-4 w-24 h-24 bg-blue-500/10 rounded-full"></div>
        <div class="absolute -bottom-4 -left-4 w-16 h-16 bg-purple-500/10 rounded-full"></div>
        
        <div class="relative z-10">
            <h1 class="text-3xl font-bold mb-2 flex items-center">
                <i class="fas fa-users text-cyan-400 mr-3"></i>
                Attendees for: <span class="text-cyan-400 ml-2"><?= htmlspecialchars($event['title']) ?></span>
            </h1>
            
            <div class="flex flex-wrap items-center gap-4 mt-4">
                <div class="flex items-center text-gray-300">
                    <i class="fas fa-calendar-day text-yellow-400 mr-2"></i>
                    <span><?= date('M d, Y H:i', strtotime($event['start_datetime'])) ?></span>
                </div>
                <div class="flex items-center text-gray-300">
                    <i class="fas fa-user-astronaut text-green-400 mr-2"></i>
                    <span>Total Going: <span class="text-green-400 font-medium"><?= $totalAttendees ?></span></span>
                </div>
            </div>
        </div>
    </div>

    <?php if ($attendees): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($attendees as $attendee): ?>
                <div class="bg-gray-800/70 hover:bg-gray-700/80 border border-gray-700/50 rounded-xl p-4 flex justify-between items-center transition-all duration-300 backdrop-blur-sm group hover:shadow-lg hover:shadow-cyan-500/10">
                    <div class="flex items-center">
                        <div class="relative mr-4">
                            <div class="w-10 h-10 rounded-full bg-cyan-900/50 flex items-center justify-center group-hover:bg-cyan-800/70 transition">
                                <i class="fas fa-user-astronaut text-cyan-400"></i>
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-gray-800"></div>
                        </div>
                        <div>
                            <p class="font-semibold text-white"><?= htmlspecialchars($attendee['name']) ?></p>
                            <p class="text-sm text-gray-400"><?= htmlspecialchars($attendee['email']) ?></p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-400 flex items-center">
                        <i class="fas fa-clock text-gray-500 mr-1"></i>
                        <?= date('M d, Y H:i', strtotime($attendee['responded_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-gray-800/70 border border-gray-700/50 rounded-xl p-8 text-center backdrop-blur-sm">
            <div class="inline-block bg-gray-700/50 p-6 rounded-full mb-4">
                <i class="fas fa-user-clock text-4xl text-gray-400"></i>
            </div>
            <h3 class="text-xl font-medium text-gray-300 mb-2">No cosmic travelers yet</h3>
            <p class="text-gray-400">The launch pad is empty. No attendees have RSVPed "Going" yet.</p>
        </div>
    <?php endif; ?>

    <div class="mt-8">
        <a href="dashboard.php" class="bg-cyan-600 hover:bg-cyan-700 px-6 py-3 rounded-full inline-flex items-center transition-all duration-300 shadow-lg shadow-cyan-500/20 hover:shadow-cyan-500/40">
            <i class="fas fa-arrow-left mr-2"></i> Return to dashboard
        </a>
    </div>
</div>
</div>
</body>
</html>
