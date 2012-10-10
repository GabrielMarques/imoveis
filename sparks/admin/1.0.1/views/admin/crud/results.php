<div class="container-fluid">
<div class="page-header controls-header">
<?php
echo '<div class="pull-right">' . anchor($this->navigation->current_route, '&larr; ' . $this->navigation->get_page_title()) . '</div>';
echo heading($header_title, 2);
?>
</div>
<?php
// alerts
echo $this->alerts->render();

// set table template and heading
$tmpl = array('table_open' => '<table class="table table-results">');
$this->table->set_template($tmpl);

$headers = array();
foreach($fields as $field){
	$headers[] = $this->lang->line($field);
}
$this->table->set_heading($headers);

// results
foreach($results as $row){
	$row_values = array();
	foreach($fields as $field){
		if ($field === 'status'){
			if ($row['status'] === false){
				$row_value = '<span class="label label-important">' . $this->lang->line('error') . '</span>';
			}else{
				$row_value = '<span class="label label-success">' . $this->lang->line('success') . '</span>';
			}
		}else{
			$row_value = $row[$field];
		}
		$row_values[] = $row_value;
	}
	$this->table->add_row($row_values);
}

// render table
echo $this->table->generate();

?>
</div>