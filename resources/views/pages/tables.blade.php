@extends('partials.layout')

@section('title')
Welcome
@endsection

@section('content')
<form method="get" action="{{url('parse-sql')}}">
	<input type="text" name="sql" placeholder="select * from" size="100">
	<input type="hidden" name="id" value="{{isset($id) ? $id : ''}}">
	<button type="submit">Sql</button>
</form>

@foreach($tables as $table)
<table border="1">
	<caption>{{$table->name}}</caption>
	<thead>
		@foreach($table->header as $header)
			<th>{{$header}}</th>
		@endforeach
	</thead>
	<tbody>
		@php($columns = $column[$table->id])
		@foreach($columns as $row)
			<tr>
				<td>{!! implode('</td><td>',$row['body']) !!}</td>
			</tr>
		@endforeach
	</tbody>
</table>
@endforeach
@endsection