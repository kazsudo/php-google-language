<?php
//文字コードはUTF-8
require __DIR__.'/../vendor/autoload.php';

use KazSudo\Google\Language\ServiceWrapper;

$ret = null;

if(isset($_FILES['src']) && is_uploaded_file($_FILES['src']['tmp_name'])){
  $content = file_get_contents($_FILES['src']['tmp_name']);
  if($content){
    $language = new ServiceWrapper(__DIR__.'/../app/config.php');
    $ret = $language->analyzeText($content, basename($_FILES['src']['name']));
  }
}
?><!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>Analyze</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<link rel="stylesheet" href="./res/css/layout.css">
</head>
<body>
<form method="post" enctype="multipart/form-data">
<input type="file" name="src" value="" accept="text/plain" placeholder="Select text file to analyze">
<input type="submit" value="Analyze">
<?php if(!is_null($ret)){
  printf('<p class="message">Saved as <a href="./view.php?name=%1$s" target="_blank">name:%1$s</a></p>', $ret['_name']);
} ?>
</form>
</body>
</html>
