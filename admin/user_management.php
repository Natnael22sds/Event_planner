<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check admin role
$stmtRole = $pdo->prepare("SELECT role FROM eusers WHERE id = ?");
$stmtRole->execute([$_SESSION['user_id']]);
$user = $stmtRole->fetch();
if (!$user || $user['role'] !== 'admin') {
    die('Access denied. Admins only.');
}

$error = '';
$success = '';

// Handle new user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    // Basic validation
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif (!in_array($role, ['user', 'admin'])) {
        $error = "Invalid role selected.";
    }

    if (!$error) {
        // Check if email exists
        $stmtCheck = $pdo->prepare("SELECT id FROM eusers WHERE email = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetch()) {
            $error = "Email already exists.";
        } else {
            // Insert new user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmtInsert = $pdo->prepare("INSERT INTO eusers (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $stmtInsert->execute([$name, $email, $hash, $role]);
            $success = "User created successfully!";
        }
    }
}

// Fetch all users
$stmtUsers = $pdo->query("SELECT id, email, role FROM eusers ORDER BY id DESC");
$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SSGI 2025 - User Management</title>
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
    
    .admin-card {
      background: rgba(15, 23, 42, 0.8);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(6, 247, 255, 0.2);
    }
    
    .input-field {
      background: rgba(30, 41, 59, 0.5);
      border: 1px solid rgba(148, 163, 184, 0.2);
      transition: all 0.3s ease;
    }
    
    .input-field:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(6, 247, 255, 0.2);
      outline: none;
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
    
    .role-badge {
      font-size: 0.75rem;
      padding: 0.25rem 0.5rem;
      border-radius: 9999px;
    }
    
    .role-badge.admin {
      background-color: rgba(234, 179, 8, 0.2);
      color: #eab308;
      border: 1px solid rgba(234, 179, 8, 0.3);
    }
    
    .role-badge.user {
      background-color: rgba(6, 182, 212, 0.2);
      color: #06b6d4;
      border: 1px solid rgba(6, 182, 212, 0.3);
    }
    
    .table-row:hover {
      background-color: rgba(30, 41, 59, 0.5);
    }
  </style>
</head>
<body>
<div class="stars"></div>

