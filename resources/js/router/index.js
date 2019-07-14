import Vue from 'vue'
import routes from './routes'
import VueRouter from 'vue-router'
import beforeEach from '../router/beforeEach'

Vue.use(VueRouter)

const router = new VueRouter({
    mode: 'history',
    hash: false,
    routes,
})
router.beforeEach(beforeEach)
export default router
