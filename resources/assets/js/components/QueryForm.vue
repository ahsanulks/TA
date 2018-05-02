<template>
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
</template>

<script>
export default {
  data() {
    return {
      query: '',
      id: window.Laravel.tableId,
      type: 'linear'
    }
  },

  methods: {
    submitQuery(e) {
      e.preventDefault();
      axios.get('/parse-sql', {
        params: {
          id: this.id,
          sql: this.query,
          type: this.type
        }
      }).then(function (response) {
        axios.get(response.data).then(function (res){
          console.log(res);
        });
      });
    }
  }
}
</script>
