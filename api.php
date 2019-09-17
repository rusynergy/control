<?php

	namespace Renua;

	class Control {

		private $key = '';
		private $ip  = '0.0.0.0';
		private $endpoint = 'https://xapi.renua.space:9999/';

		function __construct()
		{
			$this->ip = gethostbyname(gethostname());
			$this->key = sha1($this->ip . '#renua.space#');
		}

	    private function query(string $action, array $data, string $method = 'GET')
		{
		 	$ch = curl_init();
			$get = '';

			switch ($method)
			{
				case 'GET': { $get = '?' . http_build_query($data); } break;
			}

			$get = '?' . http_build_query($data);

			curl_setopt($ch, CURLOPT_URL, $this->endpoint . $action . '/' . $get);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			switch ($method)
			{
				case 'PUT':
				{
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
					curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
				} break;
			}

			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'X-Auth: ' . $this->key
			));
			$data = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if ($httpcode == 200)
			{
			   	$data = json_decode($data);
				if (isset($data->errno))
					return array(
					  	'result' => false,
						'text'   => $data->str
					);
				return $data;
			}

			else
				return false;
		}

		/**
		* Get information about shop, owner and other shops
		* @param int $shop_id Shop's Id
		*/
		function shop_info(int $shop_id = 0)
		{
			return $this->query('shop', array(
				'id' => $shop_id
			));
		}

		/**
		* Get information about shop, owner and other shops by domain
		* @param string $domain Domain name without protocol
		*/
		function shop_bydomain(string $domain = 'renua.space')
		{
			return $this->query('shop/bydomain', array(
				'domain' => $domain
			));
		}

		/**
		* Create new shop to old customer
		* @param int $user_id UserId
		*/
		function shop_new(int $user_id = 0)
		{
			return $this->query('shop/new', array(
				'user' => $user_id
			), 'PUT');
		}

		/**
		* Get Info about user
		* @param int $user_id UserId
		*/
		function user_info(int $user_id = 0)
		{
			return $this->query('user', array(
				'id' => $user_id
			));
		}





	}

?>