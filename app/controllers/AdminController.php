<?php

class AdminController extends BaseController {

		protected $layout = 'master';
		
	/**
	 * Initializer.
	 *
	 * @return void
	 */
		public function __construct()
		{
			// Apply the  auth filter
			$this->beforeFilter('admin-auth');
		}
		/**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
			//Set up vars
			$organisation = Organisation::where('active','1')->get();
			$data = NULL;
			$content = new stdClass();
			
			//Loop through organistaions
			foreach($organisation as $org)
			{

				$result = Organisation::where('id', $org->id)->first(array('name'));
				$content->name = $result->name;
				$content->id = $org->id;
				
				//Get closed tickets
				$content->closedTickets = Ticket::where('organisationID', $org->id)->where('updatedAt','>=',date('Y-m-01 00:00:00'))->where('updatedAt','<=',date('Y-m-d 23:59:59'))->where('status','closed')->count();
/*
						  $queries = DB::getQueryLog();
			$last_query = end($queries);
	
			var_dump($last_query);
*/
				//Get open tickets
				$content->openTickets = Ticket::where('organisationID', $org->id)->where('status','!=','closed')->count();
				//Get time used this month
				$content->totalTime = $this->formatTime(Ticket::where('organisationID', $org->id)->where('updatedAt','>=',date('Y-m-01 00:00:00'))->where('updatedAt','<=',date('Y-m-d 23:59:59'))->sum('time'));
				
				$data .= View::make('admin.components.orgwidget', compact('content'));
				
			}
		
			//Generate widget / view
    	$this->layout->content = View::make('admin.index',array('data' => $data));    

    }

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
		
		public function postReport($id = NULL)
		{

			//Set up any vars
			$report = new stdClass();
			
			if($id != NULL)
			{
				$report->orgID = $id;
				$report->showOpen = 'show';
				$report->hideZero = 'hide';
			} else {
				$report->orgID = Input::get('organisation-id');
				$report->showOpen = Input::get('show-open');
				$report->hideZero = Input::get('hide-zero');
			}
			$report->dateTo = date('d-m-Y');
			$result = Organisation::where('id', $report->orgID)->first(array('name'));
			$report->orgName = $result->name;
			$data = NULL;
			$headings = new stdClass();
			
			//Get organisations for drop down
			$organisation = Organisation::all();

			//Get open (status != 'closed') tickets
/* 			if (Input::get('show-open') || $showOpen) */
			if($report->showOpen == 'show')
			{

				$headings->count = Ticket::where('organisationID', $report->orgID)->where('status','!=','closed')->count();
				$tickets = Ticket::where('organisationID', $report->orgID)->where('status','!=','closed')->orderBy('status')->get();					
		
				if ($headings->count > 0)
				{
					$headings->totalTime = $this->formatTime(Ticket::where('organisationID', $report->orgID)->where('status','!=','closed')->sum('time'));
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
			if (Input::get('report-type') == 'date-range')
			{

				$reportDateFrom = date('Y-m-d', strtotime(Input::get('date-from')));
				$reportDateTo = date('Y-m-d', strtotime(Input::get('date-to')));			

			} else {

				//It's a full report so set date range to first / last ticket date
				$result = Ticket::where('organisationID', $report->orgID)->orderBy('updatedAt')->first(array('updatedAt'));
/*
		  $queries = DB::getQueryLog();
			$last_query = end($queries);
	
			var_dump($last_query);
*/

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
				$headings->count = Ticket::where('organisationID', $report->orgID)->where('updatedAt','>=',$dateRangeFrom)->where('updatedAt','<=',$dateRangeTo . ' 23:59:59')->where('status','closed')->count();
				
/* 				if((Input::get('hide-zero') != 'hide' && $headings->count == 0) ||  $headings->count > 0) */
				if(($report->hideZero != 'hide' && $headings->count == 0) ||  $headings->count > 0)
				{
				
					if ($headings->count > 0)
					{
						//Get month time total
						$headings->totalTime = $this->formatTime(Ticket::where('organisationID', $report->orgID)->where('updatedAt','>=',$dateRangeFrom)->where('updatedAt','<=',$dateRangeTo . ' 23:59:59')->where('status','closed')->sum('time'));
						//Retrieve all tickets for date range
						$tickets = Ticket::where('organisationID', $report->orgID)->where('updatedAt','>=',$dateRangeFrom)->where('updatedAt','<=',$dateRangeTo . ' 23:59:59')->orderBy('updatedAt', 'asc')->where('status','closed')->get();			
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
			$report->totalTime = $this->formatTime(Ticket::where('organisationID', $report->orgID)->where('updatedAt','>=',$reportDateFrom)->where('updatedAt','<=',$reportDateTo . ' 23:59:59')->sum('time'));
			
			//Get total ticket count
			$report->totalCount = Ticket::where('organisationID', $report->orgID)->where('updatedAt','>=',$reportDateFrom)->where('updatedAt','<=',$reportDateTo . ' 23:59:59')->count();
			
			//Generate view
			$this->layout->content = View::make('reports.adminFull', array('organisation' => $organisation, 'report' => $report, 'data' => $data)); 	
					
		}
		
		private function formatTime($time)
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
		
			return $neg . floor($time/60) . ' Hours ' . $neg . $time%60 . ' Minutes';
			
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