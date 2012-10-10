<?php
/**
 * OAuth Vimeo Provider
 *
 *
 * @package    OAuth
 * @category   Provider
 * @author
 */

class OAuth_Provider_Vimeo extends OAuth_Provider {

	public $name = 'vimeo';

	public function url_request_token()
	{
		return 'http://vimeo.com/oauth/request_token';
	}

	public function url_authorize()
	{
		return 'http://vimeo.com/oauth/authorize';
	}

	public function url_access_token()
	{
		return 'http://vimeo.com/oauth/access_token';
	}

	public function get_user_info(OAuth_Consumer $consumer, OAuth_Token $token)
	{

	}

} // End Provider_vimeo