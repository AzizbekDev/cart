import store from '../store'
const beforeEach = ((to, from, next) => {
    let authUser = store.getters.isLoggedIn;
    let requestAuth = to.meta.needsAuth;
    if (to.path == '/login' && authUser) {
        next('/');
    } else if (requestAuth && !authUser) {
        next({
            name: 'home'
        })
    } else {
        next()
    }
})
export default beforeEach
