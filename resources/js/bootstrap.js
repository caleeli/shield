/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from "axios";
import Vue from "vue";
import BootstrapVue from "bootstrap-vue";
import VueRouter from "vue-router";
import helpers from "./mixins/helpers";

import "bootstrap/dist/css/bootstrap.css";
import "bootstrap-vue/dist/bootstrap-vue.css";
import "@fortawesome/fontawesome-free/css/all.css";

Vue.use(VueRouter);
Vue.use(BootstrapVue, {
  BFormDatepicker: {
    locale: 'es-BO',
    'labelNoDateSelected': 'Seleccionar fecha',
    'labelHelp': 'Use las teclas de cursor para navegar las fechas del calendario',
  },
  BTable: {
    striped: true,
    bordered: true,
    outlined: false,
  },
});
Vue.mixin(helpers);
window.axios = axios;

// Intl.DateTimeFormat().resolvedOptions().locale = 'es-BO';
// navigator.language = "es";

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
//     wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });
