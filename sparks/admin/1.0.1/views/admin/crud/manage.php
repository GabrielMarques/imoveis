<div id="manage-app" class="container-fluid">
<div class="page-header controls-header">
<?php
// page title
if (isset($header_title) === false){
	$header_title = '';
}
echo heading($breadcrumbs[sizeof($breadcrumbs) - 1]['label'] . $header_title, 2, 'class="pull-left"');
?>
<div id="spin-container" class="pull-left">
</div>
<div class="pull-right">
<?php
echo $this->actions->render_direct_btns();
?>
</div>
<div class="pull-right">
<?php
echo $this->actions->render_main_btns();
?>
</div>
<div id="upload-progress" class="progress progress-striped active pull-right hide"><div class="bar"></div></div>
</div>
<?php
// alerts
echo $this->alerts->render();
?>
<div id="alert-client-error" class="alert-client alert alert-block alert-error fade in hide">
<a href="#" class="close">×</a>
<p class="message"></p>
</div>
<div id="alert-client-success" class="alert-client alert alert-block alert-success fade in hide">
<a href="#" class="close">×</a>
<p class="message"></p>
</div>
<div class="row-fluid">
<div id="manage-content" class="<?php echo $show_filters_form === true ? 'span10' : 'span12'; ?>">
<div class="manage-header controls-header">
<div class="pull-left">
<?php
echo $this->actions->render_table_btns();
?>
</div>
<div class="pull-left">
<?php
// limit results
$limit_options = array(10 => 10 . nbs() . $this->lang->line('records'), 25 => 25, 50 => 50, 100 => 100);
$options = array();
foreach($limit_options as $key => $value){
  $options[$key] = internal_anchor('#', $value, array('class' => 'limit-options', 'data-limit' => $key));
}
echo  render_btn_dropdown('<i class="icon-list-alt"></i> ' . $this->lang->line('show'), $options, array('id' => 'limit-menu', 'active' => $table_params['limit']));
?>
</div>
<?php
if ($filters_on !== false){
	$active = $show_filters_form === true ? ' active' : '';
  echo form_button(array('content' => '<i class="icon-filter"></i> ' . $this->lang->line('show_filters'), 'id' => 'show-filters-btn', 'class' => 'btn pull-right' . $active, 'data-toggle' => 'button'));
}
?>
<div class="input-append pull-right search-rows">
<input type="text" id="search-input" class="" placeholder="<?php echo $this->lang->line('search'); ?>" value="<?php echo $table_params['search']; ?>">
<a href="#" id="search-clear-btn" class="close hide">×</a>
<button type="button" id="search-btn" class="btn"><i class="icon-search"></i></button>
</div>
</div>
<?php
// open form
if ($this->actions->checkboxes_on === true){
  echo form_open('', array('id' => 'form-manage'));
}
?>
<table id="manage-table" class="table table-manage">
<thead><tr>
<?php
// checkboxes
if ($this->actions->checkboxes_on === true){
  echo '<th class="col-small">' . form_checkbox(array('id' => 'check-all', 'rel' => 'tooltip', 'data-placement' => 'right', 'title' => $this->lang->line('select_all'))) . '</th>';
}

// headers
foreach($fields as $key => $field){
	$col_width = isset($field['col_width']) ? ' style="width:' . $field['col_width'] . 'px;"' : '';
	if (isset($field['sort']) === false || $field['sort'] === true){
		$sort = '';
		if ($table_params['order_by']['field'] === $key){
			$sort = $table_params['order_by']['direction'] === 'asc' ? ' sort-asc' : ' sort-desc';
		}
		echo '<th class="table-header' . $sort . '" data-field="' . $key . '"' . $col_width . '>' . $field['label'] . '</th>';
	}else{
		echo '<th' . $col_width . '>' . $field['label'] . '</th>';
	}
}

// actions
$row_actions = $this->actions->get_actions('row');
if ($row_actions !== false){
  echo '<th><div class="pull-right">' . $this->lang->line('actions') . '</div></th>';
}
?>
</tr></thead>
<?php
// totals
if (isset($totals) && $totals !== false){
	echo '<tbody class="totals"><tr id="totals-row">';
	foreach($fields as $key => $field){
		echo '<td>';
		echo isset($totals[$key]) && $totals[$key] !== null ? $totals[$key] : nbs();
		echo '</td>';
	}
	echo '</tr></tbody>';
}
?>
<tbody id="table-body">
</tbody>
</table>
<div class="manage-footer">
<p id="table-info" class="help-text pull-left">
</p>
<div class="pagination pagination-right pagination-condensed">
<ul id="pagination">
</ul>
</div>
</div>
<?php
// close form
if ($this->actions->checkboxes_on === true){
	echo form_close();
}

// obs
if (isset($obs) && $obs !== false){
	echo '<em>* ' . $obs . '</em>';
}
?>
</div>
<?php
// filters
if ($filters_on === true){
	$this->load->view('admin/crud/filters');
}
?>
</div>
<?php
// modals
echo $this->actions->render_modals();
?>
</div>