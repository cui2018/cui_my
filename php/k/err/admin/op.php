<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>操作成功</title>
<link rel="stylesheet" href="<?php echo uv('/k/err/css/admin.css'); ?>" type="text/css">
</head>
<body>
<div class="box1">
  <div class="box1-top clearfix">
    <div class="box1-l">
      <img src="<?php echo uv('/k/err/images/right.png'); ?>" width="128" height="128">
    </div>
    <div class="box1-r">
      <div class="box1-gx">恭喜您！</div>
      <div class="box1-txt">您的数据已保存成功！</div>
    </div>
  </div>
  <div class="box1-button clearfix">
<?php if ( $arr ): ?>
	<?php foreach ($arr as $item): ?>
    <?php if ( $item['link'] == 'self' ): ?>
    <div class="box1-btn"><button onClick="window.location.href=window.location.href;"><?php echo $item['text']; ?></button></div>
    <?php elseif ( $item['link'] == 'iframeGoBack' ): ?>
    <div class="box1-btn"><button onClick="parent.closeIframe();"><?php echo $item['text']; ?></button></div>
    <?php else: ?>
    <div class="box1-btn"><button onClick="window.location.href='<?php echo $item['link']; ?>';"><?php echo $item['text']; ?></button></div>
    <?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>
  </div>
</div>
</body>
</html>
<?php if ( $jsapi ): ?>
<script>
parent.jsapi("<?php echo $jsapi; ?>",<?php echo $data ? json_encode($data) : '{}'; ?>);
</script>
<?php endif; ?>