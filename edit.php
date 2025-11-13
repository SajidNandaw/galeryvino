<?php
require_once 'config.php';
require_login();

$fotoId = intval($_GET['FotoID'] ?? ($_POST['FotoID'] ?? 0));
$userId = $_SESSION['UserID'];

// Ambil data foto (untuk GET atau validasi POST)
$stmt = $pdo->prepare("SELECT * FROM foto WHERE FotoID = ?");
$stmt->execute([$fotoId]);
$foto = $stmt->fetch();
if (!$foto) {
    header('Location: index.php');
    exit;
}

// Pastikan pemilik
if ($foto['UserID'] != $userId) {
    // boleh juga tampilkan pesan error, tapi redirect saja
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $desc  = trim($_POST['description'] ?? '');

    // validasi ringan
    if (strlen($title) > 255) $errors[] = "Judul maksimal 255 karakter.";

    // cek apakah ada file baru yang diupload
    $f = $_FILES['photo'] ?? null;
    $newFilename = null;

    if ($f && $f['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error upload file.";
        } else {
            $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $f['tmp_name']);
            if (!in_array($mime, ['image/jpeg','image/png','image/gif'])) {
                $errors[] = "File harus gambar (jpg/png/gif).";
            } else {
                // buat nama file baru yang unik
                $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                $newFilename = bin2hex(random_bytes(6)) . "." . $ext;
                $uploadDir = __DIR__ . '/uploads';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $dest = $uploadDir . '/' . $newFilename;
                if (!move_uploaded_file($f['tmp_name'], $dest)) {
                    $errors[] = "Gagal menyimpan file.";
                    $newFilename = null;
                }
            }
        }
    }

    if (empty($errors)) {
        // jika ada file baru, hapus file lama (jika ada)
        if ($newFilename) {
            $old = __DIR__ . '/uploads/' . $foto['LokasiFile'];
            if (is_file($old)) @unlink($old);
            $pdo->prepare("UPDATE foto SET JudulFoto = ?, DeskripsiFoto = ?, LokasiFile = ? WHERE FotoID = ?")
                ->execute([$title, $desc, $newFilename, $fotoId]);
        } else {
            $pdo->prepare("UPDATE foto SET JudulFoto = ?, DeskripsiFoto = ? WHERE FotoID = ?")
                ->execute([$title, $desc, $fotoId]);
        }

        header('Location: index.php');
        exit;
    }
}

// untuk menampilkan nilai lama di form
$oldTitle = htmlspecialchars($foto['JudulFoto'] ?? '', ENT_QUOTES);
$oldDesc  = htmlspecialchars($foto['DeskripsiFoto'] ?? '', ENT_QUOTES);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<script src="https://cdn.tailwindcss.com"></script>
<title>Edit Foto</title>
</head>
<body class="bg-gray-100 min-h-screen p-6">
<div class="max-w-lg mx-auto bg-white p-8 rounded-xl shadow">
  <h2 class="text-2xl font-bold text-indigo-600 mb-4">Edit Foto</h2>

  <?php if ($errors): ?>
    <div class="bg-red-100 text-red-600 p-3 rounded mb-4">
      <ul class="list-disc ml-5">
        <?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="space-y-4">
    <input type="hidden" name="FotoID" value="<?=htmlspecialchars($fotoId)?>">
    <label class="block">
      <span class="text-sm font-medium text-gray-700">Judul</span>
      <input name="title" value="<?=$oldTitle?>" class="w-full border rounded px-3 py-2 mt-1" />
    </label>

    <label class="block">
      <span class="text-sm font-medium text-gray-700">Deskripsi</span>
      <textarea name="description" class="w-full border rounded px-3 py-2 mt-1"><?=$oldDesc?></textarea>
    </label>

    <div>
      <p class="text-sm text-gray-600 mb-2">Ganti foto (opsional):</p>
      <img src="uploads/<?=htmlspecialchars($foto['LokasiFile'])?>" class="w-full h-44 object-cover rounded mb-3">
      <input type="file" name="photo" accept="image/*" class="w-full" />
    </div>

    <div class="flex justify-between">
      <a href="index.php" class="text-gray-600 hover:underline">Batal</a>
      <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Simpan Perubahan</button>
    </div>
  </form>
</div>
</body>
</html>
