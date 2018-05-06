<template>
  <div class="table-query">
    <div class="table-responsive">
      <table class="table table-striped">
        <caption style="caption-side: top; text-align: center">{{ tables.name }}</caption>
          <thead class="thead-light">
            <th v-for="(header, index) in tables.header" :key="index">
              {{ header }}
            </th>
          </thead>
          <tbody>
            <tr v-for="(rows, index) in columns" :key="index">
              <td v-for="(row, indexRow) in rows" :key="indexRow">{{ row }}</td>
            </tr>
          </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
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
}
</script>

