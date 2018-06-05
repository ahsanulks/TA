<template>
  <div class="card mb-3 mt-3 col-md-12">
    <div class="card-body">
      <h5 class="card-title">{{ title }}</h5>
      <input type="text" class="form-control mb-2" :value="urlQueryAccess" readonly>
    </div>
  </div>
</template>

<script>
export default {
  props: ['urlQueryAccess'],
  data() {
    return {
      title: 'Get your data as json here!'
    }
  }
}
</script>
