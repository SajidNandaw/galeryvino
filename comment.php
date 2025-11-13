<?php
require_once 'config.php';
require_login();
$fotoId=intval($_POST['FotoID']);
$komen=trim($_POST['IsiKomentar']);
$userId=$_SESSION['UserID'];
if($fotoId&&$komen){
  $pdo->prepare("INSERT INTO komentarfoto (FotoID,UserID,IsiKomentar) VALUES(?,?,?)")->execute([$fotoId,$userId,$komen]);
}
header('Location: index.php');exit;
