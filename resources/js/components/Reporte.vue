<!--
    Componente vue que dibuja un `<b-table>` cuyos datos se cargan desde un endpoint `/report?sql=sql`.
-->
<template>
  <div>
    <!-- Button to download the Excel report -->
    <a :href="excel" class="btn btn-success" target="_blank">
      <i class="fa fa-file-excel-o"></i> Descargar Excel
    </a>
    <b-table ref="table" :items="tabla.items" :fields="fields"></b-table>
    <!-- print error message -->
    <div v-if="tabla.error" class="alert alert-danger">
      {{ tabla.error }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    fields: {
      type: Array,
      required: true,
    },
    sql: {
      type: String,
      required: true,
    },
    parameters: {
      type: Object,
      default: () => ({}),
    },
  },
  data() {
    return {
      tabla: this.$api.fetchDataFrom(
        "/api/report?sql=" + encodeURIComponent(this.sql) + "&parameters=" + encodeURIComponent(JSON.stringify(this.parameters))
      ),
    };
  },
  computed: {
    excel() {
      return "/api/report/excel?sql=" + encodeURIComponent(this.sql) + "&parameters=" + encodeURIComponent(JSON.stringify(this.parameters));
    },
  },
  methods: {
    refresh() {
      return this.$refs.table.refresh();
    },
    downloadExcel() {
      // Assuming that your API will send the Excel file as a downloadable response
      window.location.href =
        "/api/export-excel?sql=" + encodeURIComponent(this.sql) + "&parameters=" + encodeURIComponent(JSON.stringify(this.parameters));
    },
  },
};
</script>
