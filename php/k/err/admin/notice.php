<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>友情提示</title>
<link rel="stylesheet" href="<?php echo uv('/k/err/css/admin.css'); ?>" type="text/css">
</head>
<body>
<div class="box1">
  <div class="box1-top clearfix">
    <div class="box1-l">
      <img src="<?php echo uv('/k/err/images/tan.png'); ?>" width="128" height="128">
    </div>
    <div class="box1-r">
      <div class="box1-gx">提示，</div>
      <div class="box1-txt"><?php echo $msg; ?></div>
    </div>
  </div>
  <div class="box1-button clearfix">
    <?php if ( $op ): ?>
    <?php if ( $op == 'login' ): ?><div class="box1-btn"><button onClick="top.location.href='<?php echo u('/adm/login'); ?>';">重新登录</button></div><?php endif; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>