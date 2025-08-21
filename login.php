<?php
ob_start();
session_start();
require_once 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_email = trim($_POST['username_email']);
    $password = $_POST['password'];

    if (empty($username_email)) {
        $errors[] = "Username sau Email este necesar.";
    }
    if (empty($password)) {
        $errors[] = "Parola este necesară.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username_email, $username_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $username, $hashed_password);
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                header("Location: ../index.php");
                exit;
            } else {
                $errors[] = "Parola incorectă.";
            }
        } else {
            $errors[] = "Nu există utilizator cu acest username sau email.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Login - Warehouse</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #2e2f4e, #3c6478);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      color: #fff;
    }
    .container {
      background: rgba(0, 0, 0, 0.75);
      padding: 40px;
      border-radius: 10px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.7);
      animation: fadeIn 0.7s ease-in-out;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    label {
      display: block;
      margin-top: 15px;
      font-size: 0.9em;
    }
    input {
      width: 100%;
      padding: 10px;
      border-radius: 5px;
      margin-top: 5px;
      border: none;
      font-size: 1em;
    }
    input:focus {
      outline: none;
      box-shadow: 0 0 5px #4CAF50;
    }
    .btn {
      margin-top: 25px;
      width: 100%;
      background: #4CAF50;
      border: none;
      color: #fff;
      padding: 12px;
      border-radius: 5px;
      font-size: 1em;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .btn:hover {
      background: #45a049;
    }
    .errors {
      background: #ffdddd;
      border-left: 5px solid #ff5c5c;
      padding: 10px;
      color: #000;
      margin-bottom: 20px;
      border-radius: 5px;
    }
    .link {
      text-align: center;
      margin-top: 15px;
    }
    .link a {
      color: #4CAF50;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Login</h2>
    <?php if (!empty($errors)): ?>
      <div class="errors">
        <ul>
          <?php foreach ($errors as $error): ?>
          <li><?php echo htmlspecialchars($error); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <form action="login.php" method="POST">
      <label for="username_email">Username sau Email</label>
      <input type="text" name="username_email" id="username_email" required>

      <label for="password">Parola</label>
      <input type="password" name="password" id="password" required>

      <button type="submit" class="btn">Login</button>
    </form>
    <div class="link">
      Nu ai cont? <a href="register.php">Înregistrează-te</a>
    </div>
  </div>
</body>
</html>
