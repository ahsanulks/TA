@extends('partials.layout')

@section('title')
Welcome
@endsection

@section('content')
<div id="app">	
	<navbar></navbar>
	<div class="container">
		<queryform></queryform>
		@foreach($tables as $table)
		<div class="table-responsive">
			<table class="table table-striped">
				<caption style="caption-side: top; text-align: center">{{$table->name}}</caption>
				<thead class="thead-light">
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
		</div>
		@endforeach
	</div>
	<footerpage></footerpage>
</div>
@endsection

@section('extra_script')
<script>window.Laravel.tableId = '{{ $id }}'</script>
<script>window.Laravel.formAction = '{{ url('parse-sql') }}'</script>
@endsection