<div class="container mx-auto px-4 py-8 max-w-6xl">
  <!-- Header -->
  <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10">
    <div>
      <h1 class="text-3xl md:text-4xl font-bold font-orbitron glow mb-2">User Management</h1>
      <p class="text-gray-400">Admin control panel for Space Science & Geospatial Institute Conference</p>
    </div>
    <a href="dashboard.php" class="text-cyan-400 hover:underline flex items-center mt-4 md:mt-0">
      <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
    </a>
  </div>

  <!-- Status Messages -->
  <?php if ($error): ?>
    <div class="bg-red-900/80 border border-red-700 text-red-200 px-6 py-4 rounded-lg mb-8 flex items-start">
      <i class="fas fa-exclamation-circle mt-1 mr-3 text-xl"></i>
      <div>
        <h3 class="font-semibold">Error</h3>
        <p><?= htmlspecialchars($error) ?></p>
      </div>
    </div>
  <?php elseif ($success): ?>
    <div class="bg-green-900/80 border border-green-700 text-green-200 px-6 py-4 rounded-lg mb-8 flex items-start">
      <i class="fas fa-check-circle mt-1 mr-3 text-xl"></i>
      <div>
        <h3 class="font-semibold">Success!</h3>
        <p><?= htmlspecialchars($success) ?></p>
      </div>
    </div>
  <?php endif; ?>

  <!-- Create User Form -->
  <div class="admin-card rounded-xl p-8 mb-10">
    <h2 class="text-2xl font-semibold mb-6 flex items-center">
      <i class="fas fa-user-plus text-cyan-400 mr-3"></i>
      Create New User
    </h2>
    
    <form method="POST" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div>
          <label class="block text-lg font-semibold mb-2">
            <span class="text-cyan-400">*</span> Full Name
          </label>
          <input 
            id="name" 
            name="name" 
            type="text" 
            required 
            class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-500 focus:outline-none"
            placeholder="Your Name"
          />
        </div>


        <div>
          <label class="block text-lg font-semibold mb-2">
            <span class="text-cyan-400">*</span> Email Address
          </label>
          <input 
            id="email" 
            name="email" 
            type="email" 
            required 
            class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-500 focus:outline-none"
            placeholder="user@example.com"
          />
        </div>
        
        <div>
          <label class="block text-lg font-semibold mb-2">
            <span class="text-cyan-400">*</span> Password
          </label>
          <input 
            id="password" 
            name="password" 
            type="password" 
            required 
            minlength="6"
            class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-500 focus:outline-none"
            placeholder="At least 6 characters"
          />
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-lg font-semibold mb-2">
            <span class="text-cyan-400">*</span> User Role
          </label>
          <select 
            id="role" 
            name="role" 
            required 
            class="input-field w-full px-4 py-3 rounded-lg text-white focus:outline-none"
          >
            <option value="user">Standard User</option>
            <option value="admin">Administrator</option>
          </select>
        </div>
        
        <div class="flex items-end">
          <button type="submit" class="btn-primary px-8 py-3 rounded-full font-bold text-black w-full md:w-auto">
            <i class="fas fa-user-astronaut mr-2"></i> Create User
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- Users List -->
  <div class="admin-card rounded-xl p-8">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-semibold flex items-center">
        <i class="fas fa-users text-cyan-400 mr-3"></i>
        User Accounts
      </h2>
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <i class="fas fa-search text-gray-500"></i>
        </div>
        <input 
          type="text" 
          placeholder="Search users..." 
          class="pl-10 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-full focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
          id="userSearch"
        >
      </div>
    </div>
    
    <?php if ($users): ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="border-b border-gray-700 text-gray-400">
              <th class="py-4 px-4 font-medium">ID</th>
              <th class="py-4 px-4 font-medium">Email</th>
              <th class="py-4 px-4 font-medium">Role</th>
              <th class="py-4 px-4 font-medium text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
              <tr class="border-b border-gray-700 table-row">
                <td class="py-4 px-4"><?= htmlspecialchars($u['id']) ?></td>
                <td class="py-4 px-4"><?= htmlspecialchars($u['email']) ?></td>
                <td class="py-4 px-4">
                  <span class="role-badge <?= $u['role'] ?>">
                    <?= htmlspecialchars(ucfirst($u['role'])) ?>
                  </span>
                </td>
                <td class="py-4 px-4 text-right">
                  <div class="flex justify-end space-x-2">
                    <a href="edit_user.php?id=<?= $u['id'] ?>" class="text-cyan-400 hover:text-cyan-300 px-3 py-1 rounded-full text-sm">
                      <i class="fas fa-edit"></i>
                    </a>
                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                      <a href="delete_user.php?id=<?= $u['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');" class="text-red-400 hover:text-red-300 px-3 py-1 rounded-full text-sm">
                        <i class="fas fa-trash-alt"></i>
                      </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination would go here -->
      <div class="mt-6 flex justify-between items-center text-sm text-gray-400">
        <div>Showing 1 to <?= count($users) ?> of <?= count($users) ?> entries</div>
        <div class="flex space-x-2">
          <button class="px-3 py-1 rounded bg-gray-800 disabled">Previous</button>
          <button class="px-3 py-1 rounded bg-gray-800 disabled">Next</button>
        </div>
      </div>
    <?php else: ?>
      <div class="text-center py-12">
        <i class="fas fa-user-slash text-4xl text-gray-600 mb-4"></i>
        <h3 class="text-xl font-medium text-gray-400">No users found</h3>
        <p class="text-gray-500 mt-2">Create your first user account</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  // Simple search functionality
  document.getElementById('userSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
      const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
      if (email.includes(searchTerm)) {
        row.style.display = 'table-row';
      } else {
        row.style.display = 'none';
      }
    });
  });
  
  // Animation for form when page loads
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    if (form) {
      form.style.opacity = '0';
      form.style.transform = 'translateY(20px)';
      form.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      
      setTimeout(() => {
        form.style.opacity = '1';
        form.style.transform = 'translateY(0)';
      }, 100);
    }
  });
</script>
</body>
</html>