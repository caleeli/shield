import "./bootstrap";
import Layout from "./components/Layout.vue";
import BButtons from "./components/BButtons.vue";
import SearchInput from "./components/SearchInput.vue";
import Upload from "./components/Upload.vue";
import UploadButton from "./components/UploadButton.vue";
import Pagination from "./components/Pagination.vue";
import router from "./router";
import store from "./store";
import Vue from "vue";
import ApiService from "./lib/ApiService";
import FormSelect from "./components/FormSelect.vue";
import Reporte from "./components/Reporte.vue";
import Workflow from "./lib/Workflow";
import { mapState } from "vuex";

Vue.prototype.$api = new ApiService;
Vue.component("b-buttons", BButtons);
Vue.component("search-input", SearchInput);
Vue.component("b-form-file", Upload);
Vue.component("b-button-upload", UploadButton);
Vue.component("pagination", Pagination);
Vue.component("b-form-select", FormSelect);
Vue.component("b-report", Reporte);

window.app = new Vue({
  router,
  store,
  el: "#app",
  components: {
    Layout,
  },
  template: "<Layout />",
});

Vue.prototype.$workflow = new Workflow(window.app);
Vue.prototype.dateFormat = function (datetimeString) {
  // convert a datetime string like "2023-07-15T01:35:54.000000Z" to a date string
  // like "15/07/2023"
  const datetime = new Date(datetimeString);
  const day = datetime.getDate();
  const month = datetime.getMonth() + 1;
  const year = datetime.getFullYear();

  // Padding single digits with leading zeros
  const formattedDay = String(day).padStart(2, '0');
  const formattedMonth = String(month).padStart(2, '0');

  return `${formattedDay}/${formattedMonth}/${year}`;
};
Vue.prototype.dateTimeFormat = function (datetimeString) {
  // convert a datetime string like "2023-07-15T01:35:54.000000Z" to a date string
  // like "15/07/2023 01:35:54"
  const datetime = new Date(datetimeString);
  const day = datetime.getDate();
  const month = datetime.getMonth() + 1;
  const year = datetime.getFullYear();
  const hours = datetime.getHours();
  const minutes = datetime.getMinutes();
  const seconds = datetime.getSeconds();

  // Padding single digits with leading zeros
  const formattedDay = String(day).padStart(2, '0');
  const formattedMonth = String(month).padStart(2, '0');
  const formattedHours = String(hours).padStart(2, '0');
  const formattedMinutes = String(minutes).padStart(2, '0');
  const formattedSeconds = String(seconds).padStart(2, '0');

  return `${formattedDay}/${formattedMonth}/${year} ${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
};
Vue.prototype.mixins = [
  {
    computed: {
      ...mapState({
        'user': (state) => state.user,
      }),
    }
  }
];

window.alert = function (message, variant = "info") {
  // first line is the title
  const title = variant === "danger" ? "Error" : "Información";
  window.app.$bvToast.toast(message, {
    title,
    variant,
    solid: true,
    autoHideDelay: 5000,
  });
}
