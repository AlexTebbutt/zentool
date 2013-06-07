@section('content')

{{ Form::open(array('method' => 'POST', 'class' => 'login', 'url' => 'admin/report')) }}

<h1 class="form-title">Admin Reporting</h1>

{{ Form::label('report-on', 'Report On') }}

{{ Form::select('report-on', array('tbo' => 'Time By Organisation'), 'tbo') }}

{{ Form::label('organisation-id', 'Organisation') }}

<select id="organisation-id" name="organisation-id">
@foreach($organisation as $org)
	
	<option 
	
	value="{{ $org->id }}" 
	
	@if($org->id == $report->orgID)
	
		{{ "selected" }}
	
	@endif
	
	>
	{{ $org->name }}
	
	</option>

@endforeach
</select>

{{ Form::label('report-type', 'Organisation') }}

{{ Form::select('report-type', array('all' => 'Full - All tickets', 'date-range' => 'By Date Range'), Input::get('report-type')) }}

{{ Form::label('date-from', 'Date From') }}

@if(Input::has('date-from'))

{{ Form::text('date-from', Input::get('date-from') ) }}

@else

{{ Form::text('date-from', '01-01-2013') }}

@endif

{{ Form::label('date-to', 'Date To') }}

@if(Input::has('date-to'))

{{ Form::text('date-to', Input::get('date-to') ) }}

@else

{{ Form::text('date-to', $report->dateTo ) }}

@endif

<div class="report-options">

@if($report->hideZero == 'hide')

{{ Form::checkbox('hide-zero', 'hide', true) }}

@else

{{ Form::checkbox('hide-zero', 'hide', false) }}

@endif


{{ Form::label('hide-zero', 'Hide months with no tickets', array('class' => 'block')) }}

</div>

<div class="report-options">

@if($report->showOpen == 'show')

{{ Form::checkbox('show-open', 'show', true) }}

@else

{{ Form::checkbox('show-open', 'show', false) }}

@endif


{{ Form::label('show-open', 'Show all open tickets', array('class' => 'block')) }}

</div>

{{ Form::submit('Generate', array('class' => 'form-button')) }}

{{ Form::close() }}

<div id="report-summary" class="center">
	
	<h2>Report Summary For {{ $report->orgName }} generated on <?php echo date('d F Y'); ?></h2>
	
	<h3>{{ $report->totalCount }} Ticket(s) closed in total for period {{ $report->dateFrom }} to {{ $report->dateTo }} taking {{ $report->totalTime }}</h3>

</div>

{{ $data }}

@stop