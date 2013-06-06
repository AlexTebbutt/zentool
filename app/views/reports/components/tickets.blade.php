<div class="month-heading">

	<h2>{{ $headings->count }} Ticket(s) closed in {{ $headings->monthTitle }} taking {{ $headings->totalTime }}<div class="open-button right"><a href="">+</a></div></h2>
	
</div>

@if($headings->count > 0)

	<table><thead><th align="center">Ticket #</th><th align="left">Closed On</th><th align="left">Task</th><th align="center">Time Taken (m)</th><th align="center">Status</th></thead>
	
	@foreach($tickets as $ticket)
		<tr>
		
			<td width="100" align="center" valign="top">
				
				<a href="{{ $ticket->url }}" target="_blank">{{ $ticket->id }}</a>
			
			</td>
			
			<td width="150" align="left" valign="top"><?php echo date('d-m-Y H:m',strtotime($ticket->updatedAt)); ?></td>
			
			<td width="500" align="left">{{ $ticket->subject }}</td>
			
			<td width="150" align="center" valign="top">{{ $ticket->time }}</td>
			
			<td width="100" align="center" valign="top">{{ $ticket->status }}</td >
		
		</tr>
	
	@endforeach
	
	</table>

@endif