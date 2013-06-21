@section('content')

<div id="page-title" class="center">
	
	<h2>Manage Users</h2>

</div>

{{ Form::open(array('method' => 'POST', 'class' => 'manage-form')) }}


<div class="section-title">

	<h3>Add a new user</h3>
	
</div>

<table class="muser">

	<thead>

		<th>{{ Form::label('new-user-fullname', 'Fullname') }}</th>
		
		<th>{{ Form::label('new-user-username', 'Username') }}</th>
		
		<th>{{ Form::label('new-user-email', 'Email') }}</th>
		
		<th>{{ Form::label('new-user-type', 'User Type') }}</th>
		
		<th>{{ Form::label('new-user-password', 'Password') }}</th>
	
	</thead>

	<tbody>
		<tr>
		
			<td>{{ Form::text('new-user-fullname', Input::get('new-user-fullname') ) }}</td>
			
			<td>{{ Form::text('new-user-username', Input::get('new-user-username') ) }}</td>
			
			<td>{{ Form::text('new-user-email', Input::get('new-user-email') ) }}</td>
			
			<td>{{ Form::select('new-user-type', array('admin' => 'Administrator', 'agent' => 'Agent', 'end-user' => 'User'), 'end-user') }}</td>
			
			<td>{{ Form::text('new-user-password', Input::get('new-user-password') ) }}</td>
			
			<td>{{ Form::submit('+', array('class' => 'form-button')) }}</td>
		
		</tr>

	</tbody>
	
</table>




<div class="section-title">

	<h3>Amend existing users</h3>
	
</div>

<table class="muser">

	<thead>

		<th>{{ Form::label('new-user-fullname', 'Fullname') }}</th>
		
		<th>{{ Form::label('new-user-username', 'Username') }}</th>
		
		<th>{{ Form::label('new-user-email', 'Email') }}</th>
		
		<th>{{ Form::label('new-user-type', 'User Type') }}</th>
		
		<th>{{ Form::label('new-user-password', 'Password') }}</th>
	
	</thead>

	<tbody>

	@foreach ($users as $user)
		<tr>			
	
			<td>{{ Form::text('amend-user-fullname', $user->fullname) }}</td>

			<td>{{ Form::text('amend-user-username', $user->username) }}</td>

			<td>{{ Form::text('amend-user-email', $user->email) }}</td>

			<td>{{ Form::select('new-user-type', array('admin' => 'Administrator', 'agent' => 'Agent', 'end-user' => 'User'), $user->type) }}</td>

			<td>{{ Form::text('amend-user-password') }}</td>

			<td>{{ Form::submit('Update', array('class' => 'form-button')) }}</td>
				
		</tr>			
	@endforeach

	</tbody>

</table>


{{ Form::submit('Update Users', array('class' => 'form-button')) }}

{{ Form::close() }}

@stop