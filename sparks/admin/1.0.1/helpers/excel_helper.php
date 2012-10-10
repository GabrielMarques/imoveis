<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Excel Helper
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

require_once ADMINPATH . 'helpers/PHPExcel.php';
require_once ADMINPATH . 'helpers/PHPExcel/IOFactory.php';

function prepare_excel_fields($fields){
	$ci =& get_instance();

	$final_fields = false;
	$space_cnt = 0;
	foreach($fields as $key){
		if ($key === null){
			$key = $key . '_' . $space_cnt;
			$label = '';
			$space_cnt++;
		}else{
			$label = $ci->lang->line($key) ? $ci->lang->line($key) : $key;
		}

		$final_fields[$key] = array(
			'label' => $label,
			'export_type' => 'str',
		);
	}
	return $final_fields;
}

function set_excel_output($sheet, $row, $col, $value, $excel_type = 'str', $func = false){
	$ci =& get_instance();

	if (is_array($value)){
		$value = $value['value'];
	}

	$type = false;
	$format = false;

	switch($excel_type){
		case 'int':
			//$type = PHPExcel_Cell_DataType::TYPE_NUMERIC;
			$format = PHPExcel_Style_NumberFormat::FORMAT_NUMBER;
			break;
		case 'decimal':
			$value = $value == null ? 0 : $value;
			$format = PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00;
			break;
		case 'percent':
			$format = PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE;
			break;
		case 'currency':
			$value = $value == null ? 0 : $value;
			$format = '"R$ "#,##0.00_-';
			break;
		case 'date':
		case 'datetime':
			if ($value != null){
				$value = strtotime($value . ' GMT');
				$value = PHPExcel_Shared_Date::PHPToExcel($value);
				$format = PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY;
			}else{
				$type = PHPExcel_Cell_DataType::TYPE_STRING;
			}
			break;
		case 'week':
			$type = PHPExcel_Cell_DataType::TYPE_STRING;
			$value = format_date($value, 'default_day_month_format', '-6 days') . ' - ' . format_date($value, 'default_date_format');
			break;
		case 'month':
			$value = ucfirst(format_date($value, 'default_month_format'));
			break;
		case 'blob':
			$value = character_limiter($value, $ci->config->item('default_blob_limit'), ' [...]');
			$value = strip_tags($value);
			break;
		case 'list':
			$values = explode($ci->config->item('list_separator'), $value);
			$value = implode(', ', $values);
		case 'str':
			$type = PHPExcel_Cell_DataType::TYPE_STRING;
			break;
		default:
			break;
	}

  if ($type !== false){
  	$sheet->getCellByColumnAndRow($col, $row)->setValueExplicit($value, $type);
  }else{
		$sheet->getCellByColumnAndRow($col, $row)->setValue($value);
  }
  if ($format !== false){
  	$sheet->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode($format);
  }
}

/* End of file excel_helper.php */
/* Location: ./application/helpers/excel_helper.php */