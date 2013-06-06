@section('content')

{{ Form::open(array('method' => 'POST', 'class' => 'login')) }}

<h1 class="form-title">Admin | Reporting</h1>

{{ Form::label('report-on', 'Report On') }}

{{ Form::select('report-on', array('tbo' => 'Time By Organisation'), 'tbo') }}

{{ Form::label('organisation-id', 'Organisation') }}

{{ Form::select('organisation-id', array('20635122' => 'APCIMS', '25096596' => 'Camerich', '20733176' => 'RJB Stone'), '20635122') }}

{{ Form::label('report-type', 'Organisation') }}

{{ Form::select('report-type', array('all' => 'Full - All tickets', 'date-range' => 'By Date Range'), 'all') }}

{{ Form::submit('Generate', array('class' => 'form-button')) }}

{{ Form::close() }}

@stop