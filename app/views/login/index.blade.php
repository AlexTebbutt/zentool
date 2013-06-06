@section('content')

{{ Form::open(array('method' => 'POST', 'class' => 'login')) }}

<h1 class="form-title">Login Page</h1>

{{ Form::label('username', 'Username:') }}

{{ Form::text('username') }}

{{ Form::label('password','Password:') }}

{{ Form::password('password') }}

{{ Form::submit('Login', array('class' => 'form-button')) }}

{{ Form::close() }}

@stop