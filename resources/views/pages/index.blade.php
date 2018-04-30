@extends('partials.layout')

@section('title')
Welcome
@endsection

@section('content')
<div id="app">
	<div class="container"> 
		<div class="card mb-3">
			<div class="card-body">
				<h4 class="card-title">@{{ title }}</h4>
				<div class="url-form">
					<form ref="form" action="{{url('url-action')}}" method="post">
					<input type="text" name="url" v-model="url.name" class="form-control mb-2" placeholder="http://www.abcd.com">
					<input type="hidden" name="_token" v-model="url.csrfToken">
					<button class="btn btn-primary" v-on:click="submitForm">
						Submit
					</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('extra_script')
<script type="text/javascript">
	window.Laravel = <?php echo json_encode(['csrfToken' => csrf_token()]); ?>
</script>
<script type="text/javascript" src="{{asset('js/pages/index.js')}}"></script>
@endsection