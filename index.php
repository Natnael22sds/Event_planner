<?php
include 'config.php';

// Fetch latest event title and date from database using PDO
try {
    $stmt = $pdo->query("SELECT title, start_datetime, location FROM events ORDER BY start_datetime DESC LIMIT 1");
    $eventData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($eventData) {
        $eventTitle = $eventData['title'];  
        $eventDate  = $eventData['start_datetime'];
        $eventLocation  = $eventData['location'];

        // Convert datetime into JS-friendly format (YYYY-MM-DDTHH:MM:SS)
        $jsEventDate = date('Y-m-d\TH:i:s', strtotime($eventDate));
    } else {
        // Fallback values if no event found
        $eventTitle   = 'Sample Event';
        $jsEventDate  = '2025-08-20T09:00:00';
    }
} catch (PDOException $e) {
    $eventTitle   = 'Sample Event';
    $jsEventDate  = '2025-08-20T09:00:00';
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Space Science & Geospatial Institute - Event 2025</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #06f7ff;
      --secondary: #0ea5e9;
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background: radial-gradient(circle at 20% 20%, #020617, #000000);
      overflow-x: hidden;
      color: #e2e8f0;
    }
    
    h1, h2, h3, h4, .font-orbitron {
      font-family: 'Orbitron', sans-serif;
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
    
    html {
      scroll-behavior: smooth;
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
    }
    
    ::-webkit-scrollbar-track {
      background: #020617;
    }
    
    ::-webkit-scrollbar-thumb {
      background: var(--primary);
      border-radius: 4px;
    }
    
    /* Section fade-in animation */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    section {
      animation: fadeIn 0.8s ease-out forwards;
    }
    
    /* Delay animations for each section */
    section:nth-child(1) { animation-delay: 0.1s; }
    section:nth-child(2) { animation-delay: 0.3s; }
    section:nth-child(3) { animation-delay: 0.5s; }
    section:nth-child(4) { animation-delay: 0.7s; }
  </style>
</head>
<body class="min-h-screen">

<div class="stars"></div>

<!-- Header -->
<header class="text-center py-12 md:py-20 px-4 relative overflow-hidden">
  <!-- Animated background elements -->
  <div class="absolute inset-0 overflow-hidden opacity-20">
    <div class="absolute top-1/4 left-1/4 w-32 h-32 rounded-full bg-blue-500 blur-3xl animate-pulse"></div>
    <div class="absolute bottom-1/3 right-1/4 w-40 h-40 rounded-full bg-cyan-500 blur-3xl opacity-70 animate-pulse delay-300"></div>
  </div>
  
  <div class="relative z-10 max-w-4xl mx-auto">
    <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold glow mb-4">
     Science Museum
    </h1>
<div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-xl p-6 mb-8 border border-gray-700 shadow-xl overflow-hidden relative">
    <!-- Decorative elements -->
    <div class="absolute -top-4 -right-4 w-24 h-24 bg-blue-500/10 rounded-full"></div>
    <div class="absolute -bottom-4 -left-4 w-16 h-16 bg-purple-500/10 rounded-full"></div>
    
    <div class="relative z-10">
        <!-- Event Title with calendar icon -->
        <div class="flex items-center mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <h2 class="text-2xl md:text-3xl font-bold text-white tracking-tight">
                <?php echo htmlspecialchars($eventTitle); ?>
            </h2>
        </div>
        
        <!-- Location with pin icon -->
        <div class="flex items-center text-gray-300 pl-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="text-lg md:text-xl font-medium"><?php echo htmlspecialchars($eventLocation); ?></span>
        </div>
    <div id="countdown" class="mt-6 text-xl md:text-2xl font-bold text-cyan-400"></div>

    <!-- Floating Login Button -->
    <div class="mt-12 relative">
      <a href="login.php" class="login-btn inline-block px-8 py-3 font-bold text-lg rounded-full text-black relative z-10">
        <i class="fas fa-user-astronaut mr-2"></i> Login / Register
      </a>
      <div class="absolute -inset-2 bg-cyan-500 rounded-full blur opacity-75 animate-pulse-slow z-0"></div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="mt-16 animate-bounce">
      <a href="#about" class="inline-block">
        <i class="fas fa-chevron-down text-2xl text-gray-400 hover:text-cyan-400"></i>
      </a>
    </div>
  </div>
</header>

<style>
  /* Floating, glowing login button */
  .login-btn {
    background: linear-gradient(90deg, var(--primary), var(--secondary), var(--primary));
    background-size: 200% 200%;
    animation: glowMove 4s ease-in-out infinite;
    box-shadow: 0 0 15px var(--primary), 0 0 30px var(--primary) inset;
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
    z-index: 10;
  }

  .login-btn:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 0 25px var(--primary), 0 0 50px var(--primary) inset;
  }

  @keyframes glowMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }
  
  @keyframes pulse-slow {
    0%, 100% { opacity: 0.7; }
    50% { opacity: 0.3; }
  }
  
  .animate-pulse-slow {
    animation: pulse-slow 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  }
