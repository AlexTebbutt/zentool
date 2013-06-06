<?php
define("ZDAPIKEY", "ra75ePE6HPIFv5NFRQeCdcWew6F4BJ626y535kFQ");
define("ZDUSER", "alex.tebbutt@images.co.uk");
define("ZDURL", "https://imagesandco.zendesk.com/api/v2");

/* Note: do not put a trailing slash at the end of v2 */

function curlWrap($url, $json, $action)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
	curl_setopt($ch, CURLOPT_URL, ZDURL.$url);
	curl_setopt($ch, CURLOPT_USERPWD, ZDUSER."/token:".ZDAPIKEY);
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
			break;
		case "DELETE":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			break;
		default:
			break;
	}

	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$output = curl_exec($ch);
	curl_close($ch);
	$decoded = json_decode($output);
	return $decoded;
}


function updateOrganisations()
{

	//Get info from Zendesk
	$data = curlWrap('/organizations.json', null, 'GET');
	$OK = TRUE;

		
	//Connect to the DB
	try 
	{
		$DB = new PDO('mysql:host=127.0.0.1;dbname=reporting', 'reporting_user', 'report01!!'); 

		//Get the max ID and only insert new organisations

/*
		$sql = 'SELECT MAX(id) FROM organisation';
		$result = $DB->query($sql);
		echo 'MaxIS: ' . $result;
*/
		
		$sth = $DB->prepare('SELECT MAX(id) FROM organisations');
		$sth->execute();

		$result = $sth->fetch(PDO::FETCH_ASSOC);
		$maxID = $result['MAX(id)'];
		//echo $maxID;		
		//Loop through returned data
		foreach($data->organizations as $organisation)
		{
			if ($organisation->id > $maxID)
			{
				$organisationUrl = 'https://imagesandco.zendesk.com/organizations/' . $organisation->id;
				echo 'From ZD: ' . $organisation->id . ', From DB: ' . $maxID . '<br />';
				$sql = 'INSERT INTO organisations
								(id,name,jsonURL,url,accountType,rollingTime,timeOnAccount,active,createdAt)
								VALUES 
								("' . $organisation->id . '","' . $organisation->name . '","' . $organisation->url . '","' . $organisationUrl . '","ROLLING","0","0","1","' . date('Y-m-d H:i:s') . '")';
				$DB->query($sql);

			}
		}

		$DB = null;	

	}
	
	catch(PDOException $e) 
	{  
    echo $e->getMessage();  
  }
	
  return $OK;
}

function updateTicketsByOrganisation()
{

	try 
	{
		$DB = new PDO('mysql:host=127.0.0.1;dbname=reporting', 'reporting_user', 'report01!!'); 
		$sql = 'SELECT id FROM organisations';

		foreach($DB->query($sql) as $organisation)
		{
			updateTickets($organisation['id']);
		}
		
	}

	catch(PDOException $e) 
	{  
    echo $e->getMessage();  
  }
}


function updateTickets($organisationID = null)
{

	try 
	{
		$DB = new PDO('mysql:host=127.0.0.1;dbname=reporting', 'reporting_user', 'report01!!'); 
		
		$page=1;
		$i = 0;		
	
		while (($i == 0 && $page == 1) || ($i%100 == 0 && $i != 0))
		{
			$data = curlWrap('/organizations/' . $organisationID . '/tickets.json?page=' . $page, null, 'GET');
			$OK = TRUE;
			
			//print_r($data);
	
			foreach ($data->tickets as $ticket)
			{
				$i++;
				if($ticket->status == 'closed')
				{
				
					//Check to see if ticker has been added already
					$sql = 'SELECT COUNT(*) FROM tickets WHERE id = "' . $ticket->id . '"';
					$result = $DB->query($sql);
					$count = $result->fetch(PDO::FETCH_NUM);
	
	
					if ($count[0] == 0)
					{
						$ticketUrl = 'https://imagesandco.zendesk.com/tickets/' . $ticket->id;
						
						$sql = 'INSERT INTO 
										tickets (id,organisationID,requesterID,assigneeID,jsonURL,url,subject,status,time,createdAt,updatedAt) 
										VALUES ("' . $ticket->id . '","' . $ticket->organization_id . '","' . $ticket->requester_id . '","'  . $ticket->assignee_id . '","' . $ticket->url . '","' . $ticketUrl . '","' . $ticket->subject . '","' . $ticket->status . '","' . $ticket->custom_fields[1]->value . '","' . date('Y-m-d H:i:s', strtotime($ticket->created_at)) . '","' . date('Y-m-d H:i:s', strtotime($ticket->updated_at)) . '")';		
						echo $sql . '<br />';
						$DB->query($sql);
					}
					
					$totalTime = $totalTime + $ticket->custom_fields[1]->value;
	
				}
			}
			$page++;
			set_time_limit(360);
		}
	}

	catch(PDOException $e) 
	{  
    echo $e->getMessage();  
  }

  echo 'Tickets Processed: ' . $i;
	//Update time on account if needed
	if($totalTime > 0) updateAccount($organisationID, $totalTime);

	return $i;

}

function updateAccount($organisationID, $totalTime)
{

	try 
	{
		$DB = new PDO('mysql:host=127.0.0.1;dbname=reporting', 'reporting_user', 'report01!!'); 
	
		$sth = $DB->prepare('SELECT time_on_account FROM organisation WHERE id = "' . $organisationID . '"');
		$sth->execute();
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		$timeOnAccount = $result['time_on_account'];
			
		$adjustedTime = $timeOnAccount - $totalTime;
		echo 'TOA: ' . $timeOnAccount . ', AT: ' . $adjustedTime . ', TT: ' . $totalTime . '<br ><br />';		

		//Update account history
		$sql = 'INSERT INTO history
						(organisationID,updated_on,time_adjustment,change_note,time_on_account)
						VALUES
						("' . $organisationID . '","' . date('Y-m-d H:i:s') . '","' . (0 - $totalTime) . '","Adjusting time on account by ' . convertTime($totalTime). ' (' . $totalTime . ' minutes) for newly closed tickets. Update TOA: ' . $adjustedTime . ' minutes","' . $adjustedTime . '")';		
		$DB->query($sql);

		echo $sql . '<br />';		
		//Update time on account for the organisation
		$sql = 'UPDATE organisations 
						SET time_on_account = "' . $adjustedTime . '"
						WHERE id = "' . $organisationID . '"';
		
		$DB->query($sql);
		echo $sql . '<br />';
	}

	catch(PDOException $e) 
	{  
    echo $e->getMessage();  
  }

}



function convertTime($time)
{

	$neg = '';

	if ($time < 0) 
	{
		$neg = '-';
		$time = $time * -1;
	}

	return $neg . floor($time/60) . ' Hours ' . $neg . $time%60 . ' Minutes';
	
}



?>