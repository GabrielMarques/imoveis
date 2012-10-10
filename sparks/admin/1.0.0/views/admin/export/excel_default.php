<?php
$sheet = $excel->getActiveSheet();
$sheet->getDefaultColumnDimension()->setWidth(20);

// header
$row_cnt = 1;
$col_cnt = 0;
foreach($fields as $key => $field){
  $sheet->setCellValueByColumnAndRow($col_cnt, $row_cnt, $field['label']);
  $sheet->getStyleByColumnAndRow($col_cnt, $row_cnt)->applyFromArray($header_style);
  $col_cnt++;
}

// results
$row_cnt++;
foreach($rows as $row){
  $col_cnt = 0;
  foreach($fields as $key => $field){
  	// get db value
  	$row_value = $row[$key];

  	// save value in sheet
  	$export_type = isset($field['export_type']) ? $field['export_type'] : 'str';
	  set_excel_output($sheet, $row_cnt, $col_cnt, $row_value, $export_type);
    $col_cnt++;
  }
  $row_cnt++;
}

// footer
$col_cnt = 0;
foreach($fields as $key => $field){
  if (isset($totals) && $totals === true){
  	if ($col_cnt == 0){
			set_excel_output($sheet, $row_cnt, $col_cnt, $this->lang->line('total'), 'str');
  	}else if ($field['export_type'] === 'int' || $field['export_type'] === 'currency' || $field['export_type'] === 'decimal'){
  		$col_str = PHPExcel_Cell::stringFromColumnIndex($col_cnt);
  		$func = '=SUM('  . $col_str . '2:' . $col_str . ($row_cnt - 1) . ')';
	    set_excel_output($sheet, $row_cnt, $col_cnt, $func, $field['export_type'], true);
  	}
  }
  $sheet->getStyleByColumnAndRow($col_cnt, $row_cnt)->applyFromArray($footer_style);
  $col_cnt++;
}

// info
$row_cnt += 2;
if (isset($info) && $info !== false){
  $sheet->setCellValueByColumnAndRow(0, $row_cnt, $info);
  $sheet->getStyleByColumnAndRow(0, $row_cnt)->applyFromArray($info_style);
}
?>