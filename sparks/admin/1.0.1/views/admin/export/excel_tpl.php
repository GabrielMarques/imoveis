<?php

// config
$locale = 'pt_br';
PHPExcel_Settings::setLocale($locale);

$excel = new PHPExcel();
$excel->getProperties()->setCreator($this->config->item('admin_site_name_short'));
$excel->getProperties()->setTitle($title);
$excel->getProperties()->setSubject('');
$excel->getProperties()->setDescription('');

$excel->getDefaultStyle()->getFont()->setName('Arial');
$excel->getDefaultStyle()->getFont()->setSize(10);
$sheet = $excel->getActiveSheet();
$sheet->getDefaultColumnDimension()->setWidth(20);

// load report
$data = array(
	'excel' => $excel,
	'header_style' => array(
		'font' => array('bold' => true),
		'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => array('rgb' => 'c0c0c0')),
	),
	'text_style' => array(),
	'footer_style' => array(
		'font' => array('bold' => true),
		'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => array('rgb' => 'c0c0c0')),
	),
	'info_style' => array(
		'font' => array('italic' => true)
	),
);
$this->load->view($content, $data);

//print_r($excel);
//exit;

// output to file
$title = convert_accented_characters($title);
$excel_format = $this->config->item('excel_format');
$file = $title . '.' . $excel_format;
ob_end_clean();

// check excel format
switch($excel_format){
  case 'xls':
    header('Content-Type: application/vnd.ms-excel');
    $excel_writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
    break;
  case 'xlsx':
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $excel_writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    break;
}
header('Content-Disposition: attachment;filename="' . $this->config->item('admin_site_name_short') . ' - ' . $file . '"');
header('Cache-Control: max-age=0');
$excel_writer->save('php://output');
$excel->disconnectWorksheets();
unset($excel);
exit;

?>