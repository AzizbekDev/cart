require('./bootstrap');
import 'normalize.css';
import Vue from 'vue'
import router from './router'
import store from './store'
import App from "./App.vue";

Vue.use(store)
Vue.config.productionTip = false;

new Vue(Vue.util.extend({
    store,
    router,
}, App)).$mount('#app');
