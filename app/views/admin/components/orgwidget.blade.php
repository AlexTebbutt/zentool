<div class="widget narrow organisation-widget">

	<a href="organisation/{{ $content->id }}/report">	
		
		<h3>{{ $content->title }}</h3>
		
		<p class="count open-bgd open-copy">{{ $content->openTickets }}<span>open</span></p>
		
		<p class="count closed-bgd closed-copy">{{ $content->closedTickets }}<span>closed</span></p>	
		
		<p class="center time-spent"><span>Time Spent</span>{{ $content->totalTime }}</p>
		
	</a>

</div>

