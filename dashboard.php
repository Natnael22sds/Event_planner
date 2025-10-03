<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? '';

// Fetch user role
$stmtRole = $pdo->prepare("SELECT role FROM eusers WHERE id = ?");
$stmtRole->execute([$user_id]);
$user = $stmtRole->fetch();

if (!$user) {
    die('User not found.');
}

$role = strtolower($user['role']);

// If admin tries to access this, send them to admin dashboard
if ($role === 'admin') {
    header('Location: admin/dashboard.php');
    exit;
}

// Regular user: fetch all events
$stmtUpcoming = $pdo->prepare("SELECT * FROM events ORDER BY start_datetime ASC");
$stmtUpcoming->execute();
$upcomingEventsList = $stmtUpcoming->fetchAll(PDO::FETCH_ASSOC);

// Fetch RSVPs for this user
$stmtUserRSVPs = $pdo->prepare("SELECT event_id FROM event_rsvps WHERE user_id = ?");
$stmtUserRSVPs->execute([$user_id]);
$userRSVPs = $stmtUserRSVPs->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SSGI 2025 - User Dashboard</title>
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
    
    .badge {
      animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.7; }
    }
  </style>
</head>
<body>
<div class="stars"></div>

<div class="container mx-auto px-4 py-8 max-w-6xl">
  <!-- Header -->
  <header class="mb-10">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
      <div>
        <h1 class="text-3xl md:text-4xl font-bold font-orbitron glow mb-2">Welcome, <?= htmlspecialchars($username) ?> <span class="text-cyan-400">👨‍🚀</span></h1>
        <p class="text-gray-400">Your dashboard for SSGI 2025 Conference</p>
      </div>
      <div class="mt-4 md:mt-0 flex gap-3">
        <a href="calendar.php" class="btn-primary px-6 py-2 rounded-full font-medium text-black flex items-center">
          <i class="fas fa-calendar-alt mr-2"></i> View Calendar
        </a>
        <a href="logout.php" class="bg-gray-700 hover:bg-gray-600 px-6 py-2 rounded-full font-medium flex items-center">
          <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
      </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
      <div class="dashboard-card rounded-xl p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm">Your RSVPs</p>
            <h3 class="text-2xl font-bold"><?= count($userRSVPs) ?></h3>
          </div>
          <div class="bg-cyan-500/20 p-3 rounded-full">
            <i class="fas fa-calendar-check text-cyan-400 text-xl"></i>
          </div>
        </div>
      </div>
      
      <div class="dashboard-card rounded-xl p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm">Upcoming Events</p>
            <h3 class="text-2xl font-bold"><?= count($upcomingEventsList) ?></h3>
          </div>
          <div class="bg-purple-500/20 p-3 rounded-full">
            <i class="fas fa-rocket text-purple-400 text-xl"></i>
          </div>
        </div>
      </div>
      
      <div class="dashboard-card rounded-xl p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm">Days Until Event</p>
            <h3 class="text-2xl font-bold" id="daysUntilEvent">-</h3>
          </div>
          <div class="bg-yellow-500/20 p-3 rounded-full">
            <i class="fas fa-clock text-yellow-400 text-xl"></i>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main>
    <section class="mb-12">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl md:text-3xl font-bold font-orbitron">
          <i class="fas fa-calendar-day text-cyan-400 mr-3"></i>
          Upcoming Events
        </h2>
        <a href="events_list.php" class="text-sm text-cyan-400 hover:underline flex items-center">
          View All <i class="fas fa-chevron-right ml-1"></i>
        </a>
      </div>
      
      <?php if ($upcomingEventsList): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <?php foreach ($upcomingEventsList as $event): ?>
            <div class="dashboard-card rounded-xl p-6 hover:border-cyan-400">
              <div class="flex flex-col h-full">
                <div class="flex-grow">
                  <div class="flex justify-between items-start mb-3">
                    <h3 class="text-xl font-semibold"><?= htmlspecialchars($event['title']) ?></h3>
                    <?php if (in_array($event['id'], $userRSVPs)): ?>
                      <span class="px-3 py-1 bg-green-600/30 text-green-400 rounded-full text-xs font-medium flex items-center">
                        <i class="fas fa-check-circle mr-1"></i> RSVPed
                      </span>
                    <?php endif; ?>
                  </div>
                  
                  <div class="flex items-center text-gray-400 text-sm mb-3">
                    <i class="far fa-clock mr-2"></i>
                    <span><?= date('M d, Y H:i', strtotime($event['start_datetime'])) ?></span>
                  </div>
                  
                  <div class="flex items-center text-gray-400 text-sm mb-4">
                    <i class="fas fa-map-marker-alt mr-2"></i>
                    <span><?= htmlspecialchars($event['location']) ?></span>
                  </div>
                  
                  <p class="text-gray-300 mb-4 line-clamp-2"><?= htmlspecialchars($event['description'] ?? 'No description available') ?></p>
                </div>
                
                <div class="flex justify-between items-center pt-4 border-t border-gray-800 mt-auto">
                  <a href="events_list.php" class="text-cyan-400 hover:underline text-sm flex items-center">
                    View Details <i class="fas fa-chevron-right ml-1 text-xs"></i>
                  </a>
                  
                  <?php if (!in_array($event['id'], $userRSVPs)): ?>
    
                        <button type="button" 
                                onclick="window.location.href='events_list.php'" 
                                class="btn-primary px-4 py-1 rounded-full text-sm font-medium">
                            RSVP Now
                        </button>

            

                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="dashboard-card rounded-xl p-12 text-center">
          <i class="fas fa-calendar-times text-4xl text-gray-600 mb-4"></i>
          <h3 class="text-xl font-medium text-gray-400">No upcoming events found</h3>
          <p class="text-gray-500 mt-2">Check back later or browse all events</p>
          <a href="all_events.php" class="inline-block mt-4 text-cyan-400 hover:underline">View All Events</a>
        </div>
      <?php endif; ?>
    </section>
    
    <!-- Additional Sections -->
    <section class="mb-12">
      <h2 class="text-2xl md:text-3xl font-bold font-orbitron mb-6">
        <i class="fas fa-bullhorn text-purple-400 mr-3"></i>
        Announcements
      </h2>
      
      <div class="dashboard-card rounded-xl p-6">
        <div class="flex items-start">
          <div class="bg-purple-500/20 p-3 rounded-full mr-4">
            <i class="fas fa-info-circle text-purple-400"></i>
          </div>
          <div>
            <h3 class="font-semibold mb-2">Conference App Now Available</h3>
            <p class="text-gray-300 mb-3">Download our official conference app to access schedules, maps, and networking features.</p>
            <div class="flex gap-3">
              <a href="#" class="text-sm bg-gray-700 hover:bg-gray-600 px-3 py-1 rounded-full">
                <i class="fab fa-apple mr-1"></i> App Store
              </a>
              <a href="#" class="text-sm bg-gray-700 hover:bg-gray-600 px-3 py-1 rounded-full">
                <i class="fab fa-google-play mr-1"></i> Play Store
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <section>
      <h2 class="text-2xl md:text-3xl font-bold font-orbitron mb-6">
        <i class="fas fa-users text-yellow-400 mr-3"></i>
        Networking Opportunities
      </h2>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="dashboard-card rounded-xl p-6 hover:border-cyan-400">
          <div class="bg-cyan-500/20 p-3 rounded-full w-12 h-12 flex items-center justify-center mb-4">
            <i class="fas fa-comments text-cyan-400"></i>
          </div>
          <h3 class="font-semibold mb-2">Discussion Forums</h3>
          <p class="text-gray-300 mb-4">Join topic-specific discussion threads with other attendees.</p>
          <a href="#" class="text-cyan-400 hover:underline text-sm">Access Forums</a>
        </div>
        
        <div class="dashboard-card rounded-xl p-6 hover:border-purple-400">
          <div class="bg-purple-500/20 p-3 rounded-full w-12 h-12 flex items-center justify-center mb-4">
            <i class="fas fa-handshake text-purple-400"></i>
          </div>
          <h3 class="font-semibold mb-2">Meetup Sessions</h3>
          <p class="text-gray-300 mb-4">Schedule 1-on-1 meetings with other participants.</p>
          <a href="#" class="text-purple-400 hover:underline text-sm">Browse Sessions</a>
        </div>
        
        <div class="dashboard-card rounded-xl p-6 hover:border-yellow-400">
          <div class="bg-yellow-500/20 p-3 rounded-full w-12 h-12 flex items-center justify-center mb-4">
            <i class="fas fa-video text-yellow-400"></i>
          </div>
          <h3 class="font-semibold mb-2">Virtual Lounges</h3>
          <p class="text-gray-300 mb-4">Drop-in video chat rooms for informal networking.</p>
          <a href="#" class="text-yellow-400 hover:underline text-sm">Enter Lounge</a>
        </div>
      </div>
    </section>
  </main>
  
  <!-- Footer -->
  <footer class="mt-16 pt-8 border-t border-gray-800 text-center text-gray-500 text-sm">
    <p>Space Science & Geospatial Institute Conference 2025</p>
    <p class="mt-2">Need help? <a href="mailto:support@spacesciencegeo.org" class="text-cyan-400 hover:underline">support@spacesciencegeo.org</a></p>
  </footer>
</div>

<script>
  // Calculate days until conference
  const eventDate = new Date("August 20, 2025 00:00:00");
  const today = new Date();
  const timeDiff = eventDate - today;
  const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
  
  document.getElementById('daysUntilEvent').textContent = daysDiff > 0 ? daysDiff : "Started!";
  
  // Add animation to RSVPed badges
  document.querySelectorAll('[class*="bg-green-600"]').forEach(badge => {
    badge.classList.add('badge');
  });
</script>
</body>
</html>