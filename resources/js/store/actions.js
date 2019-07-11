/**
 * Via axios get all categories
 * @return {response.data} 
 */
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
