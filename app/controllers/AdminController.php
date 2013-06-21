<?php

class AdminController extends ZendeskController {

		protected $layout = 'master';
		
	/**
	 * Initializer.
	 *
	 * @return void
	 */
		public function __construct()
		{
			// Apply the  auth filter
			parent::__construct();
			$this->beforeFilter('admin-auth');
			
		}
		
		/**
     * Index page: This is the main Dashboard.
     * Widget layouts are stored in views > admin > components
     *
     * @return Built view
     */
   
    public function index()
    {
			//Set up vars
			$organisation = Organisation::where('active','1')->get();
			$data = NULL;
			$content = new stdClass();
			
			//Generate grand totals widgets
			$content->title = "Total Open Tickets";
			$content->count = Ticket::where(function($query)
															{
																$query->where('status', '!=', 'closed');
																$query->where('status', '!=', 'solved');
															})
															->count();
			$data .= View::make('admin.widgets.totalopenwidget', compact('content'));

			$content->title = "Total Closed Tickets";
			$content->count = Ticket::where(function($query)
												{
													$query->where('status', '=', 'closed');
													$query->orWhere('status', '=', 'solved');
												})
												->where(function($query)
												{
													$query->where('updatedAt','>=',date('Y-m-01 00:00:00'));
													$query->where('updatedAt','<=',date('Y-m-d 23:59:59'));
												})
												->count();

			$data .= View::make('admin.widgets.totalclosedwidget', compact('content'));
			
			$content->title = "Total Time Spent";
			$content->time = $this->formatTime(Ticket::where('updatedAt','>=',date('Y-m-01 00:00:00'))->where('updatedAt','<=',date('Y-m-d 23:59:59'))->sum('time'),'short');
			$data .= View::make('admin.widgets.totaltimewidget', compact('content'));
			
			//Loop through organistaions
			foreach($organisation as $org)
			{

				$result = Organisation::where('id', $org->id)->first(array('name'));
				$content->title = $result->name;
				$content->id = $org->id;
				
				//Get closed tickets
				$content->closedTickets = Ticket::where('organisationID', $org->id)
																					->where(function($query)
																					{
																						$query->where('status', '=', 'closed');
																						$query->orWhere('status', '=', 'solved');
																					})
																					->where(function($query)
																					{
																						$query->where('updatedAt','>=',date('Y-m-01 00:00:00'));
																						$query->where('updatedAt','<=',date('Y-m-d 23:59:59'));
																					})
																					->count();

				//Get open tickets
				$content->openTickets = Ticket::where('organisationID', $org->id)
																			->where(function($query)
																			{
																				$query->where('status', '!=', 'closed');
																				$query->where('status', '!=', 'solved');
																			})
																			->count();
																			
				//Get time used this month
/* 				$content->totalTime = $this->formatTime(Ticket::where('organisationID', $org->id)->where('updatedAt','>=',date('Y-m-01 00:00:00'))->where('updatedAt','<=',date('Y-m-d 23:59:59'))->sum('time')); */

				//Closed ticket time
				$content->totalTime = Ticket::where('organisationID', $org->id)
														->where('updatedAt','>=',date('Y-m-01 00:00:00'))
														->where('updatedAt','<=',date('Y-m-d 23:59:59'))
														->where(function($query)
															{
																$query->where('status','=','closed');
																$query->orWhere('status','=','solved');
															})
														->sum('time');
				
				//Open time tickets
				$content->totalTime = $content->totalTime + Ticket::where('organisationID', $org->id)
																					->where(function($query)
																						{
																							$query->where('status','=','open');
																							$query->orWhere('status','=','pending');
																						})
																					->sum('time');
				
				$content->totalTime = $this->formatTime($content->totalTime,'short');																	
				$data .= View::make('admin.widgets.orgwidget', compact('content'));
				
			}
		
			//Generate widget / view
    	$this->layout->content = View::make('admin.index',array('data' => $data));    

    }


		public function optionsTest($key)
		{
			
/*
			$option = Option::where('name',$key);
			$options = $option->getOptions();
	
			
			
			echo $options->apikey . '<br />';
*/


		}
		

