<footer>
<div class="container-fluid footer-body">
<p class="pull-right">
<?php
echo $this->lang->line('questions') . nbs() . mailto($this->config->item('admin_site_email'), $this->config->item('admin_site_email'));
if ($this->config->item('admin_site_contact') !== false){
	echo ' - ' . $this->config->item('admin_site_contact');
}
?>
</p>
<p>
<?php
echo '<strong>' . $this->config->item('admin_site_name') . '</strong>';
?>
</p>
</div>
</footer>