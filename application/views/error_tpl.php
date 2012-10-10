<!DOCTYPE html>
<html lang="en">
<head>
<title><?php echo $this->config->item('site_name_short') . ' / ' . $this->lang->line('error_short'); ?></title>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<link href="<?php echo site_url('favicon.ico'); ?>" rel="shortcut icon" type="image/ico" />
<link type="text/css" rel="stylesheet" href="<?php echo site_url('assets/css/error_page.min.css'); ?>" media="screen" />
</head>
<body>
<div class="container-alert span8 center-span">
<?php

echo heading($heading . '!', 3, 'class="alert-text-error"');
echo '<div class="alert-text-message">' . $message . '</div>' . br(2);

if (isset($btn)){
  echo anchor($btn['route'], $btn['label'], array('class' => 'btn btn-success btn-large'));
}else{
  echo anchor('', $this->lang->line('goto_home'), array('class' => 'btn btn-success btn-large'));
}
?>
</div>
</body>
</html>