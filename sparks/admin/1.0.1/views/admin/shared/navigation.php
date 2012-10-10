<div class="navbar navbar-inverse navbar-fixed-top">
<div class="navbar-inner">
<div class="container-fluid">
<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</a>
<?php
//brand
$default_controller = MULTI_APP === true ? $this->router->routes['default_controller_admin'] : $this->router->routes['default_controller'];
if ($this->config->item('admin_logo_on') === true){
	echo anchor(ADMIN_PREFIX . $default_controller, img('assets/img/logo_admin_white.png', array('alt' => $this->config->item('admin_site_name_short'))), array('class' => 'brand-image'));
}else{
	echo anchor(ADMIN_PREFIX . $default_controller, $this->config->item('admin_site_name_short'), array('class' => 'brand'));
}
?>
<div class="nav-collapse">
<ul class="nav">
<?php
// render main menu
echo $this->navigation->render('main');
?>
</ul>
<ul class="nav pull-right">
<?php
// render main menu
echo $this->navigation->render('secondary');
?>
</ul>
</div>
</div>
</div>
</div>