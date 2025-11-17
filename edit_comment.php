<?php
require_once 'config.php';
require_login();

if(isset($_POST['KomentarID'], $_POST['IsiKomentar'])){
  $stmt=$pdo->prepare("UPDATE komentarfoto SET IsiKomentar=? WHERE KomentarID=? AND UserID=?");
  $stmt->execute([$_POST['IsiKomentar'], $_POST['KomentarID'], $_SESSION['UserID']]);
}
?>
