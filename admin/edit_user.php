<?php
session_start();
require '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$currentUserId = $_SESSION['user_id'];

// Fetch logged-in user's ID and role
$stmt = $pdo->prepare("SELECT id, role FROM eusers WHERE id = :id");
$stmt->execute([':id' => $currentUserId]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser) {
    die("Current user not found.");
}

// Determine which user to edit
$userId = isset($_GET['id']) ? (int)$_GET['id'] : $currentUserId;

// Non-admins can only edit their own profile
if ($currentUser['role'] !== 'admin' && $userId !== $currentUserId) {
    die("You don't have permission to edit this account.");
}

// Fetch user data for the edit form
$stmt = $pdo->prepare("SELECT id, name, email, role FROM eusers WHERE id = :id");
$stmt->execute([':id' => $userId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    die("User not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role  = ($currentUser['role'] === 'admin') ? $_POST['role'] : $userData['role']; // Only admin can change role
    $password = trim($_POST['password_hash']);

    // Basic validation
    if (empty($name) || empty($email)) {
        $error = "Name and email cannot be empty.";
    } else {
        try {
            if (!empty($password)) {
                // Update with password change
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE eusers
                    SET name = :name, email = :email, password = :password_hash, role = :role 
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':password_hash' => $hashedPassword,
                    ':role' => $role,
                    ':id' => $userId
                ]);
            } else {
                // Update without password change
                $stmt = $pdo->prepare("
                    UPDATE eusers
                    SET name = :name, email = :email, role = :role 
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':role' => $role,
                    ':id' => $userId
                ]);
            }

            $_SESSION['success_msg'] = "User updated successfully.";
            header("Location: user_management.php");
            exit;

        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SSGI 2025 - Edit User</title>
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
  </style>
</head>
<body>
<div class="stars"></div>

<div class="container mx-auto px-4 py-8 max-w-4xl">
  <!-- Header -->
  <div class="flex justify-between items-start mb-8">
    <div>
      <h1 class="text-3xl md:text-4xl font-bold font-orbitron glow mb-2">Edit User Profile</h1>
      <p class="text-gray-400">Update user details for Space Science & Geospatial Institute</p>
    </div>
    <a href="user_management.php" class="text-cyan-400 hover:underline flex items-center">
      <i class="fas fa-arrow-left mr-2"></i> Back to Users
    </a>
  </div>

  <!-- Error Message -->
  <?php if (!empty($error)): ?>
    <div class="bg-red-900/80 border border-red-700 text-red-200 px-6 py-4 rounded-lg mb-8 flex items-start">
      <i class="fas fa-exclamation-circle mt-1 mr-3 text-xl"></i>
      <div>
        <h3 class="font-semibold">Error</h3>
        <p><?= htmlspecialchars($error) ?></p>
      </div>
    </div>
  <?php endif; ?>

  <!-- Current User Info -->
  <div class="admin-card rounded-xl p-6 mb-8">
    <div class="flex items-center">
      <div class="bg-cyan-500/20 p-4 rounded-full mr-4">
        <i class="fas fa-user-astronaut text-cyan-400 text-2xl"></i>
      </div>
      <div>
        <h3 class="text-xl font-semibold"><?= htmlspecialchars($userData['name']) ?></h3>
        <div class="flex items-center mt-1">
          <span class="text-gray-400 mr-3"><?= htmlspecialchars($userData['email']) ?></span>
          <span class="role-badge <?= $userData['role'] ?>">
            <?= htmlspecialchars(ucfirst($userData['role'])) ?>
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Form -->
  <form method="POST" class="admin-card rounded-xl p-8 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-lg font-semibold mb-2">
          <span class="text-cyan-400">*</span> Full Name
        </label>
        <input 
          type="text" 
          name="name" 
          required 
          class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-500 focus:outline-none"
          value="<?= htmlspecialchars($userData['name']) ?>"
        />
      </div>
      
      <div>
        <label class="block text-lg font-semibold mb-2">
          <span class="text-cyan-400">*</span> Email Address
        </label>
        <input 
          type="email" 
          name="email" 
          required 
          class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-500 focus:outline-none"
          value="<?= htmlspecialchars($userData['email']) ?>"
        />
      </div>
    </div>

    <?php if ($currentUser['role'] === 'admin'): ?>
      <div>
        <label class="block text-lg font-semibold mb-2">
          <span class="text-cyan-400">*</span> User Role
        </label>
        <select 
          name="role" 
          class="input-field w-full px-4 py-3 rounded-lg text-white focus:outline-none"
        >
          <option value="user" <?= $userData['role'] === 'user' ? 'selected' : '' ?>>Standard User</option>
          <option value="admin" <?= $userData['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
        </select>
      </div>
    <?php endif; ?>

    <div>
      <label class="block text-lg font-semibold mb-2">Password Update</label>
      <div class="relative">
        <input 
          type="password" 
          name="password" 
          class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-500 focus:outline-none"
          placeholder="Leave blank to keep current password"
        />
        <button type="button" class="absolute right-3 top-3 text-gray-500 hover:text-gray-300" onclick="togglePassword(this)">
          <i class="fas fa-eye"></i>
        </button>
      </div>
      <p class="text-sm text-gray-500 mt-1">Password must be at least 8 characters</p>
    </div>

   <div class="pt-6 border-t border-gray-800 flex justify-between">
        <button type="submit" class="btn-primary px-8 py-3 rounded-full font-bold text-black">
            <i class="fas fa-save mr-2"></i> Save Changes
        </button>
        
        <?php if (!empty($currentUser['role']) && $currentUser['role'] === 'admin' && $currentUser['id'] != $userData['id']): ?>
            <a href="delete_user.php?id=<?= htmlspecialchars($userData['id']) ?>"
            onclick="return confirm('Are you sure you want to permanently delete this user?');"
            class="bg-red-600/80 hover:bg-red-600 px-6 py-3 rounded-full font-medium flex items-center">
                <i class="fas fa-trash-alt mr-2"></i> Delete User
            </a>
        <?php endif; ?>
    </div>

  </form>
</div>

<script>
  // Toggle password visibility
  function togglePassword(button) {
    const input = button.parentElement.querySelector('input');
    if (input.type === 'password') {
      input.type = 'text';
      button.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
      input.type = 'password';
      button.innerHTML = '<i class="fas fa-eye"></i>';
    }
  }
  
  // Animation for form when page loads
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
