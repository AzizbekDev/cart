import {
    isEmpty
} from 'lodash'

export const setCategories = (state, categories) => {
    state.categories = categories
}

export const login = (state) => {
    state.loading = true;
    state.auth_error = null;
}

export const loginSuccess = (state, payload) => {
    state.loading = false;
    state.auth_error = null;
    state.isLoggedIn = true;
    state.currentUser = Object.assign({}, payload.data, {
        token: payload.meta.token
    });
    localStorage.setItem("user", JSON.stringify(state.currentUser));
}

export const setToken = (state, token) => {
    if (isEmpty(token)) {
        localStorage.removeItem('token', token)
        return
    }
    localStorage.setItem('token', token)
}

export const loginFailed = (state, payload) => {
    state.loading = false;
    state.auth_error = payload.error;
}

export const logout = (state) => {
    localStorage.removeItem("user");
    state.isLoggedIn = false;
    state.currentUser = null;
}

export const setCartProducts = (state, products) => {
    state.cart.products = products
}
