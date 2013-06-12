<?php

class ZendeskController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	private $apiKey;
	private $user;
	private $subdomain;

	public function __construct()
	{
		// Apply the  auth filter
		$this->beforeFilter('admin-auth');

		//Get the Zendesk API account details and populate the variables
		$zendeskAccount = Zendesk::find(1);
		$this->api_key = $zendeskAccount->apikey;
		$this->user = $zendeskAccount->user;
		$this->base = 'https://' . $zendeskAccount->subdomain . '.zendesk.com/api/v2';
		$this->suffix = $zendeskAccount->suffix;

	}


	/**
	 * Perform an API call.
	 * @param string $url='/tickets' Endpoint URL. Will automatically add the suffix you set if necessary (both '/tickets.json' and '/tickets' are valid)
	 * @param array $json=array() An associative array of parameters
	 * @param string $action Action to perform POST/GET/PUT
	 * @return mixed Automatically decodes JSON responses. If the response is not JSON, the response is returned as is
	 */
	public function call($url, $json, $action)
	{
		if (substr_count($url, $this->suffix) == 0)
		{
			$url .= '.json';
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_URL, $this->base.$url);
		curl_setopt($ch, CURLOPT_USERPWD, $this->user."/token:".$this->api_key);
		switch($action){
			case "POST":
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
				break;
			case "GET":
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
				break;
			case "PUT":
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			default:
				break;
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json', 'Accept: application/json'));
		curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		$output = curl_exec($ch);
		curl_close($ch);
		$decoded = json_decode($output, true);

		return is_null($decoded) ? $output : $decoded;
	}


	public function test()
	{
		var_dump($this->call('/tickets', '', 'GET'));
	}
	
	public function fetchUsers()
	{
		
		//GET users from zendesk API and add NEW users to users table

		$data = new stdClass();
		$data = $this->call('/users','','GET');
		
		if (!$data) return FALSE;
		
		$user = new User;
		$log = NULL;
		$count = 0;

		echo $data->users;
		
/*
		foreach ($data->users as $row)
		{
			
			$user->organisationID = $row->organisation_id;
			$user->username = $row->name;
			$user->fullname = $row->name;
			$user->type = $row->role;
			$user->email = $row->email;

			if($row->active == TRUE && $row->suspended == FALSE)
			{
				$user->active = TRUE;

			} else {

				$user->active = FALSE;
								
			}
			
			$password = (str_replace(' ', '', $row->name)) . '01!!';
			$user->password = $password;
			$user->createdAt = $row->created_at;
			
			$user->save();
			$count++;
			
		}
*/

		return $count . ' Users created';		
		
	}
	

}