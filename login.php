<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT id, name, password_hash, role FROM eusers WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        if (strtolower($user['role']) === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SSGI 2025 - Login</title>
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
    
    .login-container {
      background: rgba(15, 23, 42, 0.8);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(6, 247, 255, 0.2);
      box-shadow: 0 0 30px rgba(6, 247, 255, 0.1);
    }
    
    .input-field {
      background: rgba(30, 41, 59, 0.5);
      border: 1px solid rgba(148, 163, 184, 0.2);
      transition: all 0.3s ease;
    }
    
    .input-field:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(6, 247, 255, 0.2);
    }
    
    .login-btn {
      background: linear-gradient(90deg, var(--primary), var(--secondary));
      background-size: 200% 200%;
      animation: glowMove 4s ease-in-out infinite;
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .login-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(6, 247, 255, 0.3);
    }
    
    @keyframes glowMove {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
  </style>
</head>
<body>
<div class="stars"></div>

<div class="flex items-center justify-center min-h-screen px-4">
  <div class="login-container rounded-xl p-8 w-full max-w-md relative overflow-hidden">
    <!-- Animated background elements -->
    <div class="absolute -top-20 -left-20 w-40 h-40 rounded-full bg-blue-500 blur-3xl opacity-20"></div>
    <div class="absolute -bottom-20 -right-20 w-40 h-40 rounded-full bg-cyan-500 blur-3xl opacity-20"></div>
    
    <div class="relative z-10">
      <!-- Logo/Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold font-orbitron glow mb-2">SSGI 2025</h1>
        <p class="text-gray-400">Space Science & Geospatial Institute</p>
      </div>
      
      <!-- Error Message -->
      <?php if (!empty($error)): ?>
        <div class="bg-red-900/50 border border-red-700 text-red-200 px-4 py-3 rounded-lg mb-6 flex items-start">
          <i class="fas fa-exclamation-circle mt-1 mr-3"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>
      
      <!-- Login Form -->
      <form method="POST" class="space-y-6">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-envelope text-gray-500"></i>
            </div>
            <input 
              type="email" 
              id="email" 
              name="email" 
              placeholder="your@email.com" 
              required 
              class="input-field w-full pl-10 pr-3 py-3 rounded-lg text-white placeholder-gray-500 focus:outline-none"
            >
          </div>
        </div>
        
        <div>
          <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Password</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-lock text-gray-500"></i>
            </div>
            <input 
              type="password" 
              id="password" 
              name="password" 
              placeholder="••••••••" 
              required 
              class="input-field w-full pl-10 pr-3 py-3 rounded-lg text-white placeholder-gray-500 focus:outline-none"
            >
          </div>
        </div>
        
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input 
              id="remember-me" 
              name="remember-me" 
              type="checkbox" 
              class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-cyan-500 focus:ring-cyan-500"
            >
            <label for="remember-me" class="ml-2 block text-sm text-gray-300">Remember me</label>
          </div>
          
          <div class="text-sm">
            <a href="#" class="text-cyan-400 hover:text-cyan-300">Forgot password?</a>
          </div>
        </div>
        
        <button type="submit" class="login-btn w-full py-3 px-4 rounded-lg font-bold text-black">
          <i class="fas fa-user-astronaut mr-2"></i> Login
        </button>
      </form>
      
     
      
      <!-- Registration status -->
      <div class="mt-8 text-center text-sm text-gray-400">
        Don't have an account? 
        <a href="#" class="text-cyan-400 hover:text-cyan-300 font-medium">Ask your department head to find one</a>
      </div>
    </div>
  </div>
</div>

<script>
  // Simple animation for the form when page loads
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    form.style.opacity = '0';
    form.style.transform = 'translateY(20px)';
    form.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    
    setTimeout(() => {
      form.style.opacity = '1';
      form.style.transform = 'translateY(0)';
    }, 100);
  });
</script>
</body>
</html>