</style>


<!-- Navigation -->
<nav class="bg-black bg-opacity-70 backdrop-blur-md sticky top-0 z-50 border-b border-gray-800 shadow-lg">
  <div class="container mx-auto px-4">
    <div class="flex flex-wrap justify-center md:justify-between items-center py-3">
      
      <!-- Logo instead of text -->
      <a href="#" class="hidden md:block mr-10">
        <img src="src/images/ssgiLogo.webp" alt="SSGI Logo" class="h-10 w-auto">
      </a>
      
      <div class="flex flex-wrap justify-center gap-4 md:gap-6">
        <a href="#about" class="px-3 py-2 hover:text-cyan-400 transition-colors relative group">
          About
          <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-cyan-400 transition-all group-hover:w-full"></span>
        </a>
        <a href="#schedule" class="px-3 py-2 hover:text-cyan-400 transition-colors relative group">
          Schedule
          <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-cyan-400 transition-all group-hover:w-full"></span>
        </a>
        <a href="#speakers" class="px-3 py-2 hover:text-cyan-400 transition-colors relative group">
          Speakers
          <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-cyan-400 transition-all group-hover:w-full"></span>
        </a>
        <a href="#location" class="px-3 py-2 hover:text-cyan-400 transition-colors relative group">
          Location
          <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-cyan-400 transition-all group-hover:w-full"></span>
        </a>
      </div>
      
      <a href="login.php" class="hidden md:block text-sm bg-cyan-500 hover:bg-cyan-600 text-black font-bold py-2 px-4 rounded-full transition-colors ml-10">
        Register Now
      </a>
    </div>
  </div>
</nav>


<!-- About -->
<section id="about" class="py-16 px-4 sm:px-6">
  <div class="max-w-6xl mx-auto">
    <div class="text-center mb-12">
      <h2 class="text-3xl md:text-4xl font-bold glow mb-4">About the Event</h2>
      <div class="w-20 h-1 bg-cyan-400 mx-auto"></div>
    </div>
    
    <div class="grid md:grid-cols-2 gap-8 items-center">
      <div class="space-y-6">
        <p class="text-lg text-gray-300 leading-relaxed">
          Join global experts in space science, satellite technology, and geospatial intelligence for three days of
          inspiring talks, hands-on workshops, and networking opportunities at the most anticipated space technology event of the year.
        </p>
        <p class="text-lg text-gray-300 leading-relaxed">
          This year's theme <span class="text-cyan-400 font-semibold">"Beyond Horizons: Mapping the Future"</span> will explore breakthroughs in space exploration,
          Earth observation, and the integration of AI with geospatial technologies.
        </p>
        <div class="flex flex-wrap gap-4 mt-6">
          <div class="flex items-center">
            <i class="fas fa-users text-cyan-400 mr-2"></i>
            <span>1,200+ Attendees</span>
          </div>
          <div class="flex items-center">
            <i class="fas fa-microphone text-cyan-400 mr-2"></i>
            <span>50+ Speakers</span>
          </div>
          <div class="flex items-center">
            <i class="fas fa-globe-americas text-cyan-400 mr-2"></i>
            <span>40+ Countries</span>
          </div>
        </div>
      </div>
      
      <div class="relative">
        <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1472&q=80" 
             alt="Space technology" 
             class="rounded-lg shadow-2xl w-full h-auto border border-gray-700 hover:border-cyan-400 transition-all duration-300">
        <div class="absolute -inset-4 border border-cyan-400 rounded-lg opacity-0 hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
      </div>
    </div>
  </div>
