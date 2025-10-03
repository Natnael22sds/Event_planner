<?php
ob_start();  // Start output buffering to prevent header issues
session_start();
require '../config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if current user is admin
$stmtRole = $pdo->prepare("SELECT role FROM eusers WHERE id = ?");
$stmtRole->execute([$_SESSION['user_id']]);
$user = $stmtRole->fetch();

if (!$user || $user['role'] !== 'admin') {
    // Deny access for non-admin users
    die('Access denied. Admins only.');
    exit;
}

// Fetch dashboard stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM eusers")->fetchColumn();
$totalAdmins = $pdo->query("SELECT COUNT(*) FROM eusers WHERE role = 'admin'")->fetchColumn();
$totalEvents = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$upcomingEvents = $pdo->query("SELECT COUNT(*) FROM events WHERE start_datetime > NOW()")->fetchColumn();

// Fetch recent 5 events
$stmtEvents = $pdo->query("SELECT * FROM events ORDER BY start_datetime DESC LIMIT 5");
$recentEvents = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SSGI 2025 - Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #06f7ff;
      --secondary: #0ea5e9;
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background: radial-gradient(circle at 20% 20%, #020617, #000000);
      color: #e2e8f0;
    }
    
    .stars {
      position: fixed;
      width: 100%;
      height: 100%;
      background: transparent url('https://www.transparenttextures.com/patterns/stardust.png') repeat;
      animation: moveStars 200s linear infinite;
      z-index: -1;
      opacity: 0.8;
    }
    
    @keyframes moveStars {
      from {background-position: 0 0;}
      to {background-position: 10000px 10000px;}
    }
    
    .glow {
      text-shadow: 0 0 10px var(--primary), 0 0 20px var(--primary);
    }
    
    .font-orbitron {
      font-family: 'Orbitron', sans-serif;
    }
    
    .dashboard-card {
      background: rgba(15, 23, 42, 0.8);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(6, 247, 255, 0.2);
      transition: all 0.3s ease;
    }
    
    .dashboard-card:hover {
      border-color: var(--primary);
      box-shadow: 0 0 20px rgba(6, 247, 255, 0.1);
      transform: translateY(-3px);
    }
    
    .btn-primary {
      background: linear-gradient(90deg, var(--primary), var(--secondary));
      background-size: 200% 200%;
      animation: glowMove 4s ease-in-out infinite;
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(6, 247, 255, 0.3);
    }
    
    @keyframes glowMove {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    
    .stat-card {
      position: relative;
      overflow: hidden;
    }
    
    .stat-card::after {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(6, 247, 255, 0.1) 0%, transparent 70%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .stat-card:hover::after {
      opacity: 1;
    }
  </style>
</head>
<body>
<div class="stars"></div>

<div class="container mx-auto px-4 py-8 max-w-7xl">
  <!-- Header -->
  <header class="mb-10">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
      <div>
        <h1 class="text-3xl md:text-4xl font-bold font-orbitron glow mb-2">Event Control Center</h1>
        <p class="text-gray-400">Admin dashboard for Space Science & Geospatial Institute Conference 2025</p>
      </div>
      <div class="flex items-center gap-4">
        <div class="flex items-center bg-gray-800/50 border border-gray-700 rounded-full px-4 py-2">
          <div class="w-3 h-3 rounded-full bg-green-500 mr-2 animate-pulse"></div>
          <span class="text-sm">Admin Online</span>
        </div>
        <a href="../logout.php" class="bg-red-600/80 hover:bg-red-600 px-4 py-2 rounded-full text-sm font-medium flex items-center">
          <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
      </div>
    </div>
  </header>

  <!-- Quick Stats -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
    <!-- Total Users -->
    <div class="stat-card dashboard-card p-6 rounded-xl">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-300 mb-1">Total Users</h2>
          <p class="text-3xl font-bold text-cyan-400"><?= $totalUsers ?></p>
        </div>
        <div class="bg-cyan-500/20 p-3 rounded-full">
          <i class="fas fa-users text-cyan-400 text-xl"></i>
        </div>
      </div>
      <div class="mt-4 pt-3 border-t border-gray-800">
        <a href="user_management.php" class="text-cyan-400 hover:underline text-sm flex items-center">
          Manage Users <i class="fas fa-chevron-right ml-1 text-xs"></i>
        </a>
      </div>
    </div>
    
    <!-- Admins -->
    <div class="stat-card dashboard-card p-6 rounded-xl">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-300 mb-1">Admin Users</h2>
          <p class="text-3xl font-bold text-yellow-400"><?= $totalAdmins ?></p>
        </div>
        <div class="bg-yellow-500/20 p-3 rounded-full">
          <i class="fas fa-user-shield text-yellow-400 text-xl"></i>
        </div>
      </div>
      <div class="mt-4 pt-3 border-t border-gray-800">
        <a href="admin_management.php" class="text-yellow-400 hover:underline text-sm flex items-center">
          View Admins <i class="fas fa-chevron-right ml-1 text-xs"></i>
        </a>
      </div>
    </div>
    
    <!-- Total Events -->
    <div class="stat-card dashboard-card p-6 rounded-xl">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-300 mb-1">Total Events</h2>
          <p class="text-3xl font-bold text-green-400"><?= $totalEvents ?></p>
        </div>
        <div class="bg-green-500/20 p-3 rounded-full">
          <i class="fas fa-calendar-star text-green-400 text-xl"></i>
        </div>
      </div>
      <div class="mt-4 pt-3 border-t border-gray-800">
        <a href="events_list.php" class="text-green-400 hover:underline text-sm flex items-center">
          All Events <i class="fas fa-chevron-right ml-1 text-xs"></i>
        </a>
      </div>
    </div>
    
    <!-- Upcoming Events -->
    <div class="stat-card dashboard-card p-6 rounded-xl">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-300 mb-1">Upcoming Events</h2>
          <p class="text-3xl font-bold text-purple-400"><?= $upcomingEvents ?></p>
        </div>
        <div class="bg-purple-500/20 p-3 rounded-full">
          <i class="fas fa-rocket text-purple-400 text-xl"></i>
        </div>
      </div>
      <div class="mt-4 pt-3 border-t border-gray-800">
        <a href="add_event.php" class="text-purple-400 hover:underline text-sm flex items-center">
          Add New <i class="fas fa-chevron-right ml-1 text-xs"></i>
        </a>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="flex flex-wrap gap-4 mb-10">
    <a href="add_event.php" class="btn-primary px-6 py-3 rounded-full font-medium text-black flex items-center">
      <i class="fas fa-plus mr-2"></i> Add Event
    </a>
    <a href="user_management.php" class="bg-yellow-600 hover:bg-yellow-700 px-6 py-3 rounded-full font-medium flex items-center">
      <i class="fas fa-users-cog mr-2"></i> Manage Users
    </a>
    <a href="../calendar.php" class="bg-purple-600 hover:bg-purple-700 px-6 py-3 rounded-full font-medium flex items-center">
      <i class="fas fa-calendar-alt mr-2"></i> View Calendar
    </a>
  </div>

  <!-- Recent Events -->
  <section class="mb-10">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl md:text-3xl font-bold font-orbitron">
        <i class="fas fa-history text-cyan-400 mr-3"></i>
        Recent Events
      </h2>
      <a href="events_list.php" class="text-cyan-400 hover:underline text-sm flex items-center">
        View All <i class="fas fa-chevron-right ml-1"></i>
      </a>
    </div>
    
    <?php if ($recentEvents): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($recentEvents as $event): ?>
          <div class="dashboard-card rounded-xl overflow-hidden">
            <div class="h-40 bg-gradient-to-r from-blue-900 to-purple-900 relative overflow-hidden">
              <div class="absolute inset-0 flex items-center justify-center">
                <i class="fas fa-calendar-star text-5xl opacity-20"></i>
              </div>
              <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent h-16"></div>
              <div class="absolute top-4 left-4 bg-gray-900/80 px-2 py-1 rounded text-xs">
                <?= date('M d, Y', strtotime($event['start_datetime'])) ?>
              </div>
            </div>
            
            <div class="p-6">
              <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars($event['title']) ?></h3>
              <div class="flex items-center text-gray-400 text-sm mb-3">
                <i class="fas fa-map-marker-alt mr-2"></i>
                <span><?= htmlspecialchars($event['location']) ?></span>
              </div>
              
              <div class="flex justify-between items-center pt-4 border-t border-gray-800 mt-4">
                <div class="space-x-3">
                  <a href="edit_event.php?id=<?= $event['id'] ?>" class="text-cyan-400 hover:underline text-sm">Edit</a>
                  <a href="delete_event.php?id=<?= $event['id'] ?>" onclick="return confirm('Are you sure you want to delete this event?');" class="text-red-400 hover:underline text-sm">Delete</a>
                </div>
                <a href="attendees.php?id=<?= $event['id'] ?>" class="text-sm bg-gray-700 hover:bg-gray-600 px-3 py-1 rounded-full">
                  <i class="fas fa-users mr-1"></i> Attendees
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="dashboard-card rounded-xl p-12 text-center">
        <i class="fas fa-calendar-times text-4xl text-gray-600 mb-4"></i>
        <h3 class="text-xl font-medium text-gray-400">No recent events found</h3>
        <p class="text-gray-500 mt-2">Create your first event to get started</p>
        <a href="add_event.php" class="inline-block mt-4 btn-primary px-6 py-2 rounded-full font-medium text-black">
          <i class="fas fa-plus mr-2"></i> Add Event
        </a>
      </div>
    <?php endif; ?>
  </section>
</div>

<script>
  // Simple animation for cards when page loads
  document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.dashboard-card');
    cards.forEach((card, index) => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      card.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
      
      setTimeout(() => {
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      }, 100);
    });
  });
</script>
</body>
</html>