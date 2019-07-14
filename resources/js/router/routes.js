import Home from '../pages/Home'
import Login from '../pages/auth/Login'
import About from '../pages/About'
import Categories from '../pages/categories/_slug'
import Cart from '../pages/cart'
import Products from '../pages/products/_slug'
import NotFound from '../pages/NotFound'


export default [{
        path: '/',
        name: 'home',
        component: Home,
        meta: {
            guest: true,
            needsAuth: false
        }
    },
    {
        path: '/about',
        name: 'about',
        component: About,
        meta: {
            guest: true,
            needsAuth: false
        }
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: {
            guest: true,
            needsAuth: false
        }
    },
    {
        path: '/categories/:slug',
        name: 'categories-slug',
        component: Categories,
        meta: {
            guest: true,
            needsAuth: false
        }
    },
    {
        path: '/products/:slug',
        name: 'products-slug',
        component: Products,
        meta: {
            guest: true,
            needsAuth: false
        }
    },
    {
        path: '/cart',
        name: 'cart',
        component: Cart,
        meta: {
            guest: false,
            needsAuth: true
        }
    },
    {
        path: '*',
        name: 'notFound',
        component: NotFound,
        meta: {
            guest: true,
            needsAuth: false
        }
    }
]
