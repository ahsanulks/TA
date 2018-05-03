<template>
  <div class="query-form">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">{{ title }}</h4>
        <form>
          <input type="text" class="form-control mb-2" v-model="query" name="sql" placeholder="select * from table_0" size="100">
          <input type="hidden" name="id" :value="id">
          <select v-model="type" name="type" class="form-control mb-2">
            <option value="linear">Linear</option>
            <option value="polynomial">Polynomial</option>
            <option value="exponensial">Exponensial</option>
          </select>
          <button class="btn btn-primary" @click="submitQuery">Query</button>
        </form>
      </div>
    </div>
    <urlqueryform v-if="isQuery" :urlQueryAccess="urlQuery"></urlqueryform>
		<datatable v-if="!isQuery"></datatable>
  </div>
</template>

<script>
export default {
  data() {
    return {
      title: 'Query your table here!',
      query: '',
      id: window.Laravel.tableId,
      type: 'linear',
      isQuery: false,
      urlQuery: ''
    }
  },

  methods: {
    submitQuery(e) {
      const vm = this;
      e.preventDefault();
      axios.get('/parse-sql', {
        params: {
          id: this.id,
          sql: this.query,
          type: this.type
        }
      }).then(function (response) {
        vm.$set(vm, 'urlQuery', response.data);
        vm.$set(vm, 'isQuery', true);        
        axios.get(response.data).then(function (res){
          console.log(res);
        });
      });
    }
  }
}
</script>
