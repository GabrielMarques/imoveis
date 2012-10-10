<?php
// open form
echo form_open(ADMIN_PREFIX . 'login', array('class' => 'form-vertical', 'id' => 'form-login'));
echo form_hidden('process', 'true');
?>
<div class="box box-small span5 center-span">
<div class="box-header">
<?php
if ($this->config->item('admin_logo_on') === true){
	echo img(array('src' => 'assets/img/logo_admin.png', 'alt' => $this->config->item('admin_site_name')));
}else{
	echo heading($this->config->item('admin_site_name'), 2);
}

// alerts
echo $this->alerts->render();
?>
</div>
<div class="box-inner">
<?php
// form elements
echo $this->build_form->render_elements();
?>
</div>
<div class="box-footer">
<?php
// form btns
echo form_submit(array('value' => $this->lang->line('enter'), 'class' => 'btn btn-primary btn-large'));
?>
</div>
</div>
<?php
// close form
echo form_close();
?>