<?php

class TicketsController extends BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    protected $layout = 'master';
    
    public function index($organisationID)
    {
    	$tickets = Ticket::where('organisationID', $organisationID)->get();
    	
    	$this->layout->content = View::make('tickets.index', compact('tickets'));
    }

    
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function showTicketsByOrg($id)
    {
    	$tickets = Ticket::where('organisationID', $id)->get();
    	
    	$this->layout->content = View::make('tickets.index', compact('tickets'));
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