</section>

<!-- Stats -->
<section class="py-12 bg-gradient-to-r from-gray-900 to-black bg-opacity-60">
  <div class="max-w-6xl mx-auto px-4 sm:px-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
      <div class="p-6 rounded-lg bg-gray-800 bg-opacity-60 border border-gray-700 hover:border-cyan-400 transition-all">
        <div class="text-3xl md:text-4xl font-bold text-cyan-400 mb-2">3</div>
        <div class="text-gray-300">Days</div>
      </div>
      <div class="p-6 rounded-lg bg-gray-800 bg-opacity-60 border border-gray-700 hover:border-cyan-400 transition-all">
        <div class="text-3xl md:text-4xl font-bold text-cyan-400 mb-2">18</div>
        <div class="text-gray-300">Workshops</div>
      </div>
      <div class="p-6 rounded-lg bg-gray-800 bg-opacity-60 border border-gray-700 hover:border-cyan-400 transition-all">
        <div class="text-3xl md:text-4xl font-bold text-cyan-400 mb-2">50+</div>
        <div class="text-gray-300">Speakers</div>
      </div>
      <div class="p-6 rounded-lg bg-gray-800 bg-opacity-60 border border-gray-700 hover:border-cyan-400 transition-all">
        <div class="text-3xl md:text-4xl font-bold text-cyan-400 mb-2">1200+</div>
        <div class="text-gray-300">Attendees</div>
      </div>
    </div>
  </div>
</section>

