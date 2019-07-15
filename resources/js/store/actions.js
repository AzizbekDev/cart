import {
    isEmpty
} from 'lodash'
import {
    setHttpToken
} from '../helpers/index'

export const fetchCategories = ({
    commit
}) => {
    return axios.get('/api/categories').then((response) => {
        commit('setCategories', response.data.data)
    })
}

export const login = ({
    commit
}) => {
    return commit('login');
}

export const setToken = ({
    commit,
    dispatch
}, token) => {
    if (isEmpty(token)) {
        return dispatch('checkTokenExists').then((token) => {
            setHttpToken(token)
        })
    }
    commit('setToken', token)
    setHttpToken(token)
}

export const checkTokenExists = ({
    commit,
    dispatch
}, token) => {
    localStorage.getItem('token').then((token) => {
        if (isEmpty(token)) {
            return Promise.reject('NO_TOKEN');
        }
        return Promise.resolve(token)
    })
}

export const clearAuth = ({
    commit
}, token) => {
    commit('logout')
    commit('setToken', null)
    setHttpToken(null)
}

export const getCart = ({
    commit
}) => {
    return axios.get('/api/cart').then((response) => {
        commit('setCartProducts', response.data.data.products)
        console.log(response.data.meta.empty)
        commit('setEmpty', response.data.meta.empty)
    })
}

export const destroyCart = ({
    dispatch
}, productId) => {
    let response = axios.delete(`/api/cart/${productId}`);
    dispatch('getCart');
}

export const updateCart = ({
    dispatch
}, {
    productId,
    quantity
}) => {
    let response = axios.patch(`/api/cart/${productId}`, {
        quantity
    });
    dispatch('getCart');
}
