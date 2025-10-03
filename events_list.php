<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch all events with organizer names
$stmt = $pdo->query("
    SELECT e.*, u.name AS organizer_name 
    FROM events e 
    JOIN eusers u ON e.organizer_id = u.id 
    ORDER BY start_datetime ASC
");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch RSVPs for current user
$rsvpStmt = $pdo->prepare("SELECT event_id, rsvp_status FROM event_rsvps WHERE user_id = :user_id");
$rsvpStmt->execute([':user_id' => $_SESSION['user_id']]);
$userRsvps = $rsvpStmt->fetchAll(PDO::FETCH_KEY_PAIR); // [event_id => rsvp_status]

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SSGI 2025 - Upcoming Events</title>
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
    
    .event-card {
      background: rgba(15, 23, 42, 0.8);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(6, 247, 255, 0.2);
      transition: all 0.3s ease;
    }
    
    .event-card:hover {
      border-color: var(--primary);
      box-shadow: 0 0 20px rgba(6, 247, 255, 0.1);
      transform: translateY(-5px);
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
    
    .rsvp-active {
      box-shadow: 0 0 0 2px white;
    }
  </style>
</head>
<body>
<div class="stars"></div>

<div class="container mx-auto px-4 py-8 max-w-7xl">
  <!-- Header -->
  <header class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="text-3xl md:text-4xl font-bold font-orbitron glow mb-2">Upcoming Events</h1>
        <p class="text-gray-400">Explore all upcoming Space Science & Geospatial Institute events</p>
      </div>
      
      <div class="flex flex-wrap gap-3">
        <a href="calendar.php" class="bg-purple-600 hover:bg-purple-700 px-6 py-2 rounded-full font-medium flex items-center">
          <i class="fas fa-calendar-alt mr-2"></i> Calendar View
        </a>
       <a href="dashboard.php" class="relative group bg-gradient-to-br from-purple-600 to-purple-800 hover:from-purple-700 hover:to-purple-900 px-6 py-2.5 rounded-full font-medium flex items-center text-white shadow-lg hover:shadow-purple-500/30 transition-all duration-300">
            <i class="fas fa-arrow-left mr-2 text-purple-200 group-hover:text-white transition-colors"></i>
            <span>Go back to dashboard</span>
            <div class="absolute inset-0 rounded-full border border-purple-400/30 group-hover:border-purple-300/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
        </a>
      </div>
    </div>
    
    <!-- Filters -->
    <div class="mt-8 flex flex-wrap gap-4 items-center">
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <i class="fas fa-search text-gray-500"></i>
        </div>
        <input 
          type="text" 
          placeholder="Search events..." 
          class="pl-10 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-full focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
          id="searchInput"
        >
      </div>
      
      <select class="bg-gray-800 border border-gray-700 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
        <option>All Categories</option>
        <option>Keynotes</option>
        <option>Workshops</option>
        <option>Networking</option>
        <option>Social Events</option>
      </select>
      
      <select class="bg-gray-800 border border-gray-700 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
        <option>Sort by Date</option>
        <option>Soonest First</option>
        <option>Latest First</option>
        <option>Most Popular</option>
      </select>
    </div>
  </header>

  <!-- Events Grid -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($events as $event): ?>
      <div class="event-card rounded-xl overflow-hidden">
        <!-- Event Image -->
        <div class="h-48 bg-gradient-to-r from-cyan-900 to-blue-900 relative overflow-hidden">
          <?php if (!empty($event['image_url'])): ?>
            <img src="<?= htmlspecialchars($event['image_url']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="w-full h-full object-cover">
          <?php else: ?>
            <div class="absolute inset-0 flex items-center justify-center">
              <i class="fas fa-calendar-star text-5xl opacity-20"></i>
            </div>
          <?php endif; ?>
          <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent h-16"></div>
          <div class="absolute top-4 right-4">
            <?php if ($event['organizer_id'] == $_SESSION['user_id']): ?>
              <span class="bg-yellow-600/80 text-yellow-100 px-3 py-1 rounded-full text-xs font-medium">
                Your Event
              </span>
            <?php endif; ?>
          </div>
        </div>
        
        <!-- Event Content -->
        <div class="p-6">
          <div class="flex justify-between items-start mb-3">
            <h2 class="text-xl font-semibold"><?= htmlspecialchars($event['title']) ?></h2>
            <div class="text-sm bg-gray-700 px-2 py-1 rounded-full">
              <?= date('M j', strtotime($event['start_datetime'])) ?>
            </div>
          </div>
          
          <div class="flex items-center text-gray-400 text-sm mb-3">
            <i class="far fa-clock mr-2"></i>
            <span>
              <?= date('g:i A', strtotime($event['start_datetime'])) ?> - 
              <?= date('g:i A', strtotime($event['end_datetime'])) ?>
            </span>
          </div>
          
          <div class="flex items-center text-gray-400 text-sm mb-4">
            <i class="fas fa-map-marker-alt mr-2"></i>
            <span><?= htmlspecialchars($event['location']) ?></span>
          </div>
          
          <p class="text-gray-300 mb-4 line-clamp-3"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
          
          <div class="flex items-center text-sm text-gray-500 mb-4">
            <i class="fas fa-user-astronaut mr-2"></i>
            <span>Organized by <?= htmlspecialchars($event['organizer_name']) ?></span>
          </div>
          
          <!-- Action Buttons -->
          <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-800">
            <?php if ($event['organizer_id'] == $_SESSION['user_id']): ?>
              <a href="attendees.php?event_id=<?= $event['id'] ?>" 
                 class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-full text-sm font-medium flex items-center">
                <i class="fas fa-users mr-2"></i> Attendees
              </a>
              <a href="edit_event.php?id=<?= $event['id'] ?>" 
                 class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-full text-sm font-medium flex items-center">
                <i class="fas fa-edit mr-2"></i> Edit
              </a>
            <?php endif; ?>
            
            <!-- RSVP Form -->
            <?php if (isset($userRsvps[$event['id']])): ?>
<div class="text-xs font-orbitron tracking-wider px-3 py-1 rounded-full inline-flex items-center bg-green-900/70 text-green-300 border border-green-500/20 shadow-lg shadow-green-900/30">
    <span class="mr-1">STATUS: RSVPed</span>
    <span class="capitalize text-white font-bold"><?= htmlspecialchars($userRsvps[$event['id']]) ?></span>
</div>
<?php else: ?>
    <!-- RSVP Form -->
    <form action="rsvp.php" method="POST" class="flex flex-wrap gap-2">
        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">

        <button type="submit" name="status" value="going" 
                class="bg-green-600 hover:bg-green-700 px-3 py-1 rounded-full text-xs font-medium flex items-center">
            <i class="fas fa-check-circle mr-1"></i> Going
        </button>

        <button type="submit" name="status" value="maybe" 
                class="bg-yellow-600 hover:bg-yellow-700 px-3 py-1 rounded-full text-xs font-medium flex items-center">
            <i class="fas fa-question-circle mr-1"></i> Maybe
        </button>

        <button type="submit" name="status" value="not_going" 
                class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded-full text-xs font-medium flex items-center">
            <i class="fas fa-times-circle mr-1"></i> Decline
        </button>
    </form>
<?php endif; ?>

          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  
  <!-- Empty State -->
  <?php if (empty($events)): ?>
    <div class="text-center py-16">
      <div class="mx-auto w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mb-6">
        <i class="fas fa-calendar-plus text-3xl text-gray-600"></i>
      </div>
      <h3 class="text-xl font-medium text-gray-400 mb-2">No upcoming events found</h3>
      <p class="text-gray-500 mb-6">Would you like to create the first event?</p>
      <a href="add_event.php" class="btn-primary px-6 py-2 rounded-full font-medium text-black inline-flex items-center">
        <i class="fas fa-plus mr-2"></i> Add New Event
      </a>
    </div>
  <?php endif; ?>
  
  <!-- Pagination -->
  <div class="mt-12 flex justify-center">
    <nav class="flex items-center gap-1">
      <a href="#" class="px-3 py-1 rounded-full bg-gray-800 text-gray-400 hover:bg-gray-700">
        <i class="fas fa-chevron-left"></i>
      </a>
      <a href="#" class="px-4 py-1 rounded-full bg-cyan-600 text-white">1</a>
      <a href="#" class="px-4 py-1 rounded-full bg-gray-800 text-gray-400 hover:bg-gray-700">2</a>
      <a href="#" class="px-4 py-1 rounded-full bg-gray-800 text-gray-400 hover:bg-gray-700">3</a>
      <span class="px-2 text-gray-500">...</span>
      <a href="#" class="px-4 py-1 rounded-full bg-gray-800 text-gray-400 hover:bg-gray-700">8</a>
      <a href="#" class="px-3 py-1 rounded-full bg-gray-800 text-gray-400 hover:bg-gray-700">
        <i class="fas fa-chevron-right"></i>
      </a>
    </nav>
  </div>
</div>

<script>
  // Simple search functionality
  document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const eventCards = document.querySelectorAll('.event-card');
    
    eventCards.forEach(card => {
      const title = card.querySelector('h2').textContent.toLowerCase();
      const description = card.querySelector('p').textContent.toLowerCase();
      
      if (title.includes(searchTerm) || description.includes(searchTerm)) {
        card.style.display = 'block';
      } else {
        card.style.display = 'none';
      }
    });
  });
  
  // Highlight active RSVP button
  document.querySelectorAll('[class*="rsvp-active"]').forEach(btn => {
    btn.classList.add('ring-2', 'ring-white');
  });
</script>
</body>
</html>