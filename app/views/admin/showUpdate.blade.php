@section('content')

<div id="page-title" class="center">
	
	<h2>Process Updates - Select the items you want to update.</h2>

</div>

{{ Form::open(array('method' => 'POST', 'class' => 'general-form')) }}

<div class="report-options">

{{ Form::checkbox('update-tickets', 'update-tickets', true) }}

{{ Form::label('update-tickets', 'Update tickets', array('class' => 'inline-block')) }}

</div>

<div class="report-options">

{{ Form::checkbox('update-organisations', 'update-organisations', false) }}

{{ Form::label('update-organisations', 'Update organisations', array('class' => 'inline-block')) }}

</div>

<div class="report-options">

{{ Form::checkbox('update-users', 'update-users', false) }}

{{ Form::label('update-users', 'Update users', array('class' => 'inline-block')) }}

</div>


{{ Form::submit('Process', array('class' => 'form-button')) }}

{{ Form::close() }}

@stop