@extends('partials.layout')

@section('title')
Welcome
@endsection

@section('extra_css')
<script>window.Laravel['formAction'] = '{{ url('url-action') }}';</script>
@endsection

@section('content')
<div id="app">	
	<navbar></navbar>
	<div class="container"> 
		<urlform></urlform>
	</div>
	<footerpage></footerpage>
</div>
@endsection
