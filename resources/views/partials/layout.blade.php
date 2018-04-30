<!DOCTYPE html>
<html>
<head>
	<title>
		@yield('title')
	</title>
	@yield('extra_css')
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
@yield('content')

<script type="text/javascript" src="{{asset('js/vue.js')}}"></script>
@yield('extra_script')
</body>
</html>