<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Apartments Model Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */


class Apartments_model extends MY_Model {

	private $connect_timeout = 5;
	private $timeout_total = 10;

	private $zap_url = 'http://www.zap.com.br/imoveis/rio-de-janeiro+rio-de-janeiro+bairros+capital---zona-sul/apartamento-padrao/venda/valor-400.000-a-1.000.000+area-acima-de-60/?tipobusca=avancada&foto=1&ord=precovenda&pag=';
	private $max_price_m2 = 10000;
	private $max_price = 800000;
	private $min_area = 70;
	private $min_rooms = 2;
	private $neighborhoods = array('CATETE', 'GLORIA', 'SANTA TERESA', 'VIDIGAL');

	public function __construct(){
		parent::__construct();
	}

	/**
	 * Get ZAP apartments
	 */

	public function get_zap_apartments($page = 1, $last_page = false, $debug = false){
		$this->load->helper('file');
		$this->load->helper('dom');
		$this->load->spark('curl/1.2.1');

		$errors = array();

		$last = $last_page === false ? $page : $last_page;
		$last_found = false;

		while($page <= $last){

			// execute curl
			$this->curl->create($this->zap_url . $page);

			$params = array(
				CURLOPT_CONNECTTIMEOUT => $this->connect_timeout,
				CURLOPT_TIMEOUT => $this->timeout_total,
			);

			$this->curl->options($params);
			$response = $this->curl->execute();
			//$response = read_file('tests/zap.htm');

			if ($response !== false){
				$response = utf8_encode($response);

				// convert to dom
				$html = str_get_html($response);

				if ($last_page === false){

					$link = $html->find('a.pagNextAll', 0);
					if ($link !== null && $last_found === false){

						$parts = parse_url($link->href);
						parse_str(str_replace('&amp;', '&', $parts['query']), $query);
						$last = (int) $query['pag'];
						$last_found = true;

						if ($debug === true) { echo 'last: ' . $last . br(); }
					}
				}

				// loop through apartments
				foreach($html->find('div.item') as $item) {
					$apartment = new Apartment();

					// url and zap id
					$url = $item->find('a[id$=_lnkFull]', 0)->href;
					$a = explode('/', $url);
					$a = explode('-', end($a));
					$zap_id = $a[1];

					// check if apartment exists in db
					$id = false;
					$apartment
						->where('zap_id', $zap_id)
						->limit(1)
						->get();

					if ($apartment->exists()){
						$id = $apartment->id;
					}else{
						$apartment->zap_id = $zap_id;
						$apartment->url = $url;
						$apartment->status = 2;
						$apartment->type = 1;
						$apartment->flagged = 1;
					}

					// thumb
					$apartment->image = $item->find('img[id$=_imgFotoPrincipal]', 0)->src;

					// address
					$address = $item->find('h3 span.location', 0)->plaintext;
					$a = explode('-', $address);
					$apartment->neighborhood = trim($a[0]);
					$apartment->street = $item->find('h3 span.street-address', 0)->plaintext;

					// price
					$price = $item->find('.valorOferta', 0)->plaintext;
					$price = (int) str_replace(array('R$', '.', ' '), '', $price);

					// update prices table
					if (isset($apartment->id) && isset($apartment->price) && (int) $apartment->price !== $price){
						$past_price = new Past_price();
						$past_price->apartment_id = $apartment->id;
						$past_price->price = $apartment->price;
						$past_price->save();

						$apartment->flagged = 2;
					}

					$apartment->price = $price;

					// realtor
					$realtor = $item->find('.itemLogo img', 0);
					if ($realtor !== null){
						$apartment->realtor = $realtor->alt;
					}

					// area + rooms
					foreach($item->find('.itemCaracteristicas p') as $value) {
						if (strtolower(convert_accented_characters($value->find('span', 0)->plaintext)) == 'area'){
							$apartment->area = (int) str_replace('Área', '', $value->plaintext);
						}else if ($value->find('span', 0)->plaintext == 'Dorms'){
							$apartment->rooms = (int) str_replace('Dorms', '', $value->plaintext);
						}
					}

					// date
					$zap_date = $item->find('.itemData span', 0)->plaintext;
					$zap_date = str_replace('data de publicação: ', '', $zap_date);
					$zap_date = trim($zap_date);
					$a = explode('/', $zap_date);
					//array_map('trim', $a);
					$apartment->zap_date = $a[2] . '-' . $a[1] . '-' . $a[0];

					if (is_valid_date($apartment->zap_date) === false){
						continue;
					}

					// highlight?
					if (
						isset($apartment->area) &&
						$apartment->area >= $this->min_area &&
						isset($apartment->rooms) &&
						$apartment->rooms >= $this->min_rooms &&
						isset($apartment->price) &&
						$apartment->price <= $this->max_price &&
						($apartment->price / $apartment->area) <= $this->max_price_m2 &&
						isset($apartment->neighborhood) &&
						in_array($apartment->neighborhood, $this->neighborhoods) === false						
					){
						$apartment->flagged = 2;
					}

					if ($debug === true) {
						// output debug
						echo 'url: ' . internal_anchor($this->zap_url . $page) . br() . '<strong>' . $apartment->zap_id . '</strong>'. br();

						$fields = array('url', 'zap_id', 'neighborhood', 'street', 'price', 'area', 'rooms', 'zap_date', 'image', 'realtor');
						foreach($fields as $field){
							echo $field . ': ' . $apartment->$field . br();
						}

						echo br();

						$success = true;
					}else{
						// save
						$success = $apartment->save();
					}

					if ($success === false){
						$errors[] = '<strong>' . $apartment->zap_id . '</strong> - ' . $apartment->error->string;
					}

					unset($apartment);
				}

				// free html
				unset($html);
				unset($response);
			}else{
				if ($debug === true) {
					echo 'url: ' . internal_anchor($this->zap_url . $page) . ' - no response' . br();
				}
			}

			$page++;
		}

		if ($debug === true) {
			exit;
		}

		//if ($page > 9){ return $errors; }

		if (sizeof($errors) > 0){
			return $errors;
		}

		return true;

	}

