<template>
  <div>
    <div class="table-responsive" v-for="(table, index) in tables" :key="index">
        <table class="table table-striped">
          <caption style="caption-side: top; text-align: center">{{ table.name }}</caption>
          <thead class="thead-light">
            <th v-for="(header, index) in table.header" :key="index">
              {{ header }}
            </th>
          </thead>
          <tbody>
            <tr v-for="(column, index) in columns[table._id]" :key="index">
              <td v-for="(body, index) in column.body" :key="index">{{ body }}</td>
            </tr>
          </tbody>
        </table>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      id: window.Laravel.tableId,
      tables: null,
      columns: null,
      lastUpdate: ''
    }
  },

  methods: {
    getData(){
      const vm = this;
      axios.get('/url/' + this.id +'/tables').then(function(response) {
        vm.$store.commit('lastUpdated', response.data.tables[0].updated_at);
        vm.$set(vm, 'tables', response.data.tables);
        vm.$set(vm, 'lastUpdate', response.data.tables[0].updated_at);
        vm.$set(vm, 'columns', response.data.columns);
      });
    }
  },

  created() {
    this.getData();
  }
}
</script>

