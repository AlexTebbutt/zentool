<div class="organisation-widget">

	<a href="organisation/{{ $content->id }}/report">	
		
		<h3>{{ $content->name }}</h3>
		
		<p class="count blue">{{ $content->openTickets }}<span>open</span></p>
		
		<p class="count green">{{ $content->closedTickets }}<span>closed</span></p>	
		
		<p class="center time-spent"><span>Time Spent</span>{{ $content->totalTime }}</p>
		
	</a>

</div>