	/**
	 * Upodate ZAP apartments
	 */

	public function update_zap_apartments($rows, $debug = false){
		$this->load->helper('file');
		$this->load->helper('dom');
		$this->load->spark('curl/1.2.1');

		$errors = array();

		$apartments = new Apartment();
		$apartments
			->where_in('id', $rows)
			->get();

		foreach($apartments as $apartment){

			// execute curl
			$this->curl->create($apartment->url);

			$params = array(
				CURLOPT_CONNECTTIMEOUT => $this->connect_timeout,
				CURLOPT_TIMEOUT => $this->timeout_total,
			);

			$this->curl->options($params);
			$response = $this->curl->execute();
			//$response = read_file('tests/zap_apartment.htm');

			if ($response !== false){
				$response = utf8_encode($response);

				// convert to dom
				$html = str_get_html($response);

				// description
				if (empty($apartment->description)){
					$description = $html->find('div.fc-descricao h3', 0)->plaintext;
					$description = substr($description, 0, 2000);
					$apartment->description = utf8_encode(strip_tags($description));
				}

				// building costs
				$building_costs = $html->find('li#ctl00_ContentPlaceHolder1_resumo_liCondominio span.featureValue', 0);
				if ($building_costs !== null){
					$apartment->building_costs = (int) str_replace(array('R$', '.', ' '), '', $building_costs->plaintext);
				}

				// parking
				$parking = $html->find('li#ctl00_ContentPlaceHolder1_resumo_liQtdVagas span.featureValue', 0);
				if ($parking !== null){
					$apartment->parking = (int) str_replace(array('vaga', 'vagas'), '', $parking->plaintext);
				}

				// realtor
				if (empty($apartment->realtor)){
					$realtor = $html->find('h4.sellerName', 0);
					if ($realtor !== null){
						$apartment->realtor = $realtor->plaintext;
					}
				}

				// realtor phone
				if (empty($apartment->realtor_phone)){
					$realtor_phone = $html->find('p#ctl00_ContentPlaceHolder1_contate_pVerTelefone', 0);
					if ($realtor_phone !== null){
						$apartment->realtor_phone = $realtor_phone->plaintext;
					}
				}

				// images
				foreach($html->find('div#galleria a img') as $img) {
					$zap_image = new Zap_image();
					$zap_image->apartment_id = $apartment->id;
					$zap_image->url = $img->src;
					$zap_image->save();
				}

				if ($debug === true) {
					// output debug
					echo '<strong>' . $apartment->zap_id . '</strong>'. br();

					$fields = array('url', 'description', 'building_costs', 'parking', 'realtor_phone');
					foreach($fields as $field){
						echo $field . ': ' . $apartment->$field . br();
					}

					echo br();

					$success = true;
				}else{
					// save
					$success = $apartment->save();
				}

				if ($success === false){
					$errors[] = '<strong>' . $apartment->zap_id . '</strong> - ' . $apartment->error->string;
				}

				unset($html);
			}
		}

		if ($debug === true) {
			exit;
		}

		if (sizeof($errors) > 0){
			return $errors;
		}

		return true;
	}

	/**
	 * Flag
	 */

	public function flag($rows, $value = true){
		if (is_array($rows) === false || $value < 1 || $value > 2){
			return false;
		}
	
		$apartments = new Apartment();
		$apartments->where_in('id', $rows)->update('flagged', $value);
		
		return true;	
	}

	/**
	 * Change status
	 */

	public function update_status($rows, $status = 2){
		if (is_array($rows) === false || $status < 1 || $status > 5){
			return false;
		}
		
		$apartments = new Apartment();
		$apartments->where_in('id', $rows)->update('status', $status);
		
		return true;
	}

}

/* End of file apartments_model.php */
/* Location: ./application/models/apartments_model.php */