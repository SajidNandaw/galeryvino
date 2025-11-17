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
<title>Galeri Vinn</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body{background:radial-gradient(circle at top,#141414 0%,#0a0a0a 100%);color:#f1f1f1;font-family:'Poppins',sans-serif;}
.card{background:linear-gradient(145deg,#0f0f0f,#1a1a1a);border:1px solid rgba(255,255,255,0.06);border-radius:.75rem;transition:.3s;overflow:hidden;position:relative;width:100%;max-width:280px;margin:0 auto;}
.card:hover{transform:translateY(-3px);box-shadow:0 0 18px rgba(255,0,0,.25);}
.menu-btn{position:absolute;top:8px;right:8px;background:rgba(20,20,20,.7);color:#fff;border-radius:9999px;padding:3px 7px;font-size:18px;cursor:pointer;}
.menu-btn:hover{background:rgba(255,0,0,.6);}
.menu{display:none;position:absolute;top:32px;right:10px;background:#111;border:1px solid #333;border-radius:.5rem;box-shadow:0 0 10px rgba(255,0,0,.3);z-index:20;}
.menu.active{display:block;}
.menu button{display:block;width:100%;text-align:left;padding:8px 12px;font-size:.85rem;color:#fff;}
.menu button:hover{background:#ff0022;}
.btn-red{background:linear-gradient(135deg,#ff0022 0%,#b80000 100%);color:#fff;font-weight:600;}
.btn-red:hover{background:linear-gradient(135deg,#ff3333,#ff0022);box-shadow:0 0 15px rgba(255,0,0,.6);}
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.95);z-index:50;justify-content:center;align-items:center;padding:2rem;}
.overlay.active{display:flex;animation:fadeIn .3s ease forwards;}
@keyframes fadeIn{from{opacity:0;}to{opacity:1;}}
.comment-actions button{background:none;color:#888;font-size:.8rem;margin-left:5px;}
.comment-actions button:hover{color:#ff4444;}
</style>
</head>
<body class="min-h-screen">

<!-- Navbar -->
<nav class="bg-black/90 border-b border-red-700 p-4 flex justify-between items-center shadow-lg">
  <h1 class="text-2xl font-extrabold bg-gradient-to-r from-white via-red-500 to-white bg-clip-text text-transparent">GALERI VINN</h1>
  <div class="flex items-center space-x-3">
    <span class="text-gray-300 text-sm">haloo👋, <?=htmlspecialchars($_SESSION['Username'])?></span>
    <a href="upload.php" class="btn-red px-3 py-1 rounded text-sm">Upload</a>
    <a href="logout.php" class="border border-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Logout</a>
  </div>
</nav>

<!-- Galeri Grid -->
<div class="max-w-7xl mx-auto p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5 justify-items-center">
<?php foreach($photos as $p): ?>
  <div class="card">
    <?php if ($p['UserID'] == $_SESSION['UserID']): ?>
      <div class="menu-btn" onclick="toggleMenu(<?=$p['FotoID']?>)">⋯</div>
      <div id="menu<?=$p['FotoID']?>" class="menu">
        <form method="get" action="edit.php">
          <input type="hidden" name="FotoID" value="<?=$p['FotoID']?>">
          <button type="submit">✏️ Edit</button>
        </form>
        <form method="post" action="delete.php" onsubmit="return confirm('Yakin ingin menghapus foto ini?');">
          <input type="hidden" name="FotoID" value="<?=$p['FotoID']?>">
          <button type="submit">🗑️ Hapus</button>
        </form>
      </div>
    <?php endif; ?>

    <img src="uploads/<?=htmlspecialchars($p['LokasiFile'])?>" 
         class="w-full h-48 object-cover rounded-t-lg cursor-pointer"
         onclick="openOverlay(<?=$p['FotoID']?>)">

    <div class="p-3">
      <div class="flex items-center gap-3 text-xs text-red-400 mb-2">
        <form method="post" action="like.php" class="inline">
          <input type="hidden" name="FotoID" value="<?=$p['FotoID']?>">
          <button type="submit" class="hover:text-white transition">
            ❤️ <?php $c=$pdo->prepare('SELECT COUNT(*) FROM likefoto WHERE FotoID=?');$c->execute([$p['FotoID']]);echo $c->fetchColumn();?> suka
          </button>
        </form>
        <button type="button" class="cursor-pointer hover:text-white transition" onclick="openOverlay(<?=$p['FotoID']?>)">💬 komentar</button>
      </div>

      <h3 class="font-semibold text-white text-sm mb-1"><?=htmlspecialchars($p['JudulFoto'])?></h3>
      <p class="text-xs text-gray-400 italic mb-1">Posted by <?=htmlspecialchars($p['Username'])?></p>
      <p class="text-sm text-gray-300 mb-2"><?=htmlspecialchars($p['DeskripsiFoto'])?></p>
      <p class="text-[11px] text-gray-500 italic">Diposting pada <?=date('d M Y',strtotime($p['TanggalUpload']??'now'))?></p>
    </div>
  </div>
<?php endforeach; ?>
</div>

<!-- Overlay komentar -->
<div id="commentOverlay" class="overlay">
  <div class="bg-[#111] rounded-xl shadow-2xl p-6 max-w-4xl w-full grid md:grid-cols-2 gap-6 relative">
    <button onclick="closeOverlay()" class="absolute top-3 right-3 text-gray-400 hover:text-red-500 text-2xl">&times;</button>
    <div>
      <img id="overlayImage" src="" class="rounded-lg w-full object-cover max-h-[80vh]">
    </div>
    <div>
      <h2 id="overlayTitle" class="text-xl font-bold mb-2 text-white"></h2>
      <p id="overlayUser" class="text-sm text-gray-400 mb-4"></p>
      <div id="overlayComments" class="max-h-[50vh] overflow-y-auto border-t border-gray-700 pt-2 mb-3"></div>
      <form method="post" action="comment.php" class="flex gap-2">
        <input type="hidden" name="FotoID" id="overlayFotoID">
        <input name="IsiKomentar" placeholder="Tulis komentar..."
               class="flex-1 border border-gray-600 rounded px-2 py-1 text-sm bg-[#1b1b1b] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500"
               required>
        <button class="btn-red text-xs px-3 py-1 rounded">Kirim</button>
      </form>
    </div>
  </div>
</div>

<footer class="text-center text-gray-400 text-sm py-6 border-t border-red-800 mt-10">
  © 2025 <span class="text-white font-semibold">Galeri Vinn</span>
</footer>

<script>
const currentUser = <?=json_encode($_SESSION['UserID'])?>;
const photoData = <?=json_encode($photos)?>;
const commentsData = <?=json_encode($pdo->query("
  SELECT k.*, u.Username, f.FotoID 
  FROM komentarfoto k 
  JOIN user u ON k.UserID = u.UserID 
  JOIN foto f ON k.FotoID = f.FotoID
  ORDER BY k.TanggalKomentar ASC
")->fetchAll())?>;

function openOverlay(id){
  const overlay=document.getElementById('commentOverlay');
  overlay.classList.add('active');
  const foto=photoData.find(p=>p.FotoID==id);
  const list=commentsData.filter(c=>c.FotoID==id);
  document.getElementById('overlayImage').src='uploads/'+foto.LokasiFile;
  document.getElementById('overlayTitle').textContent=foto.JudulFoto;
  document.getElementById('overlayUser').textContent='Oleh '+foto.Username;
  document.getElementById('overlayFotoID').value=id;
  let html='';
  list.forEach(c=>{
    const tanggal=new Date(c.TanggalKomentar).toLocaleString('id-ID',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
    html+=`
      <div class="text-sm text-gray-300 mb-2 border-b border-gray-800 pb-1 flex justify-between items-start">
        <div>
          <b class="text-red-400">${c.Username}</b>
          <span class="text-gray-500 text-[11px] ml-2">${tanggal}</span><br>
          <span id="komentar-${c.KomentarID}">${c.IsiKomentar}</span>
        </div>
        ${c.UserID == currentUser ? `
        <div class="comment-actions">
          <button onclick="editKomentar(${c.KomentarID}, '${c.IsiKomentar.replace(/'/g,"&#39;")}')">✏️</button>
          <button onclick="hapusKomentar(${c.KomentarID})">🗑️</button>
        </div>` : ''}
      </div>`;
  });
  document.getElementById('overlayComments').innerHTML=html||'<p class="text-gray-500 text-sm">Belum ada komentar.</p>';
}
function closeOverlay(){document.getElementById('commentOverlay').classList.remove('active');}
function toggleMenu(id){
  document.querySelectorAll('.menu').forEach(m=>{if(m.id!=='menu'+id)m.classList.remove('active');});
  document.getElementById('menu'+id).classList.toggle('active');
}
window.onclick=e=>{
  if(!e.target.closest('.menu')&&!e.target.closest('.menu-btn')){
    document.querySelectorAll('.menu').forEach(m=>m.classList.remove('active'));
  }
};

function editKomentar(id, isi){
  const newIsi = prompt("Edit komentar kamu:", isi);
  if(newIsi && newIsi.trim()!==""){
    fetch('edit_comment.php',{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:`KomentarID=${id}&IsiKomentar=${encodeURIComponent(newIsi)}`
    }).then(()=>location.reload());
  }
}
function hapusKomentar(id){
  if(confirm("Yakin ingin menghapus komentar ini?")){
    fetch('delete_comment.php',{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:`KomentarID=${id}`
    }).then(()=>location.reload());
  }
}
</script>

</body>
</html>
