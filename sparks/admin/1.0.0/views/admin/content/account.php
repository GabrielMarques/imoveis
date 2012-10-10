<?php
// form
echo form_open($this->navigation->current_route, array('class' => 'form-horizontal'));
echo form_hidden('process', 'true');
?>
<div class="box box-large span10 center-span">
<div class="box-header">
<?php
echo heading($header, 3);

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
echo '<p class="pull-right"><em>* ' . $this->lang->line('mandatory') . '</em></p>';
echo form_submit(array('value' => $this->lang->line('submit'), 'class' => 'btn btn-primary btn-large'));
echo anchor(ADMIN_PREFIX, $this->lang->line('cancel'), array('class' => 'btn btn-large'));
?>
</div>
</div>
<?php
// close form
echo form_close();
?>