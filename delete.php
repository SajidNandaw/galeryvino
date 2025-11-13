<?php
require_once 'config.php';
require_login();
$fotoId=intval($_POST['FotoID']??0);
$userId=$_SESSION['UserID'];
$stmt=$pdo->prepare("SELECT * FROM foto WHERE FotoID=? AND UserID=?");
$stmt->execute([$fotoId,$userId]);
$foto=$stmt->fetch();
if($foto){
  $file='uploads/'.$foto['LokasiFile'];
  if(is_file($file)) unlink($file);
  $pdo->prepare("DELETE FROM foto WHERE FotoID=?")->execute([$fotoId]);
}
header('Location: index.php');exit;
