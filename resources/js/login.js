import "./bootstrap";
import Vue from "vue";

import Login from "./components/Login.vue";

new Vue({
  el: '#app',
  components: {
    Login,
  },
  template: '<Login />',  
});
