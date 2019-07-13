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
    return localStorage.getItem('token').then((token) => {
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
    })
}