<!-- Schedule -->
<section id="schedule" class="py-16 px-4 sm:px-6 bg-black bg-opacity-40 backdrop-blur-md">
  <div class="max-w-6xl mx-auto">
    <div class="text-center mb-12">
      <h2 class="text-3xl md:text-4xl font-bold glow mb-4">Event Schedule</h2>
      <div class="w-20 h-1 bg-cyan-400 mx-auto"></div>
      <p class="mt-4 text-gray-400 max-w-2xl mx-auto">
        Our comprehensive schedule covers three days of intensive learning and networking
      </p>
    </div>
    
    <div class="grid md:grid-cols-3 gap-6">
      <!-- Day 1 -->
      <div class="p-6 rounded-lg bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 hover:border-cyan-400 transition-all transform hover:scale-[1.02] hover:shadow-lg hover:shadow-cyan-500/20">
        <div class="flex items-center mb-4">
          <div class="bg-cyan-500 text-black font-bold rounded-full w-10 h-10 flex items-center justify-center mr-4">1</div>
          <h3 class="text-xl font-semibold">Exploration Day</h3>
        </div>
        <ul class="space-y-3 text-gray-300">
          <li class="flex items-start">
            <i class="fas fa-rocket text-cyan-400 mt-1 mr-2"></i>
            <span>Keynotes on planetary science and space exploration</span>
          </li>
          <li class="flex items-start">
            <i class="fas fa-satellite text-cyan-400 mt-1 mr-2"></i>
            <span>New satellite technology showcase</span>
          </li>
          <li class="flex items-start">
            <i class="fas fa-network-wired text-cyan-400 mt-1 mr-2"></i>
            <span>Networking reception</span>
          </li>
        </ul>
      </div>
      
      <!-- Day 2 -->
      <div class="p-6 rounded-lg bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 hover:border-cyan-400 transition-all transform hover:scale-[1.02] hover:shadow-lg hover:shadow-cyan-500/20">
        <div class="flex items-center mb-4">
          <div class="bg-cyan-500 text-black font-bold rounded-full w-10 h-10 flex items-center justify-center mr-4">2</div>
          <h3 class="text-xl font-semibold">Technology Day</h3>
        </div>
        <ul class="space-y-3 text-gray-300">
          <li class="flex items-start">
            <i class="fas fa-cogs text-cyan-400 mt-1 mr-2"></i>
            <span>Workshops on satellite imaging</span>
          </li>
          <li class="flex items-start">
            <i class="fas fa-robot text-cyan-400 mt-1 mr-2"></i>
            <span>AI mapping applications</span>
          </li>
          <li class="flex items-start">
            <i class="fas fa-space-shuttle text-cyan-400 mt-1 mr-2"></i>
            <span>Space engineering masterclass</span>
          </li>
        </ul>
      </div>
      
      <!-- Day 3 -->
      <div class="p-6 rounded-lg bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 hover:border-cyan-400 transition-all transform hover:scale-[1.02] hover:shadow-lg hover:shadow-cyan-500/20">
        <div class="flex items-center mb-4">
          <div class="bg-cyan-500 text-black font-bold rounded-full w-10 h-10 flex items-center justify-center mr-4">3</div>
          <h3 class="text-xl font-semibold">Collaboration Day</h3>
        </div>
        <ul class="space-y-3 text-gray-300">
          <li class="flex items-start">
            <i class="fas fa-users text-cyan-400 mt-1 mr-2"></i>
            <span>Panel discussions with industry leaders</span>
          </li>
          <li class="flex items-start">
            <i class="fas fa-handshake text-cyan-400 mt-1 mr-2"></i>
            <span>Partnership opportunities</span>
          </li>
          <li class="flex items-start">
            <i class="fas fa-chart-line text-cyan-400 mt-1 mr-2"></i>
            <span>Future mission planning</span>
          </li>
        </ul>
      </div>
    </div>
    
    <div class="text-center mt-10">
      <a href="#" class="inline-flex items-center px-6 py-3 border border-cyan-400 text-cyan-400 rounded-full hover:bg-cyan-400 hover:text-black transition-colors">
        Download Full Schedule <i class="fas fa-download ml-2"></i>
      </a>
    </div>
  </div>
</section>

