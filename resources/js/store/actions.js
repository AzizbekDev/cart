import queryString from 'query-string'
import {
    isEmpty
} from 'lodash'
import {
    setHttpToken
} from '../helpers/index'


import {
    shipping
} from './getters';

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
    commit,
    state
}) => {
    let query = {}
    if (state.cart.shipping) {
        query.shipping_method_id = state.cart.shipping.id
    }
    return axios.get(`/api/cart?${queryString.stringify(query)}`).then((response) => {
        commit('setCartProducts', response.data.data.products)
        commit('setEmpty', response.data.meta.empty)
        commit('setSubtotal', response.data.meta.subtotal)
        commit('setTotal', response.data.meta.total)
        commit('setChanged', response.data.meta.changed)
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

export const storeCart = ({
    dispatch
}, products) => {
    let response = axios.post('/api/cart', {
        products
    })
    dispatch('getCart');

}

export const storeShipping = ({
    commit
}, shipping) => {
    commit('setShipping', shipping);
}

export const flash = ({
    commit
}, message) => {
    commit('setMessage', message)
}

export const clearMessage = ({
    commit
}) => {
    commit('setMessage', null)
}
