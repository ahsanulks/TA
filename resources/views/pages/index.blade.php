@extends('partials.layout')

@section('title')
Welcome
@endsection

@section('content')
<form method="post" action="{{url('url-action')}}">
	{{csrf_field()}}
	<input type="text" name="url" placeholder="http://abc.com" list='suggestion'>
	<datalist id="suggestion">
		<option value="black"></option>
		<option value="blue"></option>
	</datalist>
	<button type="submit">Kirim</button>
</form>
@endsection