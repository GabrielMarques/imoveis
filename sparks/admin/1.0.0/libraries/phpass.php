<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Navigation Class
 *
 * @package
 * @subpackage
 * @category
 * @author Gabriel Marques
 * @link
 */

require ADMINPATH . 'libraries/phpass/PasswordHash.php';

class Phpass{

	private $ci;
	private $strength = 8;
	private $portable = false;

	public function __construct(){
		$this->ci =& get_instance();
	}

	/**
	 * Create hash
	 */

	public function do_hash($raw_password){
		// Hash raw_password using phpass
		$hasher = new PasswordHash($this->strength, $this->portable);
		$hashed_password = $hasher->HashPassword($raw_password);

		// Return the hashed password
		return $hashed_password;
	}

	/**
	 * Check if raw_password matches hashed_password
	 */

	public function check_password($raw_password, $hashed_password){
 		$hasher = new PasswordHash($this->strength, $this->portable);
 		return $hasher->CheckPassword($raw_password, $hashed_password);
 }
}

/* End of file phpass.php */
/* Location: ./application/libraries/phpass.php */