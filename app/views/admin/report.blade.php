@section('content')

{{ Form::open(array('method' => 'POST', 'class' => 'login')) }}

<h1 class="form-title">Admin | Reporting - old</h1>

{{ Form::label('report-on', 'Report On') }}

{{ Form::select('report-on', array('tbo' => 'Time By Organisation'), 'tbo') }}

{{ Form::label('organisation-id', 'Organisation') }}

<select id="organisation-id" name="organisation-id">
@foreach($organisation as $org)
	
	<option 
	
	value="{{ $org->id }}" 
	
	@if($org->id == $organisation->organisationID)
	
		{{ "selected" }}
	
	@endif
	
	>
	{{ $org->name }}
	
	</option>

@endforeach
</select>

{{ Form::label('report-type', 'Organisation') }}

{{ Form::select('report-type', array('all' => 'Full - All tickets', 'date-range' => 'By Date Range'), 'all') }}

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

{{ Form::text('date-to', $organisation->dateTo ) }}

@endif

{{ Form::submit('Generate', array('class' => 'form-button')) }}

{{ Form::close() }}


@if($tickets != NULL)

<h2>All Tickets</h2>

<table><thead><th align="center">Ticket #</th><th align="left">Closed On</th><th align="left">Task</th><th align="center">Time Taken (m)</th><th align="left">Status</th></thead>

@foreach($tickets as $ticket)
	<tr>
	
		<td width="150" align="center" valign="top">
			
			<a href="{{ $ticket->url }}" target="_blank">{{ $ticket->id }}</a>
		
		</td>
		
		<td width="150" align="left" valign="top">{{ $ticket->updatedAt }}</td>
		
		<td width="400" align="left">{{ $ticket->subject }}</td>
		
		<td width="150" align="center" valign="top">{{ $ticket->time }}</td>
		
		<td width="150" align="left" valign="top">{{ $ticket->status }}</td >
	
	</tr>

@endforeach

</table>

@endif

@stop