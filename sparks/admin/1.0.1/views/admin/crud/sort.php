<?php
// form
$action = isset($action) ? $action : $this->navigation->current_route . '/sort';
echo form_open($action, array('id' => 'sort-app', 'class' => 'form-vertical'));
echo form_hidden('process', 'true');
?>
<div class="box box-large span10 center-span">
<div class="box-header">
<?php
echo '<div class="pull-right">' . anchor($this->navigation->current_route, '&larr; ' . $this->navigation->get_page_title()) . '</div>';
echo heading($this->lang->line('sort'), 3);

// alerts
echo $this->alerts->render();
?>
</div>
<div class="box-inner">
<?php

// set table template and heading
$tmpl = array('table_open' => '<table class="table table-sort" id="sort-table">');
$this->table->set_template($tmpl);

$headers = array();
foreach($fields as $field){
	$headers[] = $this->lang->line($field);
}
$headers[] = '';
$this->table->set_heading($headers);

// order list
foreach($rows as $row){
	$row_values = array();
	foreach($fields as $field){
		$row_values[] = $row[$field];
	}
	$row_values[] = '<span class="pull-right"><i class="icon-resize-vertical"></i></span>' . form_hidden('rows[]', $row['id']);
	$this->table->add_row($row_values);
}

// render table
echo $this->table->generate();

?>
</div>
<div class="box-footer">
<?php
echo '<p class="pull-right"><em>* ' . $this->lang->line('mandatory') . '</em></p>';
echo form_submit(array('value' => $this->lang->line('submit'), 'class' => 'btn btn-primary btn-large'));
echo anchor($this->navigation->current_route, $this->lang->line('cancel'), array('class' => 'btn btn-large'));
?>
</div>
</div>
<?php
// close form
echo form_close();
?>