const state = {
  user: {},
};

const mutations = {
  setUser(state, user) {
    state.user = user;
    // store user in session storage
    localStorage.setItem("user", JSON.stringify(user));
  },
  updateUser(state, user) {
    Object.assign(state.user, user);
    // store user in session storage
    localStorage.setItem("user", JSON.stringify(state.user));
  }
};

const actions = {
  updateUser({ commit }, user) {
    commit("updateUser", user);
  }
};

const getters = {
  getUser(state) {
    return state.user;
  }
};

// load user from local storage
const user = JSON.parse(localStorage.getItem("user"));
if (user) {
  state.user = user;
}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters,
};
