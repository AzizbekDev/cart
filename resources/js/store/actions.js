export const fetchCategories = ({
    commit
}) => {
    return axios.get('/api/categories').then((response) => {
        commit('setCategories', response.data.data)
    })
}
