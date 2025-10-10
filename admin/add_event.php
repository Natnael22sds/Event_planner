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
        // Insert event
        $stmt = $pdo->prepare("INSERT INTO events (title, description, start_datetime, end_datetime, location, category, organizer_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $title, $description, $start_datetime, $end_datetime, $location, $category, $_SESSION['user_id']
        ]);
        $event_id = $pdo->lastInsertId();
        header('Location: dashboard.php');

        // Handle attachment upload if any
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

        
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SSGI 2025 - Add New Event</title>
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
    
    .form-card {
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
    
    .file-upload {
      position: relative;
      overflow: hidden;
    }
    
    .file-upload-input {
      position: absolute;
      left: 0;
      top: 0;
      opacity: 0;
      width: 100%;
      height: 100%;
      cursor: pointer;
    }
  </style>
</head>
<body>
<div class="stars"></div>

<div class="container mx-auto px-4 py-8 max-w-4xl">
  <!-- Header -->
  <div class="flex justify-between items-start mb-8">
    <div>
      <h1 class="text-3xl md:text-4xl font-bold font-orbitron glow mb-2">Create New Event</h1>
      <p class="text-gray-400">Add a new event to the Space Science & Geospatial Institute Conference 2025</p>
    </div>
    <a href="dashboard.php" class="text-cyan-400 hover:underline flex items-center">
      <i class="fas fa-arrow-left mr-2"></i> Back to dasboard
    </a>
  </div>

  <!-- Status Messages -->
  <?php if ($error): ?>
    <div class="bg-red-900/80 border border-red-700 text-red-200 px-6 py-4 rounded-lg mb-6 flex items-start">
      <i class="fas fa-exclamation-circle mt-1 mr-3 text-xl"></i>
      <div>
        <h3 class="font-semibold">Error</h3>
        <p><?= htmlspecialchars($error) ?></p>
      </div>
    </div>
  <?php elseif ($success): ?>
    <div class="bg-green-900/80 border border-green-700 text-green-200 px-6 py-4 rounded-lg mb-6 flex items-start">
      <i class="fas fa-check-circle mt-1 mr-3 text-xl"></i>
      <div>
        <h3 class="font-semibold">Success!</h3>
        <p><?= htmlspecialchars($success) ?></p>
      </div>
    </div>
  <?php endif; ?>

  <!-- Form -->
  <form method="POST" enctype="multipart/form-data" class="form-card rounded-xl p-8 space-y-6">
    <!-- Title -->
    <div>
      <label class="block text-lg font-semibold mb-2">
        <span class="text-cyan-400">*</span> Event Title
      </label>
      <input 
        type="text" 
        name="title" 
        required 
        class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-500 focus:outline-none"
        placeholder="Enter event title"
        value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
      />
    </div>

    <!-- Description -->
    <div>
      <label class="block text-lg font-semibold mb-2">Description</label>
      <textarea 
        name="description" 
        rows="5" 
        class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-500 focus:outline-none"
        placeholder="Enter event description"
      ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
    </div>

    <!-- Date & Time -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-lg font-semibold mb-2">
          <span class="text-cyan-400">*</span> Start Date & Time
        </label>
        <input 
          type="datetime-local" 
          name="start_datetime" 
          required 
          class="input-field w-full px-4 py-3 rounded-lg text-white focus:outline-none"
          value="<?= htmlspecialchars($_POST['start_datetime'] ?? '') ?>"
        />
      </div>
      <div>
        <label class="block text-lg font-semibold mb-2">
          <span class="text-cyan-400">*</span> End Date & Time
        </label>
        <input 
          type="datetime-local" 
          name="end_datetime" 
          required 
          class="input-field w-full px-4 py-3 rounded-lg text-white focus:outline-none"
          value="<?= htmlspecialchars($_POST['end_datetime'] ?? '') ?>"
        />
      </div>
    </div>

    <!-- Location -->
    <div>
      <label class="block text-lg font-semibold mb-2">Location</label>
      <input 
        type="text" 
        name="location" 
        class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-500 focus:outline-none"
        placeholder="Enter event location"
        value="<?= htmlspecialchars($_POST['location'] ?? '') ?>"
      />
    </div>

    <!-- Category & Type -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-lg font-semibold mb-2">
          <span class="text-cyan-400">*</span> Category
        </label>
        <select 
          name="category" 
          required 
          class="input-field w-full px-4 py-3 rounded-lg text-white focus:outline-none"
        >
          <option value="" disabled selected>Select category</option>
          <option value="Meeting" <?= (($_POST['category'] ?? '') === 'Meeting') ? 'selected' : '' ?>>Meeting</option>
          <option value="Training" <?= (($_POST['category'] ?? '') === 'Training') ? 'selected' : '' ?>>Training</option>
          <option value="Social" <?= (($_POST['category'] ?? '') === 'Panel') ? 'selected' : '' ?>>Social</option>
        </select>
      </div>
    </div>

    <!-- Attachments -->
    <div>
      <label class="block text-lg font-semibold mb-2">Attachments</label>
      <div class="file-upload input-field w-full px-4 py-3 rounded-lg focus:outline-none">
        <div class="flex items-center justify-between">
          <span class="text-gray-400" id="attachment-name">No file selected</span>
          <button type="button" class="text-cyan-400 hover:text-cyan-300">
            <i class="fas fa-paperclip mr-1"></i> Browse
          </button>
        </div>
        <input 
          type="file" 
          name="attachment" 
          id="attachment" 
          accept=".pdf,.doc,.docx,.jpg,.png,.jpeg" 
          class="file-upload-input"
          onchange="document.getElementById('attachment-name').textContent = this.files[0] ? this.files[0].name : 'No file selected'"
        />
      </div>
    </div>

    <!-- Submit Button -->
    <div class="pt-6 border-t border-gray-800">
      <button type="submit" class="btn-primary px-8 py-3 rounded-full font-bold text-black text-lg">
        <i class="fas fa-rocket mr-2"></i> Launch Event
      </button>
    </div>
  </form>
</div>

<script>
  // Simple animation for form when page loads
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

