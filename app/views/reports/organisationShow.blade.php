@section('content')

{{ Form::open(array('method' => 'POST', 'class' => 'general-form')) }}

<h1 class="form-title">Reporting for {{ $organisation->name }}</h1>

{{ Form::label('report-type', 'Report Type') }}

{{ Form::select('report-type', array('date-range' => 'By Date Range', 'all' => 'Full - All tickets'), Input::get('report-type')) }}

{{ Form::label('date-from', 'Date From') }}

@if(Input::has('date-from'))

{{ Form::text('date-from', Input::get('date-from') ) }}

@else

{{ Form::text('date-from', date('01-m-Y')) }}

@endif



{{ Form::label('date-to', 'Date To') }}

@if(Input::has('date-to'))

{{ Form::text('date-to', Input::get('date-to') ) }}

@else

{{ Form::text('date-to', $organisation->dateTo ) }}

@endif

<div class="report-options">

{{ Form::checkbox('hide-zero', 'hide', true) }}

{{ Form::label('hide-zero', 'Hide months with no tickets', array('class' => 'inline-block')) }}

</div>

<div class="report-options">

{{ Form::checkbox('show-open', 'show', true) }}

{{ Form::label('show-open', 'Show all open tickets', array('class' => 'inline-block')) }}

</div>

{{ Form::submit('Generate', array('class' => 'form-button')) }}

{{ Form::close() }}

@stop