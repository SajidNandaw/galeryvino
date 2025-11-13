<?php
require_once 'config.php';
require_login();
$fotoId = intval($_POST['FotoID']);
$userId = $_SESSION['UserID'];
$stmt = $pdo->prepare("SELECT * FROM likefoto WHERE FotoID=? AND UserID=?");
$stmt->execute([$fotoId,$userId]);
if($stmt->fetch()){
  $pdo->prepare("DELETE FROM likefoto WHERE FotoID=? AND UserID=?")->execute([$fotoId,$userId]);
}else{
  $pdo->prepare("INSERT INTO likefoto (FotoID,UserID) VALUES(?,?)")->execute([$fotoId,$userId]);
}
header('Location: index.php');exit;
