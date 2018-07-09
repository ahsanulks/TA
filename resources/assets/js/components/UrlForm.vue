<template>
  <div class="card mb-3 mt-3 col-md-12">
    <div class="card-body">
      <h4 class="card-title">{{ title }}</h4>
      <form ref="form" :action="url.formAction" method="post">
        	<input type="text" name="url" v-model="url.name" class="form-control mb-2" placeholder="http://www.abcd.com" required>
					<input type="hidden" name="_token" v-model="url.csrfToken">
					<button class="btn btn-primary" @click="submitForm">
						Submit
					</button>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      title: 'GET YOUR URL HERE!!',
      url: {
        name: '',
        csrfToken: window.Laravel.csrfToken,
        formAction: window.Laravel.formAction
      }
    }
  },
  
  methods: {
    submitForm() {
			this.$refs.form.submit();
    }
  }
}
</script>
