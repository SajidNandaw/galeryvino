<?php
require_once 'config.php';
require_login();

if (!isset($_GET['FotoID'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['FotoID'];
$stmt = $pdo->prepare("SELECT * FROM foto WHERE FotoID = ?");
$stmt->execute([$id]);
$foto = $stmt->fetch();

if (!$foto || $foto['UserID'] != $_SESSION['UserID']) {
    die("Akses ditolak!");
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['title']);
    $desc = trim($_POST['description']);

    if (!empty($_FILES['photo']['name'])) {
        $file = $_FILES['photo'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = uniqid().".$ext";
        move_uploaded_file($file['tmp_name'], "uploads/$name");

        // hapus lama
        if (file_exists("uploads/".$foto['LokasiFile'])) unlink("uploads/".$foto['LokasiFile']);

        $pdo->prepare("UPDATE foto SET JudulFoto=?, DeskripsiFoto=?, LokasiFile=? WHERE FotoID=?")
            ->execute([$judul, $desc, $name, $id]);
    } else {
        $pdo->prepare("UPDATE foto SET JudulFoto=?, DeskripsiFoto=? WHERE FotoID=?")
            ->execute([$judul, $desc, $id]);
    }

    $msg = "Foto berhasil diperbarui!";
    $stmt->execute([$id]);
    $foto = $stmt->fetch();
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Edit Foto | Galeri Vinn</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body {
  background: radial-gradient(circle at top, #141414 0%, #0a0a0a 100%);
  color: #f1f1f1;
  font-family: 'Poppins', sans-serif;
}
.section {
  background: linear-gradient(145deg, #0f0f0f, #1a1a1a);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 0.75rem;
  box-shadow: 0 0 20px rgba(255,0,0,0.15);
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
<body class="min-h-screen flex flex-col items-center justify-center p-6">

<h1 class="text-3xl font-bold text-red-500 mb-6 text-center">Edit Postingan</h1>
<?php if($msg): ?><p class="text-center text-red-400 mb-4"><?=$msg?></p><?php endif; ?>

<div class="section flex flex-col md:flex-row w-full max-w-4xl overflow-hidden">
  <!-- Form Edit -->
  <div class="flex-1 p-6">
    <form method="post" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label>Judul Foto</label>
        <input name="title" value="<?=htmlspecialchars($foto['JudulFoto'])?>" class="w-full p-2 mt-1" required>
      </div>
      <div>
        <label>Deskripsi</label>
        <textarea name="description" rows="4" class="w-full p-2 mt-1"><?=htmlspecialchars($foto['DeskripsiFoto'])?></textarea>
      </div>
      <div>
        <label>Ganti Foto (Opsional)</label>
        <input type="file" name="photo" accept="image/*" class="w-full mt-1">
      </div>
      <button class="btn-red w-full py-2 rounded mt-3">Simpan Perubahan</button>
      <a href="index.php" class="block text-center text-gray-400 text-sm hover:text-white mt-2">← Kembali ke galeri</a>
    </form>
  </div>

  <!-- Preview Foto -->
  <div class="flex-1 relative">
    <img src="uploads/<?=htmlspecialchars($foto['LokasiFile'])?>" 
         class="w-full h-full object-cover object-center md:h-[400px]" 
         style="aspect-ratio: 16/9; border-left:1px solid rgba(255,0,0,0.2);">
  </div>
</div>

<footer class="text-center text-gray-400 text-sm py-6 mt-8">
  © 2025 <span class="text-white font-semibold">Galeri Vinn</span> 
</footer>
</body>
</html>
