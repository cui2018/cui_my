<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>错误提示</title>
<link rel="stylesheet" href="<?php echo uv('/k/err/css/admin.css'); ?>" type="text/css">
</head>
<body>
<div class="box1">
  <div class="box1-top clearfix">
    <div class="box1-l">
      <img src="<?php echo uv('/k/err/images/wrong.png'); ?>" width="128" height="128">
    </div>
    <div class="box1-r">
      <div class="box1-gx">抱歉，</div>
      <div class="box1-txt"><?php echo $msg; ?></div>
    </div>
  </div>
  <div class="box1-button clearfix">
    <?php if ( $op ): ?>
    <?php if ( $op == 'back' ): ?><div class="box1-btn"><button onClick="window.history.back();">返回</button></div><?php endif; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>