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
    	//Pretty sure this isn't being used. Remove during tidy up.
    	$organisation = Organisation::all();
    	$organisation->dateTo = date('d-m-Y');
			$tickets = NULL;
    	$this->layout->content = View::make('admin.report', compact('organisation','tickets'));    
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
		
		public function postReport()
		{

			//Set up any vars
			$report = new stdClass();
			$report->orgID = Input::get('organisation-id');
			$report->dateTo = date('d-m-Y');
			$result = Organisation::where('id', $report->orgID)->first(array('name'));
			$report->orgName = $result->name;
			
			//Get organisations for drop down
			$organisation = Organisation::all();

			//Set up report date ranges
			if (Input::get('report-type') == 'date-range')
			{

				$reportDateFrom = date('Y-m-d', strtotime(Input::get('date-from')));
				$reportDateTo = date('Y-m-d', strtotime(Input::get('date-to')));			

			} else {

				//It's a full report so set date range to first / last ticket date
				$result = Ticket::where('organisationID', $report->orgID)->orderBy('updatedAt')->first(array('updatedAt'));
				$reportDateFrom = date('Y-m-d', strtotime($result->updatedAt));
				$result = Ticket::where('organisationID', $report->orgID)->orderBy('updatedAt','desc')->first(array('updatedAt'));					
				$reportDateTo = date('Y-m-d', strtotime($result->updatedAt));				
				
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
			$data = NULL;
			$headings = new stdClass();
			
			
			for ($dateRangeFrom = $dateFrom; $dateRangeFrom <= $reportDateTo; )
			{
			
				//Get ticket count for date range
				$headings->count = Ticket::where('organisationID', $report->orgID)->where('updatedAt','>=',$dateRangeFrom)->where('updatedAt','<=',$dateRangeTo . ' 23:59:59')->count();

				if((Input::get('hide-zero') != 'hide' && $headings->count == 0) || $headings->count > 0)
				{
				
					if ($headings->count > 0)
					{
						//Get month time total
						$headings->totalTime = $this->formatTime(Ticket::where('organisationID', $report->orgID)->where('updatedAt','>=',$dateRangeFrom)->where('updatedAt','<=',$dateRangeTo . ' 23:59:59')->sum('time'));
						//Retrieve all tickets for date range
						$tickets = Ticket::where('organisationID', $report->orgID)->where('updatedAt','>=',$dateRangeFrom)->where('updatedAt','<=',$dateRangeTo . ' 23:59:59')->orderBy('updatedAt', 'asc')->get();			
					} else {
						$headings->totalTime = '0 Hours 0 Minutes';
					}
					
					$headings->monthTitle = date('F', strtotime($dateRangeFrom));				
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

/*
		  $queries = DB::getQueryLog();
			$last_query = end($queries);
	
			var_dump($last_query);
*/

					
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