<!-- Speakers -->
<section id="speakers" class="py-16 px-4 sm:px-6">
  <div class="max-w-6xl mx-auto">
    <div class="text-center mb-12">
      <h2 class="text-3xl md:text-4xl font-bold glow mb-4">Featured Speakers</h2>
      <div class="w-20 h-1 bg-cyan-400 mx-auto"></div>
      <p class="mt-4 text-gray-400 max-w-2xl mx-auto">
        Learn from the brightest minds in space science and geospatial technology
      </p>
    </div>
    
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
      <!-- Speaker 1 -->
      <div class="group relative overflow-hidden rounded-lg bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 hover:border-cyan-400 transition-all transform hover:scale-[1.02]">
        <div class="relative overflow-hidden h-64">
          <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=688&q=80" 
               alt="Dr. Jane Smith" 
               class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
          <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-70"></div>
        </div>
        <div class="p-6">
          <h3 class="text-xl font-bold mb-1">Dr. Jane Smith</h3>
          <p class="text-cyan-400 mb-3">Astrophysicist, NASA</p>
          <p class="text-gray-400 text-sm">Leading researcher in exoplanet discovery and characterization using next-gen space telescopes.</p>
          <div class="mt-4 flex space-x-3">
            <a href="#" class="text-gray-400 hover:text-cyan-400"><i class="fab fa-twitter"></i></a>
            <a href="#" class="text-gray-400 hover:text-cyan-400"><i class="fab fa-linkedin"></i></a>
            <a href="#" class="text-gray-400 hover:text-cyan-400"><i class="fas fa-globe"></i></a>
          </div>
        </div>
      </div>
      
      <!-- Speaker 2 -->
      <div class="group relative overflow-hidden rounded-lg bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 hover:border-cyan-400 transition-all transform hover:scale-[1.02]">
        <div class="relative overflow-hidden h-64">
          <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" 
               alt="Prof. John Doe" 
               class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
          <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-70"></div>
        </div>
        <div class="p-6">
          <h3 class="text-xl font-bold mb-1">Prof. John Doe</h3>
          <p class="text-cyan-400 mb-3">Geospatial Analyst, ESA</p>
          <p class="text-gray-400 text-sm">Pioneer in AI-driven geospatial analysis for climate change monitoring and disaster response.</p>
          <div class="mt-4 flex space-x-3">
            <a href="#" class="text-gray-400 hover:text-cyan-400"><i class="fab fa-twitter"></i></a>
            <a href="#" class="text-gray-400 hover:text-cyan-400"><i class="fab fa-linkedin"></i></a>
            <a href="#" class="text-gray-400 hover:text-cyan-400"><i class="fas fa-globe"></i></a>
          </div>
        </div>
      </div>
      
      <!-- Speaker 3 -->
      <div class="group relative overflow-hidden rounded-lg bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 hover:border-cyan-400 transition-all transform hover:scale-[1.02]">
        <div class="relative overflow-hidden h-64">
          <img src="https://images.unsplash.com/photo-1544717305-2782549b5136?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" 
               alt="Dr. Sarah Chen" 
               class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
          <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-70"></div>
        </div>
        <div class="p-6">
          <h3 class="text-xl font-bold mb-1">Dr. Sarah Chen</h3>
          <p class="text-cyan-400 mb-3">Space Systems Engineer, SpaceX</p>
          <p class="text-gray-400 text-sm">Expert in reusable rocket technology and satellite constellation deployment strategies.</p>
          <div class="mt-4 flex space-x-3">
            <a href="#" class="text-gray-400 hover:text-cyan-400"><i class="fab fa-twitter"></i></a>
            <a href="#" class="text-gray-400 hover:text-cyan-400"><i class="fab fa-linkedin"></i></a>
            <a href="#" class="text-gray-400 hover:text-cyan-400"><i class="fas fa-globe"></i></a>
          </div>
        </div>
      </div>
    </div>
    
    <div class="text-center mt-10">
      <a href="#" class="inline-flex items-center px-6 py-3 border border-cyan-400 text-cyan-400 rounded-full hover:bg-cyan-400 hover:text-black transition-colors">
        View All Speakers <i class="fas fa-arrow-right ml-2"></i>
      </a>
    </div>
  </div>
</section>

<!-- Location -->
<section id="location" class="py-16 px-4 sm:px-6 bg-black bg-opacity-40 backdrop-blur-md">
  <div class="max-w-6xl mx-auto">
    <div class="text-center mb-12">
      <h2 class="text-3xl md:text-4xl font-bold glow mb-4">Event Location</h2>
      <div class="w-20 h-1 bg-cyan-400 mx-auto"></div>
      <p class="mt-4 text-gray-400 max-w-2xl mx-auto">
        Addis Ababa International Convention Centre, Ethiopia
      </p>
    </div>
    
    <div class="grid md:grid-cols-2 gap-8 items-center">
      <div class="space-y-6">
        <div class="flex items-start">
          <i class="fas fa-map-marker-alt text-cyan-400 mt-1 mr-4 text-xl"></i>
          <div>
            <h3 class="text-xl font-semibold mb-1">Address</h3>
            <p class="text-gray-400">Convention Square, 4 kilo, Addis Ababa, 8001, Ethiopia</p>
          </div>
        </div>
        
        <div class="flex items-start">
          <i class="fas fa-calendar-alt text-cyan-400 mt-1 mr-4 text-xl"></i>
          <div>
            <h3 class="text-xl font-semibold mb-1">Date & Time</h3>
            <p class="text-gray-400">August 20-22, 2025<br>9:00 AM - 6:00 PM daily</p>
          </div>
        </div>
        
        <div class="flex items-start">
          <i class="fas fa-hotel text-cyan-400 mt-1 mr-4 text-xl"></i>
          <div>
            <h3 class="text-xl font-semibold mb-1">Accommodation</h3>
            <p class="text-gray-400">Special rates available at nearby hotels for conference attendees</p>
            <a href="#" class="text-cyan-400 hover:underline mt-2 inline-block">View hotel options</a>
          </div>
        </div>
      </div>
      
     <div class="h-80 md:h-96 rounded-lg overflow-hidden border border-gray-700 hover:border-cyan-400 transition-all">
        <iframe 
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.4789851700934!2d38.76038557314382!3d9.019994789133706!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x164b8585f0126257%3A0x65dd1dc0f4bc9ffd!2sSpace%20Science%20and%20Geospatial%20Institute!5e0!3m2!1sen!2set!4v1755425137777!5m2!1sen!2set" 
          width="100%" 
          height="100%" 
          style="border:0;" 
          allowfullscreen="" 
          loading="lazy" 
          referrerpolicy="no-referrer-when-downgrade"
          class="hover:scale-105 transition-transform duration-300">
        </iframe>
    </div>

    </div>
  </div>
