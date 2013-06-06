<?php

class AuthController extends BaseController {

		protected $layout = 'master';
		
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
    
    	$this->layout->content = View::make('login.index');    
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function postLogin()
    {
    	$user = array(
    		'username' => Input::get('username'),
    		'password' => Input::get('password')
    	);
    	
  		if (Auth::attempt($user))
			{
				Session::put('type',Auth::user()->type);
				if(Auth::user()->type == 'admin')
				{
					return Redirect::to('admin');
				} else {
					Session::put('organisationID', Auth::user()->organisationID);
					return Redirect::to('organisations');
				}
			}
			
			return Redirect::to('login');

    }
    
    public function getLogout()
    {

			Auth::logout();
			Session::flush();
	    return Redirect::to('/')->with('success', 'You have successfully logged out!');

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