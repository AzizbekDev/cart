import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex)
import state from './state'
import * as mutations from './mutations'
import * as actions from './actions'
import * as getters from './getters'


export default new Vuex.Store({
    state,
    mutations,
    getters,
    actions
})
