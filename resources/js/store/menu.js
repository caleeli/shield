import axios from "axios";

// get user from localstorage if present
const user = JSON.parse(localStorage.getItem("user"));
const rol_id = user ? user.rol_id : null;
const roles = {
  ADMIN: 1,
  GERENCIA: 2,
  SUPERVISOR: 3,
  AUDITOR: 4,
};

// menues disponibles:
const HelloWorld = {
  title: "Hola Mundo",
  icon: "fas fa-server",
  page: "/HelloWorld",
};


const state = {
  menuItems: [
    HelloWorld,
  ],
};

const mutations = {
  setMenuItems(state, menuItems) {
    state.menuItems = menuItems;
  },
};

const actions = {
  fetchMenuItems({ commit }) {
    return axios.get("/api/menu").then((response) => {
      commit("setMenuItems", response.data);
    });
  },
};

const getters = {
  getMenuItems: (state) => {
    return state.menuItems;
  },
};

export default {
  state,
  mutations,
  actions,
  getters,
};