</section>

<!-- CTA -->
<section class="py-16 px-4 sm:px-6 bg-gradient-to-r from-cyan-900 to-blue-900">
  <div class="max-w-4xl mx-auto text-center">
    <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Explore the Future With Us?</h2>
    <p class="text-xl text-gray-300 mb-8">
      Register now to secure your spot at the premier space science and geospatial technology event of 2025
    </p>
    <div class="flex flex-col sm:flex-row justify-center gap-4">
      <a href="#" class="px-8 py-4 bg-black text-white rounded-full font-bold hover:bg-gray-900 transition-colors">
        Register Now <i class="fas fa-arrow-right ml-2"></i>
      </a>
      <a href="#" class="px-8 py-4 border border-white text-white rounded-full font-bold hover:bg-white hover:text-black transition-colors">
        Request Information
      </a>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="py-12 px-4 sm:px-6 bg-black bg-opacity-90">
  <div class="max-w-6xl mx-auto">
    <div class="grid md:grid-cols-4 gap-8">
      
      <!-- Logo instead of SSGI 2025 -->
      <div class="mb-8">
        <a href="#" class="inline-block mb-4">
          <img src="src/images/ssgiLogo.webp" alt="SSGI Logo" class="h-12 w-auto">
        </a>
        <p class="text-gray-400">
          The premier conference for space science and geospatial technology professionals, researchers, and enthusiasts.
        </p>
      </div>
      
      <div class="mb-8">
        <h3 class="text-xl font-bold mb-4">Quick Links</h3>
        <ul class="space-y-2">
          <li><a href="#about" class="text-gray-400 hover:text-cyan-400 transition-colors">About</a></li>
          <li><a href="#schedule" class="text-gray-400 hover:text-cyan-400 transition-colors">Schedule</a></li>
          <li><a href="#speakers" class="text-gray-400 hover:text-cyan-400 transition-colors">Speakers</a></li>
          <li><a href="#location" class="text-gray-400 hover:text-cyan-400 transition-colors">Location</a></li>
        </ul>
      </div>
      
      <div class="mb-8">
        <h3 class="text-xl font-bold mb-4">Contact</h3>
        <ul class="space-y-2 text-gray-400">
          <li class="flex items-start">
            <i class="fas fa-envelope mt-1 mr-2 text-cyan-400"></i>
            <span>info@spacesciencegeo.org</span>
          </li>
          <li class="flex items-start">
            <i class="fas fa-phone-alt mt-1 mr-2 text-cyan-400"></i>
            <span>+251 11 551 5901</span>
          </li>
          <li class="flex items-start">
            <i class="fas fa-map-marker-alt mt-1 mr-2 text-cyan-400"></i>
            <span>Addis Ababa, Ethiopia</span>
          </li>
        </ul>
      </div>
      
      <div class="mb-8">
        <h3 class="text-xl font-bold mb-4">Follow Us</h3>
        <div class="flex space-x-4">
          <a href="#" class="text-gray-400 hover:text-cyan-400 transition-colors text-xl"><i class="fab fa-twitter"></i></a>
          <a href="#" class="text-gray-400 hover:text-cyan-400 transition-colors text-xl"><i class="fab fa-linkedin"></i></a>
          <a href="#" class="text-gray-400 hover:text-cyan-400 transition-colors text-xl"><i class="fab fa-facebook"></i></a>
          <a href="#" class="text-gray-400 hover:text-cyan-400 transition-colors text-xl"><i class="fab fa-instagram"></i></a>
          <a href="#" class="text-gray-400 hover:text-cyan-400 transition-colors text-xl"><i class="fab fa-youtube"></i></a>
        </div>
        
        <h3 class="text-xl font-bold mt-6 mb-4">Newsletter</h3>
        <form class="flex">
          <input type="email" placeholder="Your email" class="px-4 py-2 bg-gray-800 text-white rounded-l-full focus:outline-none focus:ring-2 focus:ring-cyan-400 w-full">
          <button type="submit" class="bg-cyan-500 text-black px-4 py-2 rounded-r-full hover:bg-cyan-600 transition-colors">
            <i class="fas fa-paper-plane"></i>
          </button>
        </form>
      </div>
    </div>
    
    <div class="border-t border-gray-800 pt-8 mt-8 text-center text-gray-500">
      <p>&copy; 2025 Space Science & Geospatial Institute. All rights reserved.</p>
      <div class="flex justify-center space-x-4 mt-4">
        <a href="#" class="hover:text-cyan-400 transition-colors">Privacy Policy</a>
        <a href="#" class="hover:text-cyan-400 transition-colors">Terms of Service</a>
        <a href="#" class="hover:text-cyan-400 transition-colors">Code of Conduct</a>
      </div>
    </div>
  </div>
