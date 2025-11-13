<?php
require_once 'config.php';
require_login();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $file = $_FILES['photo'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = uniqid().".$ext";
        move_uploaded_file($file['tmp_name'], "uploads/$name");
        $pdo->prepare("INSERT INTO foto (UserID, JudulFoto, DeskripsiFoto, LokasiFile) VALUES (?,?,?,?)")
            ->execute([$_SESSION['UserID'],$judul,$desc,$name]);
        $msg = "Foto berhasil diupload!";
    } else $msg = "Upload gagal.";
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Upload | Galeri Vinn</title>
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
input, textarea {
  background: #111;
  border: 1px solid #222;
  color: #eee;
  border-radius: 6px;
}
input:focus, textarea:focus {
  border-color: #ff0022;
  box-shadow: 0 0 8px rgba(255,0,0,0.4);
}
</style>
</head>
<body class="min-h-screen flex items-center justify-center">
<div class="bg-black/70 border border-red-800 rounded-xl p-8 w-[420px] shadow-lg">
  <h1 class="text-2xl font-bold text-center mb-4 text-red-500">Upload Foto</h1>
  <?php if($msg): ?><p class="text-center text-red-400 mb-3"><?=$msg?></p><?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="space-y-4">
    <div>
      <label>Judul</label>
      <input name="title" class="w-full p-2 mt-1" required>
    </div>
    <div>
      <label>Deskripsi</label>
      <textarea name="description" rows="3" class="w-full p-2 mt-1"></textarea>
    </div>
    <div>
      <label>File Foto</label>
      <input type="file" name="photo" accept="image/*" class="w-full mt-1" required>
    </div>
    <button class="btn-red w-full py-2 rounded mt-3">Upload</button>
    <a href="index.php" class="block text-center text-gray-400 text-sm hover:text-white mt-2">← Kembali ke galeri</a>
  </form>
</div>
</body>
</html>
