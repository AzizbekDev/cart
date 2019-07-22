import {
    getLocalUser
} from '../helpers/index'
const user = getLocalUser()

export default {
    categories: [],
    currentUser: user,
    isLoggedIn: !!user,
    loading: false,
    auth_error: '',
    cart: {
        quantity: null,
        empty: true,
        subtotal: null,
        total: null,
        products: [],
        changed: false,
        shipping: null
    }
}
