new Vue({
	el: '#app',
	data: {
		title: 'GET YOUR URL HERE!!',
		url: {
			name: '',
			csrfToken: window.Laravel.csrfToken
		}
	},
	methods:{
		submitForm(){
			this.$refs.form.submit();
		}
	}
});