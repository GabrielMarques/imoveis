<?php
$class = $show_filters_form === true ? 'span2' : 'hide';
echo '<div id="sidebar" class="' . $class . '">';

echo form_open('', array('id' => 'filters-form', 'class' => 'form-vertical'));
?>
<div class="box box-small container-filters">
<div class="box-inner">
<?php
// filters
echo $this->build_form->render_elements();
?>
</div>
<div class="box-footer">
<?php
echo form_button(array('content' => $this->lang->line('filters'), 'id' => 'filters-btn', 'class' => 'btn btn-primary')) . 
	form_button(array('content' => $this->lang->line('reset'), 'id' => 'filters-reset-btn', 'class' => 'btn'));
?>
</div>
</div>
<?php
echo form_close();
echo '</div>';
?>