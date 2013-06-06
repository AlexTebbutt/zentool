<?php

	include_once('zentoolFunctions.php');

	

	if (!updateOrganisations())
	{
		echo 'Organisations update failed<br />';
	} else {
		echo 'Organisations update completed<br />';		
	}

	//echo 'clear';
	updateTicketsByOrganisation();
	//updateTickets('24521057');

?>