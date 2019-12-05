<?php

	namespace Renua;

    class Billing {

		protected $key = '';
		protected $ip  = '255.0.0.0';
        protected $ua = 'Client/1.1.5';
		protected $endpoint = '';

		function __construct(string $endpoint, string $salt)
		{
		    $this->endpoint = 'https://' . $endpoint . '/'; 
			$this->ip = gethostbyname(gethostname());
			$this->key = sha1($this->ip . $salt);          
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
			curl_setopt($ch, CURLOPT_USERAGENT, isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : $this->ua);
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
                
               	case 'POST':
				{
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
				} break;
                
                case 'DELETE':
				{
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                    curl_setopt($ch, CURLOPT_POST, true);
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
			   	$data = json_decode($data, true);              
				if (isset($data['errno']))
					return array(
					  	'result' => false,
						'text'   => $data['str']
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
		function shop_info(int $shop_id = 0, int $ssl = 0)
		{
			return $this->query('shop', array(
				'id' => $shop_id,
                'ssl' => $ssl,
			));
		}
        
        /**
		* Get all active shops
		*/
		function shop_all()
		{
			return $this->query('shop/all', array());
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
        * @param string $channel Redis channel
		*/
		function shop_new(int $user_id = 0, string $channel = '')
		{
			return $this->query('shop/new', array(
				'user' => $user_id,
                'channel' => $channel
			), 'PUT');
		}
        
        /**
		* Switch On Site
		* @param int $shop_id ShopId
		*/
		function shop_on(int $shop_id = 0)
		{
			return $this->query('shop/on', array(
				'id' => $shop_id
			), 'POST');
		}
        
        /**
		* Switch Off Site
		* @param int $shop_id ShopId
		*/
		function shop_off(int $shop_id = 0)
		{
			return $this->query('shop/off', array(
				'id' => $shop_id
			), 'POST');
		}
        
        /**
		* Owner Password
		* @param int $shop_id ShopId
		*/
		function shop_ownerpassword(int $shop_id = 0, string $password)
		{
			return $this->query('shop/ownerpassword', array(
				'id' => $shop_id,
                'password' => $password
			), 'POST');
		}
        
        
       	/**
		* Set new shop's name
		* @param int $shop_id ShopId
        * @param string $name Name
		*/
		function shop_setname(int $shop_id = 0, string $name = '')
		{
			return $this->query('shop/name', array(
				'id'   => $shop_id,
                'name' => $name,
			), 'POST');
		}
        
        /**
		* Set shop options
		* @param int $shop_id ShopId
        * @param string $name Name
		*/
		function shop_options(int $shop_id = 0, string $name = '', int $tariff = 0, bool $status = true, int $domain_id = 0)
		{
			return $this->query('shop/options', array(
				'id'     => $shop_id,
                'name'   => $name,
                'tariff' => $tariff,
                'main'   => $domain_id,
                'status' => $status,
			), 'POST');
		}
        
        /**
		* Connect new domain to shop
		* @param int $shop_id ShopId
		*/
		function shop_connectdomain(int $shop_id = 0, string $domain)
		{
			return $this->query('shop/connectdomain', array(
				'id'     => $shop_id,
                'domain' => $domain
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

		/**
		* Register new user and create new shop
		* @param string $email E-Mail address
		*/
		function user_new(string $email = 'd@ufanet.xyz')
		{
			return $this->query('user/new', array(
				'email' => $email
			), 'PUT');
		}
        
        /**
		* Login User
		* @param string $email E-Mail address
        * @param string $password Password
		*/
		function user_login(string $email = 'd@ufanet.xyz', string $password)
		{
			return $this->query('user/login', array(
				'email' => $email,
                'password' => $password
			), 'POST');
		}
        
        /**
		* Top up balance by bonus
		* @param int $user_id User Id
        * @param float $amount Amount in RUB
		*/
		function user_bonus(int $user_id, float $amount = 300)
		{
			return $this->query('user/bonus', array(
				'user' => $user_id,
                'amount' => $amount
			), 'PUT');
		}
        
        
        /**
		* Get Info about tariffs		
		*/
		function tariff_list()
		{
			return $this->query('tariff', array());
		}
        
        
        /**
		* Get payment methods		
		*/
		function money_gates()
		{
			return $this->query('money', array());
		}
        
        /**
		* Go to payment	
        * @param integer $user_id User id
        * @param string  $method Payment method, e.g. Kassa/bank_card
        * @param integer $amount Amount, RUB
		*/
		function money_go(int $user_id, string $method, int $amount)
		{
			return $this->query('money/go', array(
                'user_id'  => $user_id,
                'method' => $method,
                'amount' => $amount,
            ));
		}
        
        /**
		* Switch On Domain
		* @param int $domain_id Domain Id
		*/
		function domain_on(int $domain_id = 0)
		{
			return $this->query('shop/domain/on', array(
				'id' => $domain_id
			), 'POST');
		}
        
        /**
		* Switch Off Domain
		* @param int $domain_id Domain Id
		*/
		function domain_off(int $domain_id = 0)
		{
			return $this->query('shop/domain/off', array(
				'id' => $domain_id
			), 'POST');
		}
        
        /**
		* Remove Domain
		* @param int $domain_id Domain Id
		*/
		function domain_remove(int $domain_id = 0)
		{
			return $this->query('shop/domain/remove', array(
				'id' => $domain_id
			), 'DELETE');
		}





	}

?>