</footer>


<!-- Back to top button -->
<button id="backToTop" class="fixed bottom-8 right-8 bg-cyan-500 text-black w-12 h-12 rounded-full flex items-center justify-center shadow-lg hover:bg-cyan-600 transition-colors opacity-0 invisible transition-all duration-300">
  <i class="fas fa-arrow-up"></i>
</button>

<!-- Countdown Script -->
<script>


// Get the event date from PHP
const eventDate = new Date("<?php echo $jsEventDate; ?>").getTime();
const countdown = document.getElementById('countdown');

function updateCountdown() {
  const now = new Date().getTime();
  const diff = eventDate - now;
  
  if (diff < 0) {
    countdown.innerHTML = "The event has started!";
    return;
  }
  
  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((diff % (1000 * 60)) / 1000);
  
  countdown.innerHTML = `
    <span class="countdown-segment">
      <span class="countdown-number">${days}</span>
      <span class="countdown-label">days</span>
    </span>
    <span class="countdown-segment">
      <span class="countdown-number">${hours}</span>
      <span class="countdown-label">hours</span>
    </span>
    <span class="countdown-segment">
      <span class="countdown-number">${minutes}</span>
      <span class="countdown-label">minutes</span>
    </span>
    <span class="countdown-segment">
      <span class="countdown-number">${seconds}</span>
      <span class="countdown-label">seconds</span>
    </span>
  `;
}

// Update every second
setInterval(updateCountdown, 1000);
updateCountdown(); // Initial call

// Back to top button
const backToTopBtn = document.getElementById('backToTop');

window.addEventListener('scroll', () => {
  if (window.pageYOffset > 300) {
    backToTopBtn.classList.remove('opacity-0', 'invisible');
    backToTopBtn.classList.add('opacity-100', 'visible');
  } else {
    backToTopBtn.classList.remove('opacity-100', 'visible');
    backToTopBtn.classList.add('opacity-0', 'invisible');
  }
});

backToTopBtn.addEventListener('click', () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
});




// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    e.preventDefault();
    
    const targetId = this.getAttribute('href');
    if (targetId === '#') return;
    
    const targetElement = document.querySelector(targetId);
    if (targetElement) {
      targetElement.scrollIntoView({
        behavior: 'smooth'
      });
    }
  });
});
</script>

</body>
</html>