		/**
     * Initial report view: Generate the form first shown to the user
     *
     * @return Built view
     */
		public function getReport()
		{
			//Set up any vars
			$report = new stdClass();
			$report->orgID = Input::get('organisation-id');
			$report->dateTo = date('d-m-Y');
			
			//Get organisations for drop down
			$organisation = Organisation::all();
			
			$data = NULL;
		
	  	$this->layout->content = View::make('reports.adminShow', array('organisation' => $organisation, 'report' => $report, 'data' => $data));		
			
		}

		/**
     * Processed report view: Take the user inputs and generate the report
     *
     * @return Built view
     */		
		public function postReport($id = NULL)
		{

			//Set up any vars
			$report = new stdClass();
			
			if($id != NULL)
			{
				$report->orgID = $id;
				$report->showOpen = 'show';
				$report->hideZero = 'hide';
				$report->type = 'date-range';
			} else {
				$report->orgID = Input::get('organisation-id');
				$report->showOpen = Input::get('show-open');
				$report->hideZero = Input::get('hide-zero');
				$report->type = Input::get('report-type');
			}
			
			$report->dateTo = date('d-m-Y');
			$result = Organisation::where('id', $report->orgID)->first(array('name'));
			$report->orgName = $result->name;
			$report->openTime = 0;
			$report->openCount = 0;

			$data = NULL;
			$headings = new stdClass();
			$headings->totalTime = 0;
			
			
			//Get organisations for drop down
			$organisation = Organisation::all();

			//Get open (status != 'closed') tickets
			if($report->showOpen == 'show')
			{

				$headings->count = Ticket::where('organisationID', $report->orgID)
																->where(function($query)
																{
																 	$query->where('status','!=','closed');
																 	$query->Where('status','!=','solved');
																})
																->count();

				$report->openCount = $headings->count;

				$tickets = Ticket::where('organisationID', $report->orgID)
													->where(function($query)
													{
													 	$query->where('status','!=','closed');
													 	$query->Where('status','!=','solved');
													})
													->orderBy('status')
													->get();					
													
				if ($headings->count > 0)
				{
					$headings->totalTime = Ticket::where('organisationID', $report->orgID)
																													->where(function($query)
																													{
																													 	$query->where('status','!=','closed');
																													 	$query->Where('status','!=','solved');
																													})
																													->sum('time');
				}
				
				$report->openTime = $headings->totalTime;
				$headings->totalTime = $this->formatTime($headings->totalTime);
				$headings->classTitle = 'open-summary';				
				$headings->updateTitle = 'Last Update On';
				$headings->reportTitle = $headings->count . ' Ticket(s) open ' . ' currently taking ' . $headings->totalTime;
				$data .= View::make('reports.components.tickets', compact('tickets','headings'));
			}

			//Get all closed tickets within the date range and generate monthly views
			//Set up report date ranges
			if ($report->type == 'date-range' && $id != NULL)
			{
				$reportDateFrom = date('Y-m-01');
				$reportDateTo = date('Y-m-d');
			}
			elseif ($report->type == 'date-range')
			{

				$reportDateFrom = date('Y-m-d', strtotime(Input::get('date-from')));
				$reportDateTo = date('Y-m-d', strtotime(Input::get('date-to')));			

			} else {

				//It's a full report so set date range to first / last ticket date
				$result = Ticket::where('organisationID', $report->orgID)->orderBy('updatedAt')->first(array('updatedAt'));

				if ($result)
				{
					
					$reportDateFrom = date('Y-m-d', strtotime($result->updatedAt));
					$result = Ticket::where('organisationID', $report->orgID)->orderBy('updatedAt','desc')->first(array('updatedAt'));					
					$reportDateTo = date('Y-m-d', strtotime($result->updatedAt));				

				} else {
					
					$reportDateFrom = date('Y-01-01');
					$reportDateTo = date('Y-m-d');
					
				}
				
			}

			$report->dateFrom = date('d-m-Y', strtotime($reportDateFrom));
			$report->dateTo = date('d-m-Y', strtotime($reportDateTo));
			
			$dateFrom = $reportDateFrom;
			$dateTo = date('Y-m-t', strtotime($dateFrom));						


			if ($reportDateTo < $dateTo) 
			{
				$dateRangeTo = $reportDateTo;
			} else {
				$dateRangeTo = date('Y-m-t', strtotime($dateFrom));
			}

			//Loop through tickets month by month, build the ticket section of the report
			for ($dateRangeFrom = $dateFrom; $dateRangeFrom <= $reportDateTo; )
			{
			
				//Get ticket count for date range
				$headings->count = Ticket::where('organisationID', $report->orgID)
																	->where('updatedAt','>=',$dateRangeFrom)
																	->where('updatedAt','<=',$dateRangeTo . ' 23:59:59')
																	->where(function($query)
																	{
																	 	$query->where('status','=','closed');
																	 	$query->orWhere('status','=','solved');
																	})
																	->count();
				
				if(($report->hideZero != 'hide' && $headings->count == 0) ||  $headings->count > 0)
				{
				
					if ($headings->count > 0)
					{
						
						//Get month time total
						$headings->totalTime = $this->formatTime(Ticket::where('organisationID', $report->orgID)
																														->where('updatedAt','>=',$dateRangeFrom)
																														->where('updatedAt','<=',$dateRangeTo . ' 23:59:59')
																														->where(function($query)
																														{
																														 	$query->where('status','=','closed');
																														 	$query->orWhere('status','=','solved');
																														})
																														->sum('time'));

						//Retrieve all tickets for date range
						$tickets = Ticket::where('organisationID', $report->orgID)
															->where('updatedAt','>=',$dateRangeFrom)
															->where('updatedAt','<=',$dateRangeTo . ' 23:59:59')
															->where(function($query)
															{
															 	$query->where('status','=','closed');
															 	$query->orWhere('status','=','solved');
															})
															->orderBy('updatedAt', 'asc')
															->get();	
																	

					} else {

						$headings->totalTime = '0 Hours 0 Minutes';

					}

					$headings->classTitle = 'closed-summary';
					$headings->updateTitle = 'Closed On';
					$headings->reportTitle = $headings->count . ' Ticket(s) closed in ' . date('F', strtotime($dateRangeFrom)) . ' taking ' . $headings->totalTime;		
					$data .= View::make('reports.components.tickets', compact('tickets','headings'));
					
				}

				$dateRangeFrom = date('Y-m-01', strtotime(date('Y-m-d',strtotime($dateRangeFrom . "+1 month"))));
				$dateRangeTo = date('Y-m-t', strtotime($dateRangeFrom));
				
				if ($dateRangeTo > $reportDateTo) $dateRangeTo = $reportDateTo;
			
			}
		
			//Get total closed time
			$report->closedTime = Ticket::where('organisationID', $report->orgID)
																										->where('updatedAt','>=',$reportDateFrom)
																										->where('updatedAt','<=',$reportDateTo . ' 23:59:59')
																										->where(function($query)
																										{
																											
																											$query->where('status','=','closed');
																											$query->orWhere('status','=','solved');
																											
																										})
																										->sum('time');
			
			
			//Total Time Spent
			$report->totalTime = $this->formatTime(($report->openTime + $report->closedTime));
			$report->openTime = $this->formatTime($report->openTime,'short');
			$report->closedTime = $this->formatTime($report->closedTime,'short');
			
			//Get total ticket count
			$report->closedCount = Ticket::where('organisationID', $report->orgID)->where('updatedAt','>=',$reportDateFrom)->where('updatedAt','<=',$reportDateTo . ' 23:59:59')
																		->where(function($query)
																		{
																			$query->where('status','=','closed');
																			$query->orWhere('status','=','solved');
																		})
																		->count();
			
			//Generate view
			$this->layout->content = View::make('reports.adminFull', array('organisation' => $organisation, 'report' => $report, 'data' => $data)); 	
					
		}
		

