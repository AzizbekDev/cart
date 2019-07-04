require('./bootstrap');
window.Vue = require('vue');

import router from './router'

import App from "./App.vue";

import Default from './layouts/Default.vue';

import NoSidebar from './layouts/NoSidebar.vue';

Vue.component('default-layout', Default);

Vue.component('no-sidebar-layout', NoSidebar);

Vue.config.productionTip = false;

const app = new Vue(Vue.util.extend({
    router
}, App)).$mount('#app');
