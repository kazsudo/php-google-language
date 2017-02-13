<?php
//文字コードはUTF-8
require __DIR__.'/../vendor/autoload.php';

use KazSudo\Google\Language\ServiceWrapper;

$language = new ServiceWrapper(__DIR__.'/../app/config.php');

$name = null;
$content = null;
$data = null;
if(isset($_GET['name']) && $_GET['name']){
  $name = $_GET['name'];
  $data = $language->getEntityFromDataStore($name);
}
else if(isset($_POST['content']) && $_POST['content']){
  $content = $_POST['content'];
  $name = mb_strimwidth($content, 0, 10, '...');
  $data = $language->annotateText($content);
}
?><!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>View <?php if($name && $data){ printf(" %s", $name); } ?></title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<link rel="stylesheet" href="./res/css/layout.css">
<script src="./res/js/view.js"></script>
</head>
<body>
<?php if(is_null($data)){ ?>
<section id="input">
<form>
<h1>View from DataStore</h1>
<input type="text" name="name" value="" placeholder="Google DataStore name">
<input type="submit" value="View">
</form>

<form method="post">
<h1>View from Text</h1>
<textarea name="content"></textarea>
<input type="submit" value="View">
</form>
</section>
<?php }else{ ?>
<section id="list" lang="<?php echo $data['language']; ?>">
<div id="sentence-0" class="sentence">
<?php
$entity_offsets = [];
for($index = 0; $index < count($data['entities']); $index++){
  $entity = $data['entities'][$index];
  $entity_info = [
    'name' => $entity['name'],
    'type' => $entity['type'],
    'salience' => $entity['salience']
  ];
  if(isset($entity['metadata']['mid'])){
    $entity_info['knowledge graph mid'] = $entity['metadata']['mid'];
    $entity_info['knowledge graph mid'] = sprintf('<a href="https://www.google.com/trends/explore?date=all&amp;q=%s" target="_blank">Google Trends</a>', $entity['metadata']['mid']);
  }
  if(isset($entity['metadata']['wikipedia_url'])){
    $entity_info['wikipedia url'] = sprintf('<a href="%s" target="_blank">WikiPedia</a>', $entity['metadata']['wikipedia_url']);
  }
  for($i = 0; $i < count($entity['mentions']); $i++){
    $mention = $entity['mentions'][$i];
    $offset = $mention['text']['beginOffset'];
    $entity_offsets[$offset] = $entity_info;
    $entity_offsets[$offset]['mention type'] = $mention['type'];
  }
}

$sentence_offsets = [];
for($index = 0; $index < count($data['sentences']); $index++){
  $sentence = $data['sentences'][$index];
  $offset = $sentence['text']['beginOffset'];
  $sentence_offsets[$offset] = ['index' => $index, 'content' => $sentence['text']['content']];
}
printf('<span class="alltext">%s</span>', $sentence_offsets[0]['content']);

$limit = count($data['tokens']);
for($index = 0; $index < $limit; $index++){
  $token = $data['tokens'][$index];
  $text = $token['text'];
  $beginOffset = $text['beginOffset'];
  $partOfSpeech = $token['partOfSpeech'];
  $dependencyEdge = $token['dependencyEdge'];
  $lemma = $token['lemma'];
  $class = '';
  if($beginOffset > 0 && isset($sentence_offsets[$beginOffset])){
    printf("</div>\n<div id=\"sentence-%d\" class=\"sentence\"><span class=\"alltext\">%s</span>\n",
      $sentence_offsets[$beginOffset]['index'],
      $sentence_offsets[$beginOffset]['content']
    );
  }

  if($index < $limit - 1 && strlen($text['content']) + $beginOffset < $data['tokens'][$index+1]['text']['beginOffset']){
    $class .= ' spacing';
  }
  printf(
    '<span id="token-%1$s" data-index="%1$s" data-headTokenIndex="%2$s" data-beginOffset="%3$s" class="token">',
    $index,
    $dependencyEdge['headTokenIndex'],
    $beginOffset
  );
  printf('<span class="content%s"><span class="text">%s</span><div class="info"><span class="tag">%s</span><span class="label">%s</span><span class="lemma%s">%s</span></div>',
    $class,
    $text['content'],
    $partOfSpeech['tag'],
    $dependencyEdge['label'],
    (isset($entity_offsets[$beginOffset]) ? ' entity' : ''),
    $lemma
  );
  echo '<table class="detail"><tr class="speech title"><th colspan="2">Part Of Speech</th></tr>';
  foreach(['aspect','case','form','gender','mood','number','person','proper','reciprocity','tense','voice'] as $speech_label){
    if(!preg_match('/_UNKNOWN/', $partOfSpeech[$speech_label])){
      printf('<tr class="speech %1$s"><th>%1$s</th><td>%2$s</td></tr>',
        ucwords($speech_label),
        $partOfSpeech[$speech_label]
      );
    }
  }
  if(isset($entity_offsets[$beginOffset])){
    echo '<tr class="entity title"><th colspan="2">Entity</th></tr>';
    foreach($entity_offsets[$beginOffset] as $entity_label => $entity_value){
      printf('<tr class="entity %1$s"><th>%1$s</th><td>%2$s</td></tr>',
        ucwords($entity_label),
        $entity_value
      );
    }
  }
  echo '</table></span></span>';
}
?>
</div>
<?php if(!is_null($content)){ ?>
<form method="post">
<textarea name="content"><?php echo $content; ?></textarea>
<input type="submit" value="View">
</form>
<?php } ?>
</section>
<section id="tree">
  <nav class="back">back</nav>
</section>
<?php } ?>
</body>
</html>
