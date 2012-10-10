<div id="details-app">
<?php
// form
echo form_open('', array('class' => 'form-horizontal form-details'));
?>
<div class="box box-large span10 center-span">
<div class="box-header">
<div class="row-actions pull-right">
<?php
$row_options_list = array();

// actions
$row_actions = $this->actions->get_actions('row');
if ($row_actions){
  foreach($row_actions as $route => $action){
    $btn = $this->actions->render_btn($route, array('row' => $row_raw, 'as_btn' => false));
    if ($btn !== false){
      $row_options_list[] = $btn;
    }
  }
}

// back
$secondary_header_title = isset($secondary_header_title) ? $secondary_header_title : '&larr; ' . $this->navigation->get_page_title();
if ($secondary_header_title !== false){
	$row_options_list[] = anchor($this->navigation->current_route, $secondary_header_title);
}

echo implode('<span class="action-divider">|</span>', $row_options_list);
?>
</div>
<?php
echo heading($header_title, 3);

// alerts
echo $this->alerts->render();
?>
</div>
<div class="box-inner">
<?php
// values
echo $this->build_form->render_details();
?>
</div>
</div>
<?php
// close form
echo form_close();

// modals
echo $this->actions->render_modals('row');
?>
</div>