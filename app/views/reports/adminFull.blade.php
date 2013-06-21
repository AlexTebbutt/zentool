@section('content')

{{ Form::open(array('method' => 'POST', 'class' => 'general-form', 'url' => 'admin/report')) }}

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

{{ Form::label('report-type', 'Report Type') }}

{{ Form::select('report-type', array('all' => 'Full - All tickets', 'date-range' => 'By Date Range'), Input::get('report-type')) }}

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

{{ Form::text('date-to', $report->dateTo ) }}

@endif

<div class="report-options">

@if($report->hideZero == 'hide')

{{ Form::checkbox('hide-zero', 'hide', true) }}

@else

{{ Form::checkbox('hide-zero', 'hide', false) }}

@endif


{{ Form::label('hide-zero', 'Hide months with no tickets', array('class' => 'inline-block')) }}

</div>

<div class="report-options">

@if($report->showOpen == 'show')

{{ Form::checkbox('show-open', 'show', true) }}

@else

{{ Form::checkbox('show-open', 'show', false) }}

@endif


{{ Form::label('show-open', 'Show all open tickets', array('class' => 'inline-block')) }}

</div>

{{ Form::submit('Generate', array('class' => 'form-button')) }}

{{ Form::close() }}

<div id="report-summary" class="center">
	
	<h2 class="left">{{ $report->orgName }} Report Built On: <?php echo date('d F Y'); ?><span class="block">For Period {{ $report->dateFrom }} to {{ $report->dateTo }}</span></h2>

	<h3 class="right open-bgd rounded-end">Total time:<span class="block">{{ $report->totalTime }}</span></h3>
	
	<h3 class="right closed-bgd">Tickets Closed: {{ $report->closedCount }}<span class="block">Taking: {{ $report->closedTime }}</span></h3>

	<h3 class="right open-bgd">Tickets Open: {{ $report->openCount }}<span class="block">Taking: {{ $report->openTime }}</span></h3>

</div>

{{ $data }}

@stop