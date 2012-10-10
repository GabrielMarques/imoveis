<!doctype html>
<html>
<head>
<?php
// header
$this->load->view('admin/shared/header');

// scripts
$this->load->view('admin/shared/scripts');
?>
</head>
<body>
<?php

// navigation
$this->load->view('admin/shared/navigation');
?>
<div class="wrapper">
<div id="content">
<?php
// content
$this->load->view($content);
?>
</div>
<div class="push"></div>
</div>
<?php
// footer
$this->load->view('admin/shared/footer');

?>
</body>
</html>