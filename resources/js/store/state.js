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
        total: null,
        quantity: null,
        empty: true,
        products: []
    }
}
