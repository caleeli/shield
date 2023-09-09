import Vue from "vue";
import Vuex from "vuex";

function importAll(r) {
  let modules = {};
  for (const key in r) {
    if (r.hasOwnProperty(key) && key !== "./index.js") {
      const name = key.replace(/\.\/|\.js/g, "");
      modules[name] = r[key].default;
    }
  }
  return modules;
}

const modules = importAll(import.meta.globEager("./*.js"));

Vue.use(Vuex);

export default new Vuex.Store({
  modules
});
