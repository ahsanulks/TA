<template>
  <div class="query-form col-md-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">{{ title }}</h4>
        <form>
          <div class="row">
            <div class="form-group col-md-12">
              <label for="sql">SQL Query</label>
              <textarea type="text" class="form-control mb-2" v-model="query" name="sql"></textarea>
              <input type="hidden" name="id" :value="id">
              <input class="btn btn-info btn-sm" value="SELECT ALL" type="button" @click="addValue('SELECT *')">
              <input class="btn btn-info btn-sm" value="SELECT" type="button" @click="addValue('SELECT')">
              <input class="btn btn-info btn-sm" value="FROM" type="button" @click="addValue('FROM')">
              <input class="btn btn-info btn-sm" value="WHERE" type="button" @click="addValue('WHERE')">
              <input class="btn btn-info btn-sm" value="ORDER" type="button" @click="addValue('ORDER BY')">
            </div>
            <div class="form-group col-md-6">
              <label for="tables">Tables</label>
              <select name="tables" class="form-control mb-2" v-model="tables" @click="addTable()" multiple>
                <option v-for="tableData in listColumns.tablesNames" :value="tableData">{{ tableData }}</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="sql">Columns</label>
              <select name="columns" class="form-control mb-2" v-model="columns" @click="addColumn()" multiple>
                <option v-for="columnData in listColumns.columnNames" :value="columnData">{{ columnData }}</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="sql">Increment TTL Type</label>
              <select v-model="type" name="type" class="form-control mb-2">
                <option value="linear">Linear</option>
                <option value="polynomial">Polynomial</option>
                <option value="exponential">Exponential</option>
              </select>
            </div>
          </div>
          <button class="btn btn-primary" @click="submitQuery">Query</button>
        </form>
      </div>
      <div class="card-footer text-muted">
        Last update {{ lastUpdate }}
      </div>
    </div>
    <urlqueryform v-if="isQuery" :urlQueryAccess="urlQuery"></urlqueryform>
		<datatable v-if="!isQuery"></datatable>
    <tablequery v-if="isQuery" :table="tableResponse" :column="columnResponse"></tablequery>
  </div>
</template>

<script>
// import { mapState } from 'vuex';

export default {
  data() {
    return {
      title: 'Query your table here!',
      query: '',
      id: window.Laravel.tableId,
      type: 'linear',
      isQuery: false,
      urlQuery: '',
      tableResponse: '',
      columnResponse: '',
      columns: [],
      tables: []
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
          vm.$set(vm, 'tableResponse', res.data.table);
          vm.$set(vm, 'columnResponse', res.data.columns);
        });
      });
    },

    addValue(value){
      this.query += value + ' ';
    },

    addColumn(){
      this.query += this.columns + ' ';
    },

    addTable(){
      this.query += this.tables + ' ';
    },

    onlyUnique(value, index, self){
      return self.indexOf(value) === index;
    }
  },
  
  computed: {
    lastUpdate() {
      return this.$store.state.lastUpdate;
    },

    listColumns() {
      var tables = this.$store.state.columns;
      var tablesNames = [];
      var columnNames = [];
      if(tables != false){
        for(var table in tables){
          tablesNames.push(tables[table].name);
          for(var list in tables[table].header){
            columnNames.push(tables[table].header[list]);
          }
        }
      }
      var data = {
        'tablesNames': tablesNames,
        'columnNames': columnNames.filter(this.onlyUnique)
      }
      return data;
    }
  }

}
</script>
