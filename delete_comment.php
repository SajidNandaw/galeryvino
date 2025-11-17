<?php
require_once 'config.php';
require_login();

if(isset($_POST['KomentarID'])){
  $stmt=$pdo->prepare("DELETE FROM komentarfoto WHERE KomentarID=? AND UserID=?");
  $stmt->execute([$_POST['KomentarID'], $_SESSION['UserID']]);
}
?>
