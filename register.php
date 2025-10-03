<?php
session_start();
include 'config.php'; // Your DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill all fields.';
    } else {
        // Check if user already exists
        $stmt = $pdo->prepare('SELECT id FROM eusers WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            // Hash password securely
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert user, default role is 'user'
            $stmt = $pdo->prepare('INSERT INTO eusers (name, email, password_hash) VALUES (?, ?, ?)');
            if ($stmt->execute([$name, $email, $password_hash])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = 'user';
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Registration failed.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen text-white">
  <form action="" method="POST" class="bg-gray-800 p-8 rounded shadow-md w-full max-w-md">
    <h2 class="text-2xl font-semibold mb-6 text-center">Register</h2>

    <?php if (!empty($error)): ?>
      <p class="bg-red-500 p-2 rounded mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <label class="block mb-2">Name</label>
    <input type="text" name="name" required class="w-full p-2 mb-4 rounded bg-gray-700 focus:outline-none" />

    <label class="block mb-2">Email</label>
    <input type="email" name="email" required class="w-full p-2 mb-4 rounded bg-gray-700 focus:outline-none" />

    <label class="block mb-2">Password</label>
    <input type="password" name="password" required class="w-full p-2 mb-6 rounded bg-gray-700 focus:outline-none" />

    <button type="submit" class="w-full py-2 bg-blue-600 rounded hover:bg-blue-700 transition">
      Register
    </button>
  </form>
</body>
</html>
