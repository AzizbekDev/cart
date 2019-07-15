export const categories = (state) => {
    return state.categories
}

export const isLoading = (state) => {
    return state.loading
}

export const isLoggedIn = (state) => {
    return state.isLoggedIn
}

export const currentUser = (state) => {
    return state.currentUser
}
export const authError = (state) => {
    return state.auth_error
}

export const cart = (state) => {
    return state.cart
}

export const cartProducts = (state) => {
    return state.cart.products
}

export const countCart = (state) => {
    return state.cart.products.length
}

export const cartEmpty = (state) => {
    return state.cart.empty
}

export const cartSubtotal = (state) => {
    return state.cart.subtotal
}

export const cartTotal = (state) => {
    return state.cart.total
}