		public function getUpdate() 
		{
			
			$this->layout->content = View::make('admin.showUpdate');
			
		}
		

		public function postUpdate() 
		{
		
			$data = NULL;
			 
			//Process Organisations if required
			if (Input::has('update-organisations') == 'update-organisations')
			{
			
				$data .= $this->fetchOrganisations();
			 				 	
			}
			
			//Process Users if required
			if (Input::has('update-users') == 'update-users')
			{
			
			 	$data .= $this->fetchUsers();
			 	
			}
						
			//Process Tickets if required
			if (Input::has('update-tickets') == 'update-tickets')
			{
	
			 	$data .= $this->fetchTickets();
			 	
			}			

			
			$this->layout->content = View::make('admin.processUpdate', array('data'=>$data));
			
		}
		
		
		public function getOpenTickets()
		{
		
			$data = NULL;
			$headings = new stdClass();
			$headings->totalTime = 0;
			
			//Get all open tickets ordered by organistion and then date
			$headings->count = Ticket::where(function($query)
																{
																 	$query->where('status','!=','closed');
																 	$query->Where('status','!=','solved');
																})
																->count();
																
			if ($headings->count > 0)
			{
				$headings->totalTime = Ticket::where(function($query)
																				{
																				 	$query->where('status','!=','closed');
																				 	$query->Where('status','!=','solved');
																				})
																				->sum('time');
			}	
			
			$headings->totalTime = $this->formatTime($headings->totalTime);
					
			$tickets = Ticket::where(function($query)
												{
												 	$query->where('status','!=','closed');
												 	$query->Where('status','!=','solved');
												})
												->orderBy('organisationID')
												->orderBy('updatedAt')
												->get();				
			
			$headings->reportTitle = $headings->count . ' Ticket(s) open ' . ' currently taking ' . $headings->totalTime;		
				$headings->classTitle = 'open-summary';				
				$headings->updateTitle = 'Last Update On';
			
			$data = View::make('reports.components.tickets', compact('tickets','headings'));
			
			//Generate view
			$this->layout->content = View::make('reports.adminOpen', array('data' => $data));
			
		}

