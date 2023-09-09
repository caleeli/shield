const headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
};
export default {
  namespaced: true,
  state: {
    instanceId: null,
    tokenId: null,
    home: null,
    tokens: [],
    data: {},
    page: null,
  },
  mutations: {
    setState(state, { newState, options, user }) {
      state.instanceId = newState.instance;
      state.tokens = newState.tokens;
      state.tokenId = null;
      state.data = newState.data;
      state.home = newState.home;
      const myFirstToken = newState.tokens.find((token) => {
        return token.status === 'ACTIVE' && token.user === user.id || token.user instanceof Array && token.user.includes(user.id);
      });
      if (!myFirstToken) {
        // go to default page of the process
        if (state.home) {
          state.page = state.home;
          const query = {};
          if (newState.record) {
            query.record = String(newState.record);
          } else if (newState.instance) {
            query.instance = String(newState.instance);
          }
          if (window.app.$route.path !== state.home || JSON.stringify(query) !== JSON.stringify(window.app.$route.query)) {
            window.app.$router.replace({
              path: state.home,
              query,
            });
            if (newState.tokens[0]) {
              const performer = newState.tokens[0].user;
              if (performer instanceof Array) {
                alert(`El proyecto se encuentra en ${newState.tokens[0].name}`, 'success');
              } else {
                fetch('/api/data/usuario/' + newState.tokens[0].user, { headers })
                  .then(response => response.json())
                  .then(user => {
                    alert(`El proyecto se encuentra en ${newState.tokens[0].name} por ${user.nombres_apellidos}.`, 'success');
                  });
              }
            }
          }
        }
        return;
      }
      state.tokenId = myFirstToken.id;
      const page = myFirstToken.implementation;
      if (!options || options.route) {
        state.page = page;
        // get absolute path of current page or relative path (last part of the path)
        const currentPage = page.substring(0, 1) === "/" ? window.app.$route.path : window.app.$route.path.split("/").pop();
        if (currentPage !== page || window.app.$route.query.instance !== newState.instance || window.app.$route.query.token !== myFirstToken.id) {
          let path = page;
          if (page.substring(0, 1) !== "/") {
            const [, ...currentRoute] = window.location.hash.split("/");
            const parentPath = currentRoute.slice(0, -1).join("/");
            path = "/" + parentPath + "/" + page;
          }
          window.app.$router.replace({
            path,
            query: {
              instance: newState.instance,
              token: myFirstToken.id
            }
          }).catch(err => {
            if (err.message && err.message.indexOf("Navigation cancelled") > -1) {
              // ignore the error
            } else {
              throw err;
            }
          });
        }
      }
    },
    setData(state, { data }) {
      Object.assign(state.data, data);
    }
  },
  actions: {
    initializeData({ commit }, data) {
      commit('setData', { data });
    },
    async routeInstance({ commit, rootState }, { instance, from, to }) {
      console.log("route instance", from, to);
      const user = rootState.user.user;
      const params = new URLSearchParams({ from, to }).toString();
      const response = await fetch(`/api/route/${instance}?${params}`);
      const newState = await response.json();
      if (!newState) {
        alert("No se pudo abrir el proceso");
      }
      commit('setState', { newState, user });
    },
    async openStartInstance({ commit, rootState }, { instanceId, processId, startEvent, data }) {
      const user = rootState.user.user;
      const url = `/api/open-start/${processId}/${instanceId}/${startEvent}`;
      let response;
      if (data) {
        response = await fetch(url, {
          method: 'POST',
          headers,
          body: JSON.stringify(data)
        });
      } else {
        response = await fetch(url, {
          method: 'GET',
          headers,
        });
      }
      const newState = await response.json();
      if (!newState) {
        alert("No se pudo abrir el proceso");
      }
      commit('setState', { newState, user });
    },
    async openInstance({ commit, rootState }, instanceId) {
      const user = rootState.user.user;
      const response = await fetch(`/api/open/${instanceId}`);
      const newState = await response.json();
      if (!newState) {
        alert("No se pudo abrir el proceso");
      }
      commit('setState', { newState, user });
    },
    async openRecord({ commit, rootState }, recordId) {
      console.log("entro", recordId);
      const user = rootState.user.user;
      const response = await fetch(`/api/open-record/${recordId}`);
      const newState = await response.json();
      if (!newState) {
        alert("No se pudo abrir el proceso");
      }
      commit('setState', { newState, user, options: { route: false } });
    },
    async callProcess({ commit, rootState }, { processId, data }) {
      const user = rootState.user.user;
      let response;
      if (data) {
        response = await fetch(`/api/call/${processId}`, {
          method: 'POST',
          headers,
          body: JSON.stringify(data)
        });
      } else {
        response = await fetch(`/api/call/${processId}`);
      }
      const newState = await response.json();
      if (!newState) {
        alert("No se pudo iniciar el proceso");
      }
      commit('setState', { newState, user });
    },
    async startProcess({ commit, rootState }, { processId, startEvent, data }) {
      const user = rootState.user.user;
      let response;
      if (data) {
        response = await fetch(`/api/start/${processId}/${startEvent}`, {
          method: 'POST',
          headers,
          body: JSON.stringify(data)
        });
      } else {
        response = await fetch(`/api/start/${processId}/${startEvent}`);
      }
      const newState = await response.json();
      if (!newState) {
        alert("No se pudo iniciar el proceso");
      }
      commit('setState', { newState, user });
    },
    async workflowMessage({ state, commit, rootState }, { messageRef, tokenId }) {
      const user = rootState.user.user;
      const response = await fetch(`/api/message/${state.instanceId}/${tokenId || state.tokenId}/${messageRef}`);
      const newState = await response.json();
      if (!newState) {
        alert("No se pudo enviar el mensaje");
      }
      commit('setState', { newState, user });
    },
    async completeTask({ state, commit, rootState }, { instanceId, tokenId, data }) {
      const user = rootState.user.user;
      // post complete
      const url = `/api/complete/${instanceId || state.instanceId}/${tokenId || state.tokenId}`;
      const response = await fetch(url, {
        method: 'POST',
        headers,
        body: JSON.stringify(data)
      });
      const newState = await response.json();
      if (!newState) {
        alert("No se pudo completar la tarea");
        return;
      } else if (newState.exception) {
        alert(newState.message, "danger");
        return;
      } else if (newState.error) {
        alert(newState.error, "danger");
      }
      commit('setState', { newState, user });
    },
    async updateTask({ state, commit, rootState }, { instanceId, tokenId, data }) {
      const user = rootState.user.user;
      // post complete
      const url = `/api/update/${instanceId || state.instanceId}/${tokenId || state.tokenId}`;
      const response = await fetch(url, {
        method: 'POST',
        headers,
        body: JSON.stringify(data)
      });
      const newState = await response.json();
      if (!newState) {
        alert("No se pudo actualizar la tarea");
        return;
      } else if (newState.exception) {
        alert(newState.message, "danger");
        return;
      } else if (newState.error) {
        alert(newState.error, "danger");
      }
      commit('setState', { newState, user });
    },
  },
  getters: {
    instanceId: (state) => state.instanceId,
    tokenId: (state) => state.tokenId,
    data: (state) => state.data,
  },
};
