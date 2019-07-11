require('./bootstrap');
import Vue from 'vue'
import router from './router'
import store from './store'
import App from "./App.vue";

Vue.use(store)
Vue.config.productionTip = false;

router.beforeEach((to, from, next) => {
    let currentUser = store.getters.currentUser;
    let requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    if (requiresAuth && currentUser) {
        next('/login');
    } else if (to.path == '/login' && currentUser) {
        next('/');
    } else {
        next();
    }
});

new Vue(Vue.util.extend({
    store,
    router,
}, App)).$mount('#app');
