<?php
require_once 'config.php';
if (isset($_SESSION['UserID'])) header('Location: index.php');
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username']);
    $e = trim($_POST['email']);
    $p = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $cek = $pdo->prepare("SELECT * FROM user WHERE Username=? OR Email=?");
    $cek->execute([$u, $e]);
    if ($cek->rowCount() == 0) {
        $pdo->prepare("INSERT INTO user (Username, Email, Password) VALUES (?,?,?)")->execute([$u,$e,$p]);
        $msg = "Registrasi berhasil! Silakan login.";
    } else $msg = "Username atau email sudah digunakan!";
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Daftar | Galeri Vinn</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body {
  background: radial-gradient(circle at top, #141414 0%, #0a0a0a 100%);
  color: #f1f1f1;
  font-family: 'Poppins', sans-serif;
}
.btn-red {
  background: linear-gradient(135deg, #ff0022 0%, #b80000 100%);
  color: #fff;
  font-weight: 600;
  transition: 0.3s;
}
.btn-red:hover {
  background: linear-gradient(135deg, #ff3333, #ff0022);
  box-shadow: 0 0 15px rgba(255,0,0,0.6);
}
input {
  background: #111;
  border: 1px solid #222;
  color: #eee;
  border-radius: 6px;
}
input:focus {
  border-color: #ff0022;
  box-shadow: 0 0 8px rgba(255,0,0,0.4);
}
</style>
</head>
<body class="min-h-screen flex items-center justify-center">
<div class="bg-black/70 border border-red-800 rounded-xl p-8 w-96 shadow-lg">
  <h1 class="text-2xl font-bold text-center mb-4 text-red-500">Daftar Akun</h1>
  <?php if($msg): ?><p class="text-center text-red-400 mb-3"><?=$msg?></p><?php endif; ?>
  <form method="post" class="space-y-4">
    <div>
      <label>Username</label>
      <input name="username" class="w-full p-2 mt-1" required>
    </div>
    <div>
      <label>Email</label>
      <input type="email" name="email" class="w-full p-2 mt-1" required>
    </div>
    <div>
      <label>Password</label>
      <input type="password" name="password" class="w-full p-2 mt-1" required>
    </div>
    <button class="btn-red w-full py-2 rounded mt-3">Daftar</button>
  </form>
  <p class="text-center text-gray-400 text-sm mt-4">Sudah punya akun?
    <a href="login.php" class="text-red-400 hover:text-white font-semibold">Login di sini</a>
  </p>
</div>
</body>
</html>
