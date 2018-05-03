@extends('partials.layout')

@section('title')
Welcome
@endsection

@section('content')
<div id="app">	
	<navbar></navbar>
	<div class="container">
		<queryform></queryform>
	</div>
	<footerpage></footerpage>
</div>
@endsection

@section('extra_script')
<script>
	window.Laravel = { tableId: '{{ $id }}' };
</script>
@endsection