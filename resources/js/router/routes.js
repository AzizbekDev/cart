import Home from '../pages/Home'
import Login from '../pages/Login'
import About from '../pages/About'
import NotFound from '../pages/NotFound'


export default [{
        path: "/",
        name: "home",
        meta: {
            layout: "no-sidebar"
        },
        component: Home
    },
    {
        path: "/login",
        name: "login",
        meta: {
            layout: "no-sidebar"
        },
        component: Login
    },
    {
        path: "/about-us",
        name: "about",
        meta: {
            layout: "no-sidebar"
        },
        component: About,
    },
    {
        path: "*",
        name: "notFound",
        component: NotFound
    }
];
