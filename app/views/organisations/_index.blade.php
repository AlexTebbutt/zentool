@section('content')

<div id="report-summary" class="center">
	
	<h2>{{ $content->name }} Dashboard - Month Snapshot for <?php echo date('F'); ?></h2>

</div>

{{ $data }}

<div class="clear"></div>

@stop