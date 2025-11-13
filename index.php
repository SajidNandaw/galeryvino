<?php
require_once 'config.php';
require_login();

$photos = $pdo->query("
  SELECT f.*, u.Username
  FROM foto f 
  JOIN user u ON f.UserID = u.UserID 
  ORDER BY f.FotoID DESC
")->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Galeri iQOO 15</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body {
  background: radial-gradient(circle at top, #141414 0%, #0a0a0a 100%);
  color: #f1f1f1;
  font-family: 'Poppins', sans-serif;
}

.card {
  background: linear-gradient(145deg, #0f0f0f, #1a1a1a);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 0.75rem;
  transition: 0.4s ease;
  box-shadow: 0 0 12px rgba(255, 0, 0, 0.05);
}
.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 0 25px rgba(255, 0, 0, 0.3), inset 0 0 12px rgba(255, 255, 255, 0.05);
}

.btn-red {
  background: linear-gradient(135deg, #ff0022 0%, #b80000 100%);
  color: #fff;
  font-weight: 600;
  border: none;
  box-shadow: 0 0 10px rgba(255, 0, 0, 0.4);
  transition: 0.3s ease;
}
.btn-red:hover {
  background: linear-gradient(135deg, #ff3333 0%, #ff0022 100%);
  box-shadow: 0 0 18px rgba(255, 50, 50, 0.8);
}

input, textarea {
  background: #111;
  border: 1px solid #222;
  color: #eee;
  border-radius: 6px;
}
input:focus, textarea:focus {
  border-color: #ff0022;
  outline: none;
  box-shadow: 0 0 8px rgba(255, 0, 0, 0.4);
}

::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-thumb {
  background-color: #ff0022;
  border-radius: 10px;
}
</style>
</head>
<body class="min-h-screen">

<!-- Navbar -->
<nav class="backdrop-blur-sm bg-black/90 border-b border-red-700 p-4 flex justify-between items-center shadow-lg">
  <h1 class="text-2xl font-extrabold tracking-wide bg-gradient-to-r from-white via-red-500 to-white bg-clip-text text-transparent drop-shadow-lg">
    GALERI Vinn
  </h1>
  <div class="flex items-center space-x-3">
    <span class="text-gray-300 text-sm">Halo👋, <?=htmlspecialchars($_SESSION['Username'])?></span>
    <a href="upload.php" class="btn-red px-3 py-1 rounded text-sm">Upload</a>
    <a href="logout.php" class="border border-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">Logout</a>
  </div>
</nav>

<!-- Galeri -->
<div class="max-w-6xl mx-auto p-6 grid md:grid-cols-3 sm:grid-cols-2 gap-6">
<?php foreach($photos as $p): ?>
  <div class="card overflow-hidden">
    <img src="uploads/<?=htmlspecialchars($p['LokasiFile'])?>" class="w-full h-60 object-cover border-b border-red-800">
    <div class="p-4">
      <h3 class="font-semibold text-lg text-white drop-shadow-md"><?=htmlspecialchars($p['JudulFoto'] ?? 'Tanpa Judul')?></h3>
      <p class="text-sm text-gray-300"><?=nl2br(htmlspecialchars($p['DeskripsiFoto'] ?? ''))?></p>
      <p class="text-xs text-gray-500 mt-1">Oleh <span class="text-red-500 font-semibold"><?=htmlspecialchars($p['Username'])?></span></p>

      <?php
        $fotoId = $p['FotoID'];
        $likeCountStmt = $pdo->prepare("SELECT COUNT(*) FROM likefoto WHERE FotoID=?");
        $likeCountStmt->execute([$fotoId]);
        $likes = $likeCountStmt->fetchColumn();

        $commentsStmt = $pdo->prepare("
          SELECT k.*, u.Username 
          FROM komentarfoto k 
          JOIN user u ON k.UserID = u.UserID 
          WHERE FotoID=? ORDER BY k.KomentarID DESC
        ");
        $commentsStmt->execute([$fotoId]);
        $commentList = $commentsStmt->fetchAll();
      ?>

      <!-- Tombol Like -->
      <div class="mt-3 flex items-center">
        <form method="post" action="like.php">
          <input type="hidden" name="FotoID" value="<?=$fotoId?>">
          <button class="text-red-500 hover:text-white font-semibold transition">❤️ <?=$likes?> Suka</button>
        </form>
      </div>

      <!-- Komentar -->
      <div class="mt-3 border-t border-gray-700 pt-2">
        <h4 class="font-semibold text-sm text-gray-200 mb-1">Komentar:</h4>
        <div class="max-h-24 overflow-y-auto space-y-1 pr-1">
        <?php foreach($commentList as $c): ?>
          <p class="text-sm text-gray-400">
            <b class="text-white"><?=htmlspecialchars($c['Username'])?>:</b> <?=htmlspecialchars($c['IsiKomentar'])?>
          </p>
        <?php endforeach; ?>
        </div>

        <!-- Form komentar -->
        <form method="post" action="comment.php" class="mt-2">
          <input type="hidden" name="FotoID" value="<?=$fotoId?>">
          <input name="IsiKomentar" placeholder="Tulis komentar..." class="w-full border rounded px-2 py-1 text-sm mb-1" required>
          <button class="btn-red text-xs px-3 py-1 rounded">Kirim</button>
        </form>

        <!-- Tombol Edit & Hapus -->
        <?php if ($p['UserID'] == $_SESSION['UserID']): ?>
          <div class="flex gap-2 mt-2">
            <form method="get" action="edit.php">
              <input type="hidden" name="FotoID" value="<?=$fotoId?>">
              <button class="bg-white text-black text-xs px-3 py-1 rounded hover:bg-gray-200 font-semibold">Edit</button>
            </form>
            <form method="post" action="delete.php" onsubmit="return confirm('Yakin ingin menghapus foto ini?');">
              <input type="hidden" name="FotoID" value="<?=$fotoId?>">
              <button class="btn-red text-xs px-3 py-1 rounded">Hapus</button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<!-- Footer -->
<footer class="text-center text-gray-400 text-sm py-6 border-t border-red-800 mt-10">
  © 2025 <span class="text-white font-semibold">Galeri Vinn</span>
</footer>

</body>
</html>
