<?php
session_start();

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulasi validasi login sederhana
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Di sini nanti bisa ditambahkan koneksi database untuk cek user & password
    // Untuk prototype, kita anggap semua login berhasil
    if (!empty($username) && !empty($password)) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'Admin'; // Default role
        
        header("Location: index.php");
        exit;
    } else {
        $error = "Username dan Password harus diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - SIFASTER Gudang</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body class="login-body">
  <div class="login-container">
    <div class="login-box">
      <div class="login-header">
        <div class="logo">SIFASTER</div>
        <h2>Login Sistem</h2>
        <p>Sistem Informasi Gudang</p>
      </div>
      
      <?php if (isset($error)): ?>
        <div style="color: red; text-align: center; margin-bottom: 10px;">
            <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="Masukkan username" required>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Masukkan password" required>
        </div>

        <button type="submit" class="btn-login">Masuk</button>
      </form>
      
      <div class="login-footer">
        <p>&copy; 2025 SIFASTER</p>
      </div>
    </div>
  </div>
</body>
</html>
