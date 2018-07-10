<template>
  <div>
    <div class="table-query">
      <div class="table-responsive">
        <table class="table table-striped" :id="id">
          <caption style="caption-side: top; text-align: center">{{ tables.name }}</caption>
            <thead class="thead-light">
              <th v-for="(header, index) in tables.header" :key="index">
                {{ header }}
              </th>
            </thead>
            <tbody>
              <tr style="display: none;">
                <td v-for="(header, index) in tables.header" :key="index">{{ header }}</td>
              </tr>
              <tr v-for="(rows, index) in columns" :key="index">
                <td v-for="(row, indexRow) in rows" :key="indexRow">{{ row }}</td>
              </tr>
            </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      id: 'data-' + this.getDate(),
    }
  },
  props: ['table', 'column'],
  computed: {
    tables: {
      get() {
        return this.table;
      }
    },
    columns: {
      get() {
        return this.column;
      }
    }
  },

  methods: {
    downloadAsCsv(){
      $("table").tableExport().remove();
      $("table").tableExport({
          headings: true,
          footers: false,
          formats: ["xls", "csv"],
          fileName: "id",
          bootstrap: false,
          position: "bottom",
          ignoreRows: null,
          ignoreCols: null,
          ignoreCSS: ".tableexport-ignore",
          emptyCSS: ".tableexport-empty",
          trimWhitespace: false
      });
    },
    getDate(){
      var today = new Date();
      var dd = today.getDate();
      var mm = today.getMonth()+1; //January is 0!

      var yyyy = today.getFullYear();
      if(dd<10){
          dd='0'+dd;
      } 
      if(mm<10){
          mm='0'+mm;
      } 
      return today = dd+'-'+mm+'-'+yyyy;
    }
  },

  updated: function () {
      this.downloadAsCsv();
  }
}
</script>

