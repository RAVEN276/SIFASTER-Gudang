<?php
session_start();
require_once 'koneksi.php'; // Koneksi database

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = md5($_POST['password']); // Menggunakan MD5 sesuai database

    // Query cek user di tabel users
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Set Session
        $_SESSION['user_logged_in'] = true;
        $_SESSION['id_user'] = $row['id_user'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role']; // Admin, Produksi, Purchasing, Sales
        
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - SIFASTER</title>
  <link rel="stylesheet" href="style.css" />
  <style>/
    .img-logo {
        width: 250px;
        height: auto;
        object-fit: contain;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
    }

    .mobile-logo-container {
        display: none; /* Default sembunyi di desktop */
        text-align: center;
        margin-bottom: 20px;
    }

    .img-logo-mobile {
        width: 160px; /* Ukuran Besar untuk HP */
        background: var(--primary-color);
        padding: 15px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    
    /* Media Query: Tampilkan logo mobile hanya di layar kecil */
    @media (max-width: 900px) {
        .mobile-logo-container {
            display: block; 
        }
    }
  </style>
</head>
<body style="background-color: #f1f5f9;"> 
  
  <div class="login-wrapper">
    
    <div class="login-left">
      <div class="brand-name">
          <img src="logo.png" alt="Logo SIFASTER" class="img-logo">
      </div>
      
      <div class="login-caption">
        <h1>Login into<br>your account</h1>
        <p>Sistem Informasi Manajemen Gudang Cepat & Akurat.</p>
      </div>

      <div class="circle-deco circle-1"></div>
      <div class="circle-deco circle-2"></div>
    </div>

    <div class="login-right">
      <div class="login-card">
        
        <div class="mobile-logo-container">
            <img src="logo.png" alt="SIFASTER" class="img-logo-mobile">
        </div>

        <h2 style="margin-bottom: 20px; color: var(--primary-color);">Welcome Back!</h2>
        
        <?php if (isset($error)): ?>
          <div class="alert-error">
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

          <div style="margin-top: 30px;">
            <button type="submit" class="btn-login-full">Login</button>
          </div>
          
          <p style="margin-top: 20px; font-size: 0.85rem; color: #64748b; text-align: center;">
            Lupa password? <a href="#" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Hubungi IT</a>
          </p>
        </form>
      </div>
      
      <div class="copyright-text">
        &copy; 2025 SIFASTER System. All rights reserved.
      </div>
    </div>

  </div>

</body>
</html>