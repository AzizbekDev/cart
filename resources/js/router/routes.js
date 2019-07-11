import Home from '../pages/Home'
import Login from '../pages/auth/Login'
import About from '../pages/About'
import Categories from '../pages/categories/_slug'
import Products from '../pages/products/_slug'
import NotFound from '../pages/NotFound'

export default [{
        path: '/',
        name: 'home',
        component: Home,
        meta: {
            requiresAuth: false
        }
    },
    {
        path: '/about',
        name: 'about',
        component: About,
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
    },
    {
        path: '/categories/:slug',
        name: 'categories-slug',
        component: Categories
    },
    {
        path: '/products/:slug',
        name: 'products-slug',
        component: Products
    },
    {
        path: '*',
        name: 'notFound',
        component: NotFound,
    }
]
