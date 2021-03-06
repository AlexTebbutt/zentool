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


	public function __construct()
	{
		// Apply the  auth filter
		//$this->beforeFilter('admin-auth');

		//Get the Zendesk API account details and populate the variables
		$zendeskAccount = Option::find(1)->getOptions();
		$this->api_key = $zendeskAccount->apikey;
		$this->user = $zendeskAccount->user;
		$this->base = 'https://' . $zendeskAccount->subdomain . '.zendesk.com/api/v2';
		$this->suffix = $zendeskAccount->suffix;
		$this->subdomain = $zendeskAccount->subdomain;

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
		$decoded = json_decode($output);

		return is_null($decoded) ? $output : $decoded;

	}


	public function test()
	{
		var_dump($this->call('/tickets', '', 'GET'));
	}
	
	public function fetchUsers()
	{
		
		//GET users from zendesk API and add NEW users to users table or UPDATE existing users.
		$data = $this->call('/users',NULL,'GET');
		
		if (!$data) return FALSE;
		
		$log = NULL;
		$newUserCount = 0;
		$count = 0;			
				
		foreach ($data->users as $row)
		{

			$user = User::find($row->id);
	
			if (!$user && $row->active) 
			{
				
				$user = new User;
								 	
				$user->id = $row->id;				 
				if($row->organization_id != NULL) $user->organisationID = $row->organization_id;
				
				$user->fullname = $row->name;
				$user->type = $row->role;
				
				if($row->email != NULL) 
				{
					$user->email = $row->email;
					$user->username = $row->email;
				} else {
					$user->username = 'Username';
				}
	
				if($row->suspended == FALSE)
				{
					$user->active = TRUE;
	
				} else {
	
					$user->active = FALSE;
									
				}
				$password = (strtolower(str_replace(' ', '', $row->name))) . '01!!';
				$user->password = Hash::make($password);
				$newUserCount++;
	
				$user->zendeskUser = TRUE;
				$user->createdAt = $row->created_at;
				$user->save();
	
			}
		
			$count++;
			
		}

		return '<p><strong>Users Processed: </strong>' . $count . '</p><p><strong>Users Created: </strong> ' . $newUserCount . '</p>';		
		
	}
	
	public function fetchOrganisations($updateTickets = FALSE)
	{
		
		//GET organisations from zendesk API and add NEW users to users table 
		$data = $this->call('/organizations',NULL,'GET');
		
		if (!$data) return FALSE;
		
		$log = NULL;
		$count = 0;			
				
		foreach ($data->organizations as $row)
		{

			$organisation = Organisation::find($row->id);
			$log .= '<p><strong>Processing ' . $row->name . '</strong></p>';
			
			if (!$organisation) 
			{
				
				$organisation = new Organisation;
				
				$organisation->id = $row->id;
				$organisation->name = $row->name;
				$organisation->jsonUrl = $row->url;
				$organisation->url = 'https://' . $this->subdomain . '.zendesk.com/organizations/' . $organisation->id;;
				$organisation->active = TRUE;
				$organisation->createdAt = $row->created_at;
				$count++;
				$organisation->save();
				$log .= '<p>Created</p>';
			}	

			if ($updateTickets) $log .= $this->fetchTickets($row->id);

		}

		$log .= '<p><strong>Organisations Created: </strong>' . $count . '</p>';

		return $log;		
		
	}
	
	public function fetchTickets($organisationID = NULL)
	{
		
		$log = NULL;
		$url = NULL;
		$urlPrefix = NULL;
		$processed = 0;
		$created = 0;
		$page = 1;
		$more = TRUE;
		
		if($organisationID != NULL)
		{

			$urlPrefix = '/organizations/' . $organisationID . ''; 

		}
		
		while (($processed == 0 && $page == 1) || ($processed%100 == 0 && $processed != 0))
		{
		
			$url = $urlPrefix . '/tickets.json?page=' . $page;
			$data = $this->call($url,NULL,'GET');

			$log .= '<p><strong>Fetching tickets:</strong> ' . $url . '</p>';  
		
			
			foreach($data->tickets as $row)
			{

				$processed++;
				//Check to see if ticket already exists
				$ticket = Ticket::find($row->id);
				
				if(!$ticket) $ticket = new Ticket;
				
				//If the ticket exists and is closed, ignore
				if($row->status == NULL || $ticket->status != 'closed')
				{			
			
					$ticket->id = $row->id ;
					$row->organization_id == NULL ? $ticket->organisationID = '1' : $ticket->organisationID = $row->organization_id;
					$ticket->requesterID = $row->requester_id;
					$ticket->assigneeID = $row->assignee_id;
					$ticket->jsonUrl = $row->url;
					$ticket->url = 'https://' . $this->subdomain . '.zendesk.com/tickets/' . $row->id;
					$ticket->type = $row->type;
					$ticket->subject = $row->subject;
					$ticket->description = $row->description;
					$ticket->status = $row->status;
					$row->custom_fields[1]->value == NULL ? $ticket->time = 0 : $ticket->time = $row->custom_fields[1]->value;
					$ticket->createdAt = date('Y-m-d H:i:s', strtotime($row->created_at));
					$ticket->updatedAt = date('Y-m-d H:i:s', strtotime($row->updated_at));

					$ticket->save();	
					$created++;				
				}
				
			}

			$page++;
			//extend the prcoessing time to prevent a time-out.
			set_time_limit(360);
		
		}
		
		$log .= '<p><strong>Tickets Updated:</strong> ' . $created . '</p>';		
		$log .= '<p><strong>Total Tickets Processed:</strong> ' . $processed . '</p>';
		
		return $log;
	
	}
	
	
	public function updateTickets()
	{
	
		$count = 0;
		$log = NULL;
			
		//Get and process only open tickets
		$tickets = Ticket::where(function($query)
												{
												 	$query->where('status','!=','closed');
												 	$query->Where('status','!=','solved');
												})->get();
												
		foreach ($tickets as $ticket)
		{
			
			$data = $this->call('/tickets/' . $ticket->id,NULL,'GET');
			
			$ticket->type = $data->ticket->type;
			$ticket->status = $data->ticket->status;
			$data->ticket->custom_fields[1]->value == NULL ? $ticket->time = 0 : $ticket->time = $data->ticket->custom_fields[1]->value;
			$ticket->updatedAt = date('Y-m-d H:i:s', strtotime($data->ticket->updated_at));
			$ticket->save();		
			
			//echo $data->ticket->id . '<br /><br />';
			$log .= '<p><strong>Processing ticket:</strong> ' . $data->ticket->id . '</p>';
			$count++;
			
		}

		$log .= '<p><strong>Total Open Tickets Processed:</strong> ' . $count . '</p>';
		
		return $log;
		
		
	}

	public function listTicketFields() 
	{
		
		$data = $this->call('/ticket_fields',NULL,'GET');

		foreach($data->ticket_fields as $row)
		{
			
			var_dump($row);
			echo '<br />';
			
			
		}
		
		
	}
	
	

}