		public function getClosedTickets()
		{
		
			$data = NULL;
			$headings = new stdClass();
			$headings->totalTime = 0;
			
			//Get all open tickets ordered by organistion and then date
			$headings->count = Ticket::where(function($query)
																{
																 	$query->where('status','=','closed');
																 	$query->orWhere('status','=','solved');
																})
																->count();
																
			if ($headings->count > 0)
			{
				$headings->totalTime = Ticket::where(function($query)
																				{
																				 	$query->where('status','!=','closed');
																				 	$query->Where('status','!=','solved');
																				})
																				->sum('time');
			}	
			
			$headings->totalTime = $this->formatTime($headings->totalTime);
					
			$tickets = Ticket::where(function($query)
												{
												 	$query->where('status','!=','closed');
												 	$query->Where('status','!=','solved');
												})
												->orderBy('organisationID')
												->orderBy('updatedAt')
												->get();				
			
			$headings->reportTitle = $headings->count . ' Ticket(s) closed ' . ' taking ' . $headings->totalTime;		
				$headings->classTitle = 'closed-summary';				
				$headings->updateTitle = 'Closed On';
			
			$data = View::make('reports.components.tickets', compact('tickets','headings'));
			
			//Generate view
			$this->layout->content = View::make('reports.adminClosed', array('data' => $data));
			
		}		
		

		public function getManageUsers()
		{
			
			$users = User::all();
			$organisation = Organisation::all();
			
			$this->layout->content = View::make('admin.manage.users',compact('users','organisation'));
			
			
		}


		private function formatTime($time, $format = 'long')
		{

			$neg = '';
			
			if ($time < 0) 
			{
				
				$neg = '-';
				$time = $time * -1;
				
			}
		
			if ($format == 'long' && $time > 0)
			{

				return $neg . floor($time/60) . ' Hours ' . $neg . $time%60 . ' Minutes';

			} elseif ($format == 'long' && $time == 0) {
				
				return '0 Hours 0 Minutes';	
	
			}	elseif ($format == 'short' && $time > 0)	{

				return $neg . floor($time/60) . 'h ' . $neg . $time%60 . 'm';

			} elseif ($format == 'short' && $time == 0) {
				
				return '0h 0m';
								
			}
			
		}


		private function checkQuery()
		{

			$queries = DB::getQueryLog();
			$last_query = end($queries);
			var_dump($last_query);
			
		}




    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

}