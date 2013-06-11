<?php

class OrganisationsController extends BaseController {

	protected $layout = 'master';
	
	/**
	 * Initializer.
	 *
	 * @return void
	 */
	public function __construct()
	{
		// Apply the  auth filter
		$this->beforeFilter('auth');
	}


  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {

		//$this->layout->content = View::make('organisations.index');
		//Set up and vars
		$data = NULL;
		$content = new stdClass();
  	$organisationID = Session::get('organisationID');
  	$result = Organisation::where('id', $organisationID)->first(array('name'));
		$content->name = $result->name;
 
		//Generate grand total widgets.
		//Time spent this month
		$content->title = "Time Spent in " . date('F');
		
		$content->time = Ticket::where('organisationID', $organisationID)
										->where('updatedAt','>=',date('Y-m-01 00:00:00'))
										->where('updatedAt','<=',date('Y-m-d 23:59:59'))
										->where(function($query)
											{
												$query->where('status','=','closed');
												$query->orWhere('status','=','solved');
											})
										->sum('time');
				
		//Open time tickets
		$content->time = $content->time + Ticket::where('organisationID', $organisationID)
																			->where(function($query)
																				{
																					$query->where('status','=','open');
																					$query->orWhere('status','=','pending');
																				})
																			->sum('time');
		
		$content->time = $this->formatTime($content->time,'short');	
		$data .= View::make('organisations.components.totaltimewidget', compact('content'));
		
		$content->title = "Tickets Closed in " . date('F');
		$content->count = Ticket::where('organisationID', $organisationID)
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

		$data .= View::make('organisations.components.totalclosedwidget', compact('content'));

		$content->title = "Total Open Tickets";
		$content->count = Ticket::where('organisationID', $organisationID)
															->where(function($query)
															{
																$query->where('status', '!=', 'closed');
																$query->where('status', '!=', 'solved');
															})
															->count();
		$data .= View::make('organisations.components.totalopenwidget', compact('content'));

		$content->title = "Total Time Spent";
		$content->time = $this->formatTime(Ticket::where('organisationID', $organisationID)->sum('time'),'short');
		$data .= View::make('organisations.components.totaltimewidget', compact('content'));
		
		$content->title = "Total Tickets Closed";
		$content->count = Ticket::where('organisationID', $organisationID)
											->where(function($query)
											{
												$query->where('status', '=', 'closed');
												$query->orWhere('status', '=', 'solved');
											})
											->count();

		$data .= View::make('organisations.components.totalclosedwidget', compact('content'));
		
		//Generate view
  	$this->layout->content = View::make('organisations.index', array('content' => $content, 'data' => $data));     

  }


	public function getReport()
	{
  	$organisationID = Session::get('organisationID');
  	$organisation = Organisation::find($organisationID);
  	$organisation->dateTo = date('d-m-Y');

		$tickets = NULL;
	
  	$this->layout->content = View::make('reports.organisationShow', compact('organisation'));  		
		
	}
	
	public function postReport()
	{
		$report = new stdClass();
		$headings = new stdClass();		
		$data = NULL;
		
  	$report->orgID = Session::get('organisationID');
  	$organisation = Organisation::find($report->orgID);

		$report->showOpen = Input::get('show-open');
		$report->hideZero = Input::get('hide-zero');
		$report->type = Input::get('report-type');
		$report->dateTo = date('d-m-Y');
		$result = Organisation::where('id', $report->orgID)->first(array('name'));
		$report->orgName = $result->name;

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
				$headings->totalTime = $this->formatTime(Ticket::where('organisationID', $report->orgID)
																												->where(function($query)
																												{
																												 	$query->where('status','!=','closed');
																												 	$query->Where('status','!=','solved');
																												})
																												->sum('time'));
			} else {
				$headings->totalTime = '0 Hours 0 Minutes';
			}
			
			$headings->classTitle = 'open-summary';				
			$headings->updateTitle = 'Last Update On';
			$headings->reportTitle = $headings->count . ' Ticket(s) open ' . ' currently taking ' . $headings->totalTime;
			$data .= View::make('reports.components.tickets', compact('tickets','headings'));
		
		}

		//Get all closed tickets within the date range and generate monthly views
		//Set up report date ranges
		if ($report->type == 'date-range')
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
	
		//Get total time
		$report->totalTime = $this->formatTime(Ticket::where('organisationID', $report->orgID)
																									->where('updatedAt','>=',$reportDateFrom)
																									->where('updatedAt','<=',$reportDateTo . ' 23:59:59')
																									->where(function($query)
																									{
																										
																										$query->where('status','=','closed');
																										$query->orWhere('status','=','solved');
																										
																									})
																									->sum('time'));
		
		
		//Get total ticket count
		$report->totalCount = Ticket::where('organisationID', $report->orgID)->where('updatedAt','>=',$reportDateFrom)->where('updatedAt','<=',$reportDateTo . ' 23:59:59')
																	->where(function($query)
																	{
																		$query->where('status','=','closed');
																		$query->orWhere('status','=','solved');
																	})
																	->count();
		
		//Generate view
		$this->layout->content = View::make('reports.organisationFull', array('organisation' => $organisation, 'report' => $report, 'data' => $data));
			
	}
	
	private function formatTime($time, $format = 'long')
	{

		$neg = '';

		if ($time == 0)
		{

			return '0 Hours 0 Minutes';

		}	elseif ($time < 0) 
		{
			
			$neg = '-';
			$time = $time * -1;
			
		}
	
		if ($format == 'long')
		{
			return $neg . floor($time/60) . ' Hours ' . $neg . $time%60 . ' Minutes';
		} 
		elseif ($format == 'short')
		{
			return $neg . floor($time/60) . ' H ' . $neg . $time%60 . ' M';
		}
		
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

?>