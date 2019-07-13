import {
    isEmpty
} from 'lodash'

/**
 * Login request
 *
 * @export
 * @param {*} credentials
 */
export function login(credentials) {
    return new Promise((res, rej) => {
        axios.post('/api/auth/login', credentials)
            .then((response) => {
                res(response.data);
            })
            .catch((err) => {
                rej("Wrong email or password")
            })
    });
}
export function getLocalUser() {
    let userStr = localStorage.getItem('user');
    if (!userStr) {
        return null;
    }
    return JSON.parse(userStr);
}

export const setHttpToken = (token) => {
    if (isEmpty(token)) {
        window.axios.defaults.headers.common['Authorization'] = null
    }
    